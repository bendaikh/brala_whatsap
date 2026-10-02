<?php

namespace App\Services;

use App\Models\GoogleSheetConnection;
use App\Models\ProductLead;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleSheetService
{
    /**
     * Column definitions sent to the Apps Script. The script writes each value
     * under the sheet header that matches key / header / one of the aliases
     * (case, accents, spaces and punctuation are ignored), so the row always
     * lines up with the sheet's own header row whatever its column order.
     *
     * Order of this list = default header row for an empty sheet.
     */
    public const COLUMNS = [
        'created_at' => ['header' => 'Date', 'aliases' => ['Order date', 'Created at', 'Date commande', 'Date de commande', 'Datetime', 'التاريخ']],
        'lead_id' => ['header' => 'Lead ID', 'text' => true, 'aliases' => ['ID', 'Order ID', 'Order', 'Order number', 'Order N', 'Reference', 'Ref', 'N commande', 'Numero commande', 'Commande', 'Code', 'رقم الطلب']],
        'customer_name' => ['header' => 'Name', 'aliases' => ['Full name', 'Customer', 'Customer name', 'Client', 'Nom', 'Nom complet', 'Nom client', 'Nom du client', 'Recipient', 'Destinataire', 'Receiver', 'Consignee', 'الاسم']],
        'customer_phone' => ['header' => 'Phone', 'text' => true, 'aliases' => ['Phone number', 'Telephone', 'Tel', 'Tél', 'Mobile', 'Numero', 'Numero de telephone', 'Contact', 'Customer phone', 'Recipient phone', 'Whatsapp', 'الهاتف']],
        'city' => ['header' => 'City', 'aliases' => ['Ville', 'Commune', 'Town', 'Destination', 'المدينة']],
        'address' => ['header' => 'Address', 'aliases' => ['Adresse', 'Shipping address', 'Adresse de livraison', 'Delivery address', 'Lieu de livraison', 'Quartier', 'Location', 'العنوان']],
        'product_name' => ['header' => 'Product', 'aliases' => ['Product name', 'Produit', 'Nom produit', 'Nom du produit', 'Article', 'Item', 'Items', 'المنتج']],
        'product_sku' => ['header' => 'SKU', 'text' => true, 'aliases' => ['Product SKU', 'Product ref', 'Product reference', 'Reference produit', 'Ref produit', 'Code produit']],
        'quantity' => ['header' => 'Qty', 'number' => true, 'aliases' => ['Quantity', 'Quantite', 'Qte', 'Qté', 'Product qt', 'Product qty', 'Nombre', 'الكمية']],
        'price' => ['header' => 'Price', 'number' => true, 'aliases' => ['Unit price', 'Prix', 'Prix unitaire', 'السعر']],
        'total' => ['header' => 'Total', 'number' => true, 'aliases' => ['Amount', 'Total price', 'Prix total', 'Montant', 'Montant total', 'COD', 'COD amount', 'Cash on delivery', 'A encaisser', 'Total amount', 'المجموع']],
        'note' => ['header' => 'Note', 'aliases' => ['Notes', 'Comment', 'Comments', 'Commentaire', 'Remarque', 'Remarques', 'Observation', 'Instructions', 'ملاحظة']],
        'status' => ['header' => 'Status', 'aliases' => ['Statut', 'Etat', 'Order status', 'الحالة']],
        'language' => ['header' => 'Language', 'aliases' => ['Langue', 'Lang']],
        'variation' => ['header' => 'Variation', 'aliases' => ['Variant', 'Variante', 'Option', 'Options', 'Size', 'Taille', 'Color', 'Couleur']],
        'promotion' => ['header' => 'Offer', 'aliases' => ['Promotion', 'Promo', 'Offre', 'Pack', 'Bundle', 'العرض']],
        'country' => ['header' => 'Country', 'aliases' => ['Pays', 'البلد']],
        'currency' => ['header' => 'Currency', 'aliases' => ['Devise', 'Monnaie', 'العملة']],
    ];

    /** Columns that are added to the default header row of an empty sheet. */
    protected const DEFAULT_HEADER_KEYS = [
        'created_at', 'lead_id', 'customer_name', 'customer_phone', 'city', 'address',
        'product_name', 'product_sku', 'quantity', 'price', 'total', 'note', 'status', 'language',
    ];

    public function appendLead(GoogleSheetConnection $connection, ProductLead $lead): array
    {
        if (!$connection->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Google Sheet connection is not enabled or missing webhook URL',
            ];
        }

        $lead->loadMissing(['product.store.workspace', 'variation', 'promotion']);

        $product = $lead->product;
        $quantity = max(1, (int) ($lead->order_quantity ?: 1));
        $price = (float) ($lead->selected_price ?? $product?->price ?? 0);

        $values = [
            'created_at' => optional($lead->created_at)->toDateTimeString() ?? now()->toDateTimeString(),
            'lead_id' => $lead->id,
            'customer_name' => $lead->name,
            'customer_phone' => $lead->phone,
            'city' => $lead->city,
            'address' => $lead->address,
            'note' => $lead->note,
            'product_name' => $product?->name,
            'product_sku' => $lead->variation?->sku ?? $product?->sku,
            'quantity' => $quantity,
            // Prices are stored in the store currency's main unit (e.g. 14900.00 XOF):
            // send plain numbers, never a formatted string or currency symbol.
            'price' => $this->plainAmount($price),
            'total' => $this->plainAmount($price * $quantity),
            'currency' => $product?->store?->workspace?->getCurrencyCode(),
            'variation' => $lead->variation?->name ?? $lead->variation?->sku,
            'promotion' => $lead->promotion?->label,
            'language' => $lead->language,
            'status' => $lead->status,
            'country' => null,
        ];

        return $this->postWebhook($connection, $this->buildPayload($connection, $values, (array) ($lead->custom_fields ?? [])));
    }

    public function testConnection(GoogleSheetConnection $connection): array
    {
        if (!$connection->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Add a webhook URL and enable the connection first',
            ];
        }

        $values = [
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
            'total' => 0,
            'language' => 'fr',
            'status' => 'test',
        ];

        $payload = $this->buildPayload($connection, $values);
        $payload['is_test'] = true;

        return $this->postWebhook($connection, $payload);
    }

    /**
     * Flat keys are kept for older Apps Script templates (positional appendRow);
     * "columns" drives the header-aware template.
     */
    public function buildPayload(GoogleSheetConnection $connection, array $values, array $customFields = []): array
    {
        $columns = [];

        foreach (self::COLUMNS as $key => $def) {
            $columns[] = [
                'key' => $key,
                'header' => $def['header'],
                'aliases' => $def['aliases'] ?? [],
                'text' => (bool) ($def['text'] ?? false),
                'number' => (bool) ($def['number'] ?? false),
                'default' => in_array($key, self::DEFAULT_HEADER_KEYS, true),
                'value' => $this->cellValue($values[$key] ?? null),
            ];
        }

        foreach ($customFields as $name => $value) {
            if (!is_string($name) || trim($name) === '') {
                continue;
            }
            $columns[] = [
                'key' => 'custom_' . $name,
                'header' => $name,
                'aliases' => [str_replace('_', ' ', $name)],
                'text' => true,
                'number' => false,
                'default' => false,
                'value' => $this->cellValue($value),
            ];
        }

        return array_merge(
            $values,
            [
                'sheet_tab' => $connection->sheet_tab,
                'spreadsheet_id' => $connection->spreadsheet_id,
                'columns' => $columns,
            ]
        );
    }

    /** 14900.0 -> 14900, 149.5 -> 149.5 (max 2 decimals). */
    protected function plainAmount(float $amount): int|float
    {
        $amount = round($amount, 2);

        return floor($amount) == $amount ? (int) $amount : $amount;
    }

    protected function cellValue(mixed $value): string|int|float
    {
        if ($value === null) {
            return '';
        }
        if (is_bool($value)) {
            return $value ? 'yes' : 'no';
        }
        if (is_array($value)) {
            return implode(', ', array_map(fn ($v) => is_scalar($v) ? (string) $v : json_encode($v), $value));
        }
        if (is_int($value) || is_float($value)) {
            return $value;
        }

        // Keep newlines, strip other control characters.
        return trim(preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F]/u', ' ', (string) $value));
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
            $json = $response->json();

            if ($response->successful() && is_array($json) && array_key_exists('success', $json) && $json['success'] === false) {
                Log::warning('Google Sheet Apps Script reported an error', [
                    'connection_id' => $connection->id,
                    'error' => mb_substr((string) ($json['error'] ?? ''), 0, 1000),
                ]);

                return [
                    'success' => false,
                    'message' => 'Google Sheet script error: ' . mb_substr((string) ($json['error'] ?? 'unknown'), 0, 500),
                    'data' => $json,
                ];
            }

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
