@php
    $product = $lead->product;
    $orderValue = $lead->selected_price ?? $product->price;
    $workspace = $store->workspace ?? null;
    $currencySymbol = $workspace?->getCurrencySymbol() ?? 'DHS';
    $currencyCode = $workspace?->getCurrencyCode() ?? 'MAD';
    $pageLang = $workspace?->getHtmlLang() ?? 'ar';
    $pageDir = $workspace?->isRtl() ? 'rtl' : 'ltr';
    $tyLang = $workspace?->getLanguage() ?? 'ar';
    $ty = match ($tyLang) {
        'fr' => [
            'title' => 'Merci pour votre confiance',
            'doc_title' => 'Merci',
            'summary' => 'Détails de la commande',
            'order' => 'N° de commande',
            'name' => 'Nom',
            'phone' => 'Téléphone',
            'product' => 'Produit',
            'offer' => 'Offre',
            'option' => 'Option',
            'amount' => 'Montant',
            'success' => 'Votre commande a été enregistrée avec succès et est en cours de traitement.',
            'call' => 'Notre équipe vous contactera sous peu pour confirmer la commande et vérifier les informations de livraison.',
            'call2' => 'Veuillez garder votre téléphone disponible et répondre à l\'appel afin que nous puissions confirmer et expédier rapidement.',
            'delivery' => 'Après confirmation, votre commande sera préparée et livrée rapidement sous 24 à 48 heures maximum.',
            'important' => 'Important :',
            'warning' => 'Ne pas répondre à l\'appel de confirmation peut entraîner un retard ou une annulation automatique de la commande.',
            'closing' => 'Merci de nous avoir choisis. Nous avons hâte de vous offrir une excellente expérience.',
            'back' => 'Retour aux produits',
        ],
        'en' => [
            'title' => 'Thank you for your trust',
            'doc_title' => 'Thank you',
            'summary' => 'Order details',
            'order' => 'Order number',
            'name' => 'Name',
            'phone' => 'Phone',
            'product' => 'Product',
            'offer' => 'Offer',
            'option' => 'Option',
            'amount' => 'Amount',
            'success' => 'Your order has been successfully recorded and is now being prepared.',
            'call' => 'Our team will contact you shortly to confirm the order and verify delivery details.',
            'call2' => 'Please keep your phone available and answer the call so we can confirm and ship your order quickly.',
            'delivery' => 'After confirmation, your order will be prepared and delivered within 24 to 48 hours.',
            'important' => 'Important:',
            'warning' => 'Not answering the confirmation call may delay or automatically cancel the order.',
            'closing' => 'Thank you for choosing us. We look forward to giving you a great experience.',
            'back' => 'Back to products',
        ],
        default => [
            'title' => 'شكرًا لثقتكم بنا',
            'doc_title' => 'شكرًا لثقتكم بنا',
            'summary' => 'تفاصيل الطلب',
            'order' => 'رقم الطلب',
            'name' => 'الاسم',
            'phone' => 'الهاتف',
            'product' => 'المنتج',
            'offer' => 'العرض',
            'option' => 'الخيار',
            'amount' => 'المبلغ',
            'success' => 'تم تسجيل طلبكم بنجاح، وهو الآن قيد المراجعة والتحضير.',
            'call' => 'سيتواصل معكم فريقنا خلال وقت قصير لتأكيد الطلب والتحقق من معلومات التوصيل.',
            'call2' => 'يرجى التأكد من إبقاء هاتفكم متاحًا والرد على المكالمة حتى نتمكن من تأكيد طلبكم وإرساله بسرعة.',
            'delivery' => 'بعد التأكيد، سيتم تجهيز وشحن طلبكم مع توصيل سريع خلال 24 إلى 48 ساعة كحد أقصى.',
            'important' => 'مهم:',
            'warning' => 'عدم الرد على مكالمة التأكيد قد يؤدي إلى تأخير أو إلغاء الطلب تلقائيًا.',
            'closing' => 'نشكركم على اختياركم لنا، ونتطلع إلى تقديم تجربة ممتازة لكم 🌿',
            'back' => 'العودة إلى المنتجات',
        ],
    };
@endphp
<!DOCTYPE html>
<html lang="{{ $pageLang }}" dir="{{ $pageDir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $ty['doc_title'] }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; }
    </style>

    @include('partials.facebook-pixels', [
        'store' => $store,
        'facebookPixelEvents' => $trackConversion ? [[
            'name' => 'Lead',
            'params' => [
                'content_name' => $product->name,
                'content_ids' => [(string) $product->id],
                'content_type' => 'product',
                'value' => (float) $orderValue,
                'currency' => $currencyCode,
                'order_id' => (string) $lead->id,
            ],
        ]] : [],
    ])

    @if($store->tiktok_pixel_enabled && $store->tiktok_pixel_id)
    <!-- TikTok Pixel Code -->
    <script>
        !function (w, d, t) {
          w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var o=document.createElement("script");o.type="text/javascript",o.async=!0,o.src=i+"?sdkid="+e+"&lib="+t;var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};
          ttq.load('{{ $store->tiktok_pixel_id }}');
          ttq.page();
        }(window, document, 'ttq');
    </script>
    <!-- End TikTok Pixel Code -->
    @endif

    @include('partials.thank-you-conversion-events', compact('store', 'lead', 'trackConversion'))
