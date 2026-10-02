<?php

namespace App\Services;

use App\Models\GoogleSheetConnection;
use App\Models\ProductLead;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleSheetService
{
    public function appendLead(GoogleSheetConnection $connection, ProductLead $lead): array
    {
        if (!$connection->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Google Sheet connection is not enabled or missing webhook URL',
            ];
        }

        $lead->loadMissing(['product', 'variation', 'promotion']);

        $product = $lead->product;
        $payload = [
            'created_at' => optional($lead->created_at)->toDateTimeString() ?? now()->toDateTimeString(),
            'lead_id' => $lead->id,
            'customer_name' => $lead->name,
            'customer_phone' => $lead->phone,
            'city' => $lead->city,
            'address' => $lead->address,
            'note' => $lead->note,
            'product_name' => $product?->name,
            'product_sku' => $lead->variation?->sku ?? $product?->sku,
            'quantity' => $lead->order_quantity ?? 1,
            'price' => (float) ($lead->selected_price ?? $product?->price ?? 0),
            'variation' => $lead->variation?->name ?? $lead->variation?->sku,
            'promotion' => $lead->promotion?->label,
            'language' => $lead->language,
            'status' => $lead->status,
            'sheet_tab' => $connection->sheet_tab,
            'spreadsheet_id' => $connection->spreadsheet_id,
        ];

        return $this->postWebhook($connection, $payload);
    }

    public function testConnection(GoogleSheetConnection $connection): array
    {
        if (!$connection->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Add a webhook URL and enable the connection first',
            ];
        }

        $payload = [
            'created_at' => now()->toDateTimeString(),
            'lead_id' => 'TEST',
            'customer_name' => 'Bralam Test',
            'customer_phone' => '0000000000',
            'city' => 'Test City',
            'address' => 'Test Address',
            'note' => 'Connection test from Bralam',
            'product_name' => 'Test Product',
            'product_sku' => 'TEST-SKU',
            'quantity' => 1,
            'price' => 0,
            'language' => 'fr',
            'status' => 'test',
            'sheet_tab' => $connection->sheet_tab,
            'spreadsheet_id' => $connection->spreadsheet_id,
            'is_test' => true,
        ];

        return $this->postWebhook($connection, $payload);
    }

    protected function postWebhook(GoogleSheetConnection $connection, array $payload): array
    {
        try {
            Log::info('Pushing order to Google Sheet', [
                'connection_id' => $connection->id,
                'lead_id' => $payload['lead_id'] ?? null,
            ]);

            $response = Http::timeout(30)
                ->acceptJson()
                ->asJson()
                ->post($connection->webhook_url, $payload);

            // Apps Script often returns 302 then 200; follow redirects already handled by HTTP client.
            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Row added to Google Sheet successfully',
                    'data' => $response->json() ?: $response->body(),
                ];
            }

            // Some Apps Script deployments return HTML on success with 200 already covered.
            Log::warning('Google Sheet webhook failed', [
                'connection_id' => $connection->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Google Sheet webhook failed (HTTP ' . $response->status() . ')',
                'status' => $response->status(),
            ];
        } catch (\Throwable $e) {
            Log::error('Google Sheet webhook exception', [
                'connection_id' => $connection->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Google Sheet error: ' . $e->getMessage(),
            ];
        }
    }
}
