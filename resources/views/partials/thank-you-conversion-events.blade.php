@php
    $product = $lead->product;
    $orderValue = (float) ($lead->selected_price ?? $product->price);
    $currencyCode = ($store->workspace ?? null)?->getCurrencyCode() ?? 'MAD';
@endphp

@if($trackConversion)
    @if($store->tiktok_pixel_enabled && $store->tiktok_pixel_id)
    <script>
        if (typeof ttq === 'object' && typeof ttq.track === 'function') {
            ttq.track('SubmitForm', {
                content_name: @json($product->name),
                content_id: @json((string) $product->id),
                content_type: 'product',
                value: {{ $orderValue }},
                currency: @json($currencyCode)
            });
        }
    </script>
    @endif
@endif