</head>
<body class="antialiased bg-gradient-to-br from-green-50 to-emerald-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-2xl w-full bg-white rounded-3xl shadow-2xl p-8 md:p-12 text-center">
        <!-- Success Icon -->
        <div class="w-24 h-24 mx-auto mb-6 bg-green-100 rounded-full flex items-center justify-center">
            <span class="text-5xl">✅</span>
        </div>
        
        <!-- Main Title -->
        <h1 class="text-3xl md:text-4xl font-black text-gray-900 mb-6">
            {{ $ty['title'] }}
        </h1>

        <!-- Order Summary -->
        <div class="bg-gray-50 border-2 border-gray-200 rounded-2xl p-6 mb-6 {{ $pageDir === 'rtl' ? 'text-right' : 'text-left' }}">
            <h2 class="text-lg font-bold text-gray-900 mb-4 text-center">{{ $ty['summary'] }}</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">{{ $ty['order'] }}</dt>
                    <dd class="text-gray-900 font-bold">#{{ $lead->id }}</dd>
                </div>
                @if($lead->name)
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">{{ $ty['name'] }}</dt>
                    <dd class="text-gray-900 font-semibold">{{ $lead->name }}</dd>
                </div>
                @endif
                @if($lead->phone)
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">{{ $ty['phone'] }}</dt>
                    <dd class="text-gray-900 font-semibold" dir="ltr">{{ $lead->phone }}</dd>
                </div>
                @endif
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">{{ $ty['product'] }}</dt>
                    <dd class="text-gray-900 font-semibold">{{ $product->name }}</dd>
                </div>
                @if($lead->promotion)
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">{{ $ty['offer'] }}</dt>
                    <dd class="text-gray-900">{{ $lead->promotion->label ?? $lead->promotion->quantity_range }}</dd>
                </div>
                @endif
                @if($lead->variation)
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">{{ $ty['option'] }}</dt>
                    <dd class="text-gray-900">
                        @if(!empty($lead->variation->attributes) && is_array($lead->variation->attributes))
                            {{ implode(' / ', array_map(fn($k, $v) => "$k: $v", array_keys($lead->variation->attributes), $lead->variation->attributes)) }}
                        @else
                            {{ $lead->variation->name ?? '—' }}
                        @endif
                    </dd>
                </div>
                @endif
                @if($orderValue)
                <div class="flex justify-between gap-4 border-t border-gray-200 pt-3">
                    <dt class="text-gray-500">{{ $ty['amount'] }}</dt>
                    <dd class="text-green-700 font-black text-lg">{{ number_format((float) $orderValue, 2) }} {{ $currencySymbol }}</dd>
                </div>
                @endif
            </dl>
        </div>
        
        <!-- Success Message -->
        <div class="bg-green-50 border-2 border-green-200 rounded-2xl p-6 mb-6">
            <p class="text-lg text-gray-800 leading-relaxed">
                {{ $ty['success'] }}
            </p>
        </div>
        
        <!-- Phone Call Notice -->
        <div class="bg-blue-50 border-2 border-blue-200 rounded-2xl p-6 mb-6">
            <div class="flex items-start gap-4">
                <span class="text-3xl">📞</span>
                <div class="{{ $pageDir === 'rtl' ? 'text-right' : 'text-left' }}">
                    <p class="text-gray-800 leading-relaxed">
                        {{ $ty['call'] }}
                    </p>
                    <p class="text-gray-700 mt-2 leading-relaxed">
                        {{ $ty['call2'] }}
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Delivery Notice -->
        <div class="bg-amber-50 border-2 border-amber-200 rounded-2xl p-6 mb-6">
            <div class="flex items-start gap-4">
                <span class="text-3xl">🚚</span>
                <p class="text-gray-800 leading-relaxed {{ $pageDir === 'rtl' ? 'text-right' : 'text-left' }}">
                    {{ $ty['delivery'] }}
                </p>
            </div>
        </div>
        
        <!-- Warning Notice -->
        <div class="bg-red-50 border-2 border-red-200 rounded-2xl p-6 mb-8">
            <div class="flex items-start gap-4">
                <span class="text-3xl">⚠️</span>
                <div class="{{ $pageDir === 'rtl' ? 'text-right' : 'text-left' }}">
                    <p class="text-red-800 font-bold mb-1">{{ $ty['important'] }}</p>
                    <p class="text-red-700 leading-relaxed">
                        {{ $ty['warning'] }}
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Closing Message -->
        <div class="border-t-2 border-gray-100 pt-6 mb-8">
            <p class="text-gray-700 text-lg leading-relaxed">
                {{ $ty['closing'] }}
            </p>
        </div>

        <!-- Back to products -->
        <a href="{{ \App\Support\StoreDomain::homeUrl($store) }}"
           class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-8 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-lg rounded-2xl transition shadow-lg shadow-emerald-600/20">
            {{ $ty['back'] }}
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </a>
    </div>
</body>
</html>
