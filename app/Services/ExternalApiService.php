<?php

namespace App\Services;

use App\Models\Store;
use App\Models\User;
use App\Models\WorkspaceServiceIntegration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExternalApiService
{
    protected ?string $apiUrl = null;
    protected ?string $apiKey = null;
    protected bool $enabled = false;
    protected ?string $sourceLabel = null;
    protected ?int $userId = null;
    protected ?int $integrationId = null;

    public function __construct(?User $user = null)
    {
        if ($user) {
            $this->configureFromUser($user);
        }
    }

    /**
     * Prefer the store's assigned service company; fall back to user-level System Connect.
     */
    public static function forStore(?Store $store, ?User $user = null): self
    {
        $service = new self();

        $integration = $store?->serviceIntegration;
        if ($integration && $integration->is_enabled && $integration->api_url && $integration->hasApiKey()) {
            $service->configureFromIntegration($integration);
            return $service;
        }

        $user = $user ?: $store?->user;
        if ($user) {
            $service->configureFromUser($user);
        }

        return $service;
    }

    public static function fromIntegration(WorkspaceServiceIntegration $integration): self
    {
        $service = new self();
        $service->configureFromIntegration($integration);

        return $service;
    }

    protected function configureFromUser(User $user): void
    {
        $this->userId = $user->id;
        $this->sourceLabel = 'user:' . $user->id;
        $this->enabled = (bool) $user->external_api_enabled;

        if ($user->external_api_enabled && $user->external_api_url && $user->external_api_key_encrypted) {
            $this->apiUrl = $this->normalizeUrl($user->external_api_url);

            try {
                $this->apiKey = Crypt::decryptString($user->external_api_key_encrypted);
            } catch (\Throwable $e) {
                Log::error('Failed to decrypt external API key for user ' . $user->id, ['error' => $e->getMessage()]);
            }
        }
    }

    protected function configureFromIntegration(WorkspaceServiceIntegration $integration): void
    {
        $this->integrationId = $integration->id;
        $this->userId = $integration->user_id;
        $this->sourceLabel = 'integration:' . $integration->id . ':' . $integration->name;
        $this->enabled = (bool) $integration->is_enabled;

        if ($integration->api_url && $integration->hasApiKey()) {
            $this->apiUrl = $this->normalizeUrl($integration->api_url);
            $this->apiKey = $integration->api_key;
        }
    }

    protected function normalizeUrl(string $url): string
    {
        $url = rtrim($url, '/');

        return preg_replace('#/api/?$#i', '', $url);
    }

    public function isEnabled(): bool
    {
        return $this->enabled && !empty($this->apiUrl) && !empty($this->apiKey);
    }

    public function createOrder(array $orderData): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'message' => 'External API is not enabled or configured properly'
            ];
        }

        $url = $this->apiUrl . '/api/orders';

        try {
            Log::info('Attempting to create order in external API', [
                'url' => $url,
                'source' => $this->sourceLabel,
                'user_id' => $this->userId,
                'integration_id' => $this->integrationId,
                'order_data' => $orderData
            ]);

            $jsonBody = json_encode($orderData);

            Log::info('Raw JSON body being sent', [
                'json_body' => $jsonBody,
                'json_valid' => json_last_error() === JSON_ERROR_NONE
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])
            ->timeout(30)
            ->withBody($jsonBody, 'application/json')
            ->post($url);

            if ($response->successful()) {
                Log::info('Order pushed to external API successfully', [
                    'source' => $this->sourceLabel,
                    'user_id' => $this->userId,
                    'integration_id' => $this->integrationId,
                    'url' => $url,
                    'response' => $response->json()
                ]);

                return [
                    'success' => true,
                    'message' => 'Order created successfully',
                    'data' => $response->json()
                ];
            }

            Log::warning('Failed to push order to external API', [
                'source' => $this->sourceLabel,
                'user_id' => $this->userId,
                'integration_id' => $this->integrationId,
                'url' => $url,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create order: ' . $response->body(),
                'status' => $response->status()
            ];

        } catch (\Throwable $e) {
            Log::error('Exception while pushing order to external API', [
                'source' => $this->sourceLabel,
                'user_id' => $this->userId,
                'integration_id' => $this->integrationId,
                'url' => $url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    public function testConnection(): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'message' => 'External API is not enabled or configured properly'
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])
            ->timeout(10)
            ->get($this->apiUrl . '/api/orders');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Connection successful'
                ];
            }

            return [
                'success' => false,
                'message' => 'Connection failed with status: ' . $response->status()
            ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage()
            ];
        }
    }
}
