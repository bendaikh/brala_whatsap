<?php

namespace App\Services;

use App\Models\ProductLead;
use App\Models\Store;
use App\Models\WorkspaceServiceIntegration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SureshipApiService
{
    protected ?string $apiBase = null;
    protected ?string $apiToken = null;
    protected bool $enabled = false;
    protected ?string $defaultProductRef = null;
    protected ?string $defaultCountry = null;
    protected ?int $integrationId = null;
    protected ?string $sourceLabel = null;

    public static function fromIntegration(WorkspaceServiceIntegration $integration): self
    {
        $service = new self();
        $service->configureFromIntegration($integration);

        return $service;
    }

    protected function configureFromIntegration(WorkspaceServiceIntegration $integration): void
    {
        $this->integrationId = $integration->id;
        $this->sourceLabel = 'sureship:' . $integration->id . ':' . $integration->name;
        $this->enabled = (bool) $integration->is_enabled;
        $this->defaultProductRef = $integration->getSetting('default_product_ref');
        $this->defaultCountry = $integration->getSetting('default_country', 'CI');

        if ($integration->api_url && $integration->hasApiKey()) {
            $this->apiBase = $this->normalizeBaseUrl($integration->api_url);
            $this->apiToken = $integration->api_key;
        }
    }

    /**
     * Accepts either https://app.sureship.space or .../api/v1
     */
    protected function normalizeBaseUrl(string $url): string
    {
        $url = rtrim($url, '/');

        if (!preg_match('#/api/v1$#i', $url)) {
            $url = preg_replace('#/api/?$#i', '', $url);
            $url .= '/api/v1';
        }

        return $url;
    }

    public function isEnabled(): bool
    {
        return $this->enabled && !empty($this->apiBase) && !empty($this->apiToken);
    }

    public function createOrderFromLead(ProductLead $lead, ?Store $store = null): array
    {
        $product = $lead->product;
        $sku = $lead->variation?->sku ?? $product?->sku;
        $productRef = $this->defaultProductRef ?: ($sku ?: (string) ($product?->id ?? ''));
        $quantity = max(1, (int) ($lead->order_quantity ?: 1));
        $unitPrice = (float) ($lead->selected_price ?? $product?->price ?? 0);
        $amount = $unitPrice * $quantity;
        $country = $this->defaultCountry
            ?: ($store?->workspace?->getCurrency() === 'XOF' ? 'CI' : 'CI');

        $payload = [
            'api_token' => $this->apiToken,
            'recipient' => (string) $lead->name,
            'phone' => (string) $lead->phone,
            'address' => (string) ($lead->address ?: ($lead->city ?: 'N/A')),
            'product_qt' => $quantity,
            'product' => (string) $productRef,
            'amount' => $amount,
            'country' => $country,
            'city' => $lead->city,
            'note' => trim(implode(' | ', array_filter([
                $lead->note,
                $product?->name ? 'Product: ' . $product->name : null,
                $sku ? 'SKU: ' . $sku : null,
                $store?->name ? 'Store: ' . $store->name : null,
                'Bralam lead #' . $lead->id,
            ]))),
            'can_open' => true,
            'ip' => $lead->ip_address ?? request()->ip(),
        ];

        return $this->createOrder($payload);
    }

    public function createOrder(array $orderData): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'message' => 'Sureship API is not enabled or configured properly',
            ];
        }

        // Production route is /orders/create (docs UI shows /orders-create which is SPA-only).
        $url = $this->apiBase . '/orders/create';

        try {
            Log::info('Pushing order to Sureship', [
                'url' => $url,
                'source' => $this->sourceLabel,
                'integration_id' => $this->integrationId,
                'payload' => array_merge($orderData, ['api_token' => '[redacted]']),
            ]);

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
                ->timeout(30)
                ->post($url, $orderData);

            $body = $response->body();
            $json = $response->json();

            // Sureship sometimes returns HTTP 200 with plain text errors like "Product not found"
            $isPlainError = is_string($body)
                && !$response->json()
                && preg_match('/not found|error|unauthorized|invalid/i', $body);

            if ($response->successful() && !$isPlainError) {
                Log::info('Sureship order created', [
                    'integration_id' => $this->integrationId,
                    'response' => $json ?: $body,
                ]);

                return [
                    'success' => true,
                    'message' => 'Order created successfully in Sureship',
                    'data' => $json ?: ['raw' => $body],
                ];
            }

            $message = is_array($json)
                ? ($json['message'] ?? $json['error'] ?? json_encode($json))
                : trim($body);

            Log::warning('Sureship order create failed', [
                'integration_id' => $this->integrationId,
                'status' => $response->status(),
                'response' => $body,
            ]);

            return [
                'success' => false,
                'message' => 'Sureship: ' . $message,
                'status' => $response->status(),
            ];
        } catch (\Throwable $e) {
            Log::error('Sureship order create exception', [
                'integration_id' => $this->integrationId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Sureship exception: ' . $e->getMessage(),
            ];
        }
    }

    public function testConnection(): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'message' => 'Sureship API is not enabled or configured properly',
            ];
        }

        try {
            // Authenticated endpoint; 401 without token, any auth-aware response means URL is reachable.
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
                ->timeout(10)
                ->post($this->apiBase . '/orders/create', [
                    'api_token' => $this->apiToken,
                ]);

            $body = trim((string) $response->body());

            // Valid token typically returns validation / product errors, not Unauthorized.
            if ($response->status() === 401 || stripos($body, 'unauthorized') !== false) {
                return [
                    'success' => false,
                    'message' => 'Invalid Sureship API token',
                ];
            }

            if ($response->successful() || $response->status() === 422 || $response->status() === 400) {
                return [
                    'success' => true,
                    'message' => 'Connection successful (API token accepted by Sureship)',
                ];
            }

            // Plain-text business errors (e.g. Product not found) still prove auth works.
            if ($response->status() === 200 && $body !== '') {
                return [
                    'success' => true,
                    'message' => 'Connection successful (API token accepted by Sureship)',
                ];
            }

            return [
                'success' => false,
                'message' => 'Connection failed: HTTP ' . $response->status() . ' — ' . $body,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage(),
            ];
        }
    }
}
