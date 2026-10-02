<?php

namespace App\Jobs;

use App\Models\ProductLead;
use App\Services\ExternalApiService;
use App\Services\ServiceCompanyClient;
use App\Services\SureshipApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class PushOrderToExternalApi implements ShouldQueue
{
    use Queueable;

    protected $lead;

    public function __construct(ProductLead $lead)
    {
        $this->lead = $lead;
    }

    public function handle(): void
    {
        $lead = $this->lead->fresh([
            'product.store.websiteSettings',
            'product.store.serviceIntegration',
            'product.store.workspace',
            'variation',
            'promotion',
        ]);

        if (!$lead) {
            Log::warning('Lead not found for pushing to external API');
            return;
        }

        $user = $lead->user;

        if (!$user) {
            Log::warning('User not found for lead ' . $lead->id);
            return;
        }

        $product = $lead->product;
        $store = $product?->store;
        $client = ServiceCompanyClient::forStore($store, $user);

        if (!$client->isEnabled()) {
            Log::info('External API not enabled for lead ' . $lead->id, [
                'user_id' => $user->id,
                'store_id' => $store?->id,
                'service_integration_id' => $store?->service_integration_id,
            ]);
            return;
        }

        if ($client instanceof SureshipApiService) {
            $result = $client->createOrderFromLead($lead, $store);
        } else {
            $result = $this->pushAlfaOrCustom($client, $lead, $store, $user, $product);
        }

        if ($result['success']) {
            Log::info('Successfully pushed lead to external API', [
                'lead_id' => $lead->id,
                'user_id' => $user->id,
                'store_id' => $store?->id,
                'client' => $client instanceof SureshipApiService ? 'sureship' : 'external',
            ]);
        } else {
            Log::error('Failed to push lead to external API', [
                'lead_id' => $lead->id,
                'user_id' => $user->id,
                'error' => $result['message'],
            ]);
        }
    }

    protected function pushAlfaOrCustom(ExternalApiService $apiService, ProductLead $lead, $store, $user, $product): array
    {
        $websiteSettings = $store?->websiteSettings;
        $sku = $lead->variation?->sku ?? $product?->sku;
        $itemPrice = (float) ($lead->selected_price ?? $product?->price ?? 0);
        $platformName = $this->platformName();
        $storeName = $store?->name;
        $productName = $product?->name ?? ('Product from ' . $platformName);
        $quantity = $lead->order_quantity;
        $website = $this->buildWebsitePayload($store, $websiteSettings, $product, $user, $platformName);

        $orderData = [
            'client_name' => $lead->name,
            'client_phone' => $lead->phone,
            'client_city' => $lead->city,
            'client_address' => $lead->address,
            'city' => $lead->city,
            'address' => $lead->address,
            'source' => 'whatsapp',
            'platform' => $platformName,
            'store_name' => $storeName,
            'website' => $website,
            'items' => [
                [
                    'product_id' => 1,
                    'sku' => $sku,
                    'name' => $productName,
                    'quantity' => $quantity,
                    'price' => $itemPrice,
                ]
            ],
            'notes' => trim(($lead->note ?? '') . "\n[{$platformName}" . ($storeName ? " | Store: {$storeName}" : '') . " | Product: {$productName}" . ($sku ? " | SKU: {$sku}" : '') . ($lead->city ? " | City: {$lead->city}" : '') . ($lead->address ? " | Address: {$lead->address}" : '') . ']'),
            'metadata' => [
                'platform' => $platformName,
                'store_name' => $storeName,
                'bralam_lead_id' => $lead->id,
                'bralam_product_id' => $lead->product_id,
                'bralam_variation_id' => $lead->selected_variation_id,
                'bralam_promotion_id' => $lead->selected_promotion_id,
                'bralam_store_id' => $store?->id,
                'quantity' => $quantity,
                'sku' => $sku,
                'city' => $lead->city,
                'address' => $lead->address,
                'language' => $lead->language,
                'created_at' => $lead->created_at->toIso8601String(),
                'website' => $website,
            ]
        ];

        return $apiService->createOrder($orderData);
    }

    protected function platformName(): string
    {
        return 'Bralam';
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildWebsitePayload($store, $websiteSettings, $product, $user, ?string $platformName = null): array
    {
        $platformName = $platformName ?: $this->platformName();

        if (!$store) {
            return [
                'id' => null,
                'name' => $platformName,
                'platform' => $platformName,
                'store_name' => null,
                'site_name' => null,
                'subdomain' => null,
                'domain' => null,
                'url' => null,
                'product_url' => null,
                'owner' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'company_name' => $user->company_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                ],
                'contact' => [
                    'phone' => null,
                    'email' => null,
                ],
            ];
        }

        $websiteUrl = $this->buildStoreWebsiteUrl($store);
        $productUrl = $product
            ? \App\Support\StoreDomain::productUrl($store, $product->slug)
            : null;
        $siteName = $websiteSettings?->site_name;
        $storeName = $store->name;
        $displayName = trim($platformName . ($storeName ? ' - ' . $storeName : ''));

        return [
            'id' => $store->id,
            'name' => $displayName,
            'platform' => $platformName,
            'store_name' => $storeName,
            'site_name' => $siteName,
            'subdomain' => $store->subdomain,
            'domain' => $store->domain,
            'url' => $websiteUrl,
            'product_url' => $productUrl,
            'owner' => [
                'id' => $user->id,
                'name' => $user->name,
                'company_name' => $user->company_name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            'contact' => [
                'phone' => $websiteSettings?->contact_phone,
                'email' => $websiteSettings?->contact_email,
            ],
        ];
    }

    protected function buildStoreWebsiteUrl($store): string
    {
        return \App\Support\StoreDomain::homeUrl($store);
    }
}
