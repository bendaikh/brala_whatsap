<!DOCTYPE html>
@php
    $workspace = $workspace ?? $store->workspace ?? null;
    $lpLang = $workspace?->getLanguage() ?? 'ar';
    $lpCurrencyCode = $workspace?->getCurrencyCode() ?? 'MAD';
    $lpCurrencySymbol = $workspace?->getCurrencySymbol() ?? 'DHS';
    $lpIsRtl = $workspace?->isRtl() ?? ($lpLang === 'ar');
    $lpHtmlLang = $workspace?->getHtmlLang() ?? 'ar';
    $isMoroccoMarket = $workspace?->usesDarija() ?? ($lpCurrencyCode === 'MAD');

    $lpBg = $product->landing_page_background_color ?: '#1e3a8a';
    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $lpBg)) {
        $lpBg = '#1e3a8a';
    }
    $lpBg = strtolower($lpBg);

    $lpAdjust = function (string $hex, float $percent): string {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $r = (int) max(0, min(255, round($r + (255 * $percent / 100))));
        $g = (int) max(0, min(255, round($g + (255 * $percent / 100))));
        $b = (int) max(0, min(255, round($b + (255 * $percent / 100))));
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    };

    $lpBgDark = $lpAdjust($lpBg, -12);
    $lpBgMid = $lpAdjust($lpBg, 8);
    $lpBgLight = $lpAdjust($lpBg, 18);
@endphp
<html lang="{{ $lpHtmlLang }}" class="scroll-smooth" x-data="{ currentLang: '{{ $lpLang }}' }" style="background-color:{{ $lpBg }};">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $product->name }}</title>
    <meta name="description" content="{{ Str::limit(strip_tags($product->description), 160) }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800;900&family=Tajawal:wght@400;500;700;800;900&family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    @include('partials.facebook-pixels', [
        'store' => $store,
        'facebookPixelEvents' => [[
            'name' => 'ViewContent',
            'params' => [
                'content_name' => $product->name,
                'content_ids' => [(string) $product->id],
                'content_type' => 'product',
                'value' => (float) $product->price,
                'currency' => $lpCurrencyCode,
            ],
        ]],
    ])
    
    @if($store->tiktok_pixel_enabled && $store->tiktok_pixel_id)
    <!-- TikTok Pixel Code -->
    <script>
        !function (w, d, t) {
          w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var o=document.createElement("script");o.type="text/javascript",o.async=!0,o.src=i+"?sdkid="+e+"&lib="+t;var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};
          ttq.load('{{ $store->tiktok_pixel_id }}');
          ttq.page();
          ttq.track('ViewContent', {
            content_name: '{{ addslashes($product->name) }}',
            content_id: '{{ $product->id }}',
            content_type: 'product',
            value: {{ $product->price }},
            currency: '{{ $lpCurrencyCode }}'
          });
        }(window, document, 'ttq');
    </script>
    <!-- End TikTok Pixel Code -->
    @endif
    
    <style>
        :root {
            --lp-bg: {{ $lpBg }};
            --lp-bg-dark: {{ $lpBgDark }};
            --lp-bg-mid: {{ $lpBgMid }};
            --lp-bg-light: {{ $lpBgLight }};
        }
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Inter', sans-serif;
            padding-bottom: 5.5rem;
            background-color: var(--lp-bg);
        }
        .rtl { direction: rtl; font-family: 'Cairo', 'Tajawal', sans-serif; }
        .landing-hero {
            position: relative;
            overflow-x: hidden;
            overflow-y: visible;
            padding: 0.75rem 0 2.5rem;
            background:
                radial-gradient(ellipse 70% 50% at 85% 15%, rgba(250, 204, 21, 0.18), transparent 55%),
                radial-gradient(ellipse 60% 45% at 10% 90%, rgba(255, 255, 255, 0.12), transparent 50%),
                linear-gradient(145deg, var(--lp-bg-dark) 0%, var(--lp-bg) 48%, var(--lp-bg-light) 100%);
        }
        .landing-band {
            background: linear-gradient(90deg, var(--lp-bg-dark), var(--lp-bg-mid));
        }
        .landing-order-band {
            background: linear-gradient(145deg, var(--lp-bg-dark) 0%, var(--lp-bg) 50%, var(--lp-bg-mid) 100%);
        }
        @media (min-width: 1024px) {
            .landing-hero { padding: 1.25rem 0 3.5rem; }
        }
        .landing-hero__pattern {
            position: absolute;
            inset: 0;
            opacity: 0.12;
            pointer-events: none;
            overflow: hidden;
            background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.4\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');
        }
        .hero-grid {
            display: grid;
            gap: 2rem;
            align-items: start;
        }
        .hero-grid--cta-only {
            grid-template-columns: 1fr;
            max-width: 48rem;
            margin-left: auto;
            margin-right: auto;
        }
        @media (min-width: 1024px) {
            .hero-grid:not(.hero-grid--cta-only) {
                grid-template-columns: 1fr 1fr;
                gap: 3.5rem;
                column-gap: 3.5rem;
            }
        }
        .hero-sticky-col {
            align-self: start;
        }
        @media (min-width: 1024px) {
            .hero-sticky-col {
                position: sticky;
                top: 1.5rem;
                z-index: 20;
            }
        }
        .hero-title {
            font-family: 'Tajawal', 'Cairo', sans-serif;
            letter-spacing: -0.01em;
            line-height: 1.3;
            text-wrap: balance;
            color: #111827;
            text-shadow: none;
        }
        .hero-title-accent {
            display: block;
            margin-top: 0.45rem;
            font-size: 0.62em;
            font-weight: 800;
            letter-spacing: 0;
            color: #92400e;
            text-shadow: none;
        }
        .hero-title-underline {
            width: 4.5rem;
            height: 4px;
            margin: 0.65rem auto 0;
            border-radius: 999px;
            background: linear-gradient(90deg, transparent, #92400e, transparent);
        }
        .hero-description {
            margin: 0 auto;
            max-width: 36rem;
            color: #1f2937;
            font-size: 1.05rem;
            line-height: 1.75;
            font-weight: 500;
        }
        .hero-image-fullbleed {
            position: relative;
            width: 100%;
            margin-top: 0.75rem;
            margin-bottom: 1rem;
            overflow: hidden;
            background: transparent;
            border: none;
            border-radius: 0;
            box-shadow: none;
        }
        .hero-image-fullbleed img {
            position: relative;
            z-index: 2;
            width: 100%;
            height: auto;
            max-height: none;
            display: block;
            object-fit: contain;
            object-position: center;
        }
        @media (min-width: 1024px) {
            .hero-image-fullbleed {
                margin-top: 1rem;
                margin-bottom: 1.25rem;
            }
        }
        .hero-fade-in {
            animation: heroFadeUp 0.7s ease-out both;
        }
        .hero-fade-in-delay {
            animation: heroFadeUp 0.85s ease-out 0.12s both;
        }
        @keyframes heroFadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (prefers-reduced-motion: reduce) {
            .hero-fade-in,
            .hero-fade-in-delay { animation: none; }
        }
    </style>
</head>
<body class="antialiased {{ $lpIsRtl ? 'rtl' : '' }}">
    @php
        // Helper function to format price - show decimals only when needed
        function formatPrice($price) {
            if ($price == floor($price)) {
                return number_format($price, 0);
            } else {
                return rtrim(rtrim(number_format($price, 2), '0'), '.');
            }
        }
        
        // Prepare default testimonials in PHP to avoid JavaScript issues
        $defaultTestimonials = [
            'fr' => [
                ['name' => 'Ahmed', 'text' => "J'ai commandé ce produit et je suis vraiment impressionné par la qualité. Le service client était excellent et la livraison très rapide. Je recommande vivement!", 'rating' => 5],
                ['name' => 'Fatima', 'text' => "Exactement ce que je recherchais! Le rapport qualité-prix est imbattable. Mes amies m'ont déjà demandé où je l'ai acheté. Très satisfaite de mon achat!", 'rating' => 5],
                ['name' => 'Hassan', 'text' => "Produit conforme à la description. L'équipe a été très professionnelle du début à la fin. Je commanderai à nouveau sans hésiter. Merci beaucoup!", 'rating' => 5],
            ],
            'en' => [
                ['name' => 'Ahmed', 'text' => "I ordered this product and I'm really impressed with the quality. Customer service was excellent and delivery was very fast. Highly recommend!", 'rating' => 5],
                ['name' => 'Fatima', 'text' => "Exactly what I was looking for! The value for money is unbeatable. My friends already asked me where I bought it. Very satisfied with my purchase!", 'rating' => 5],
                ['name' => 'Hassan', 'text' => "Product matches the description. The team was very professional from start to finish. I will order again without hesitation. Thank you so much!", 'rating' => 5],
            ],
            'ar' => [
                ['name' => 'أحمد', 'text' => "والله المنتج زوين بزاف، الجودة عالية والتوصيل كان سريع. كنصح بيه أي واحد!", 'rating' => 5],
                ['name' => 'فاطمة', 'text' => "صراحة عجبني بزاف، الثمن مناسب والخدمة ممتازة. غادي نعاود نشري من عندهم.", 'rating' => 5],
                ['name' => 'يوسف', 'text' => "وصلني فالوقت والجودة أحسن مما توقعت. شكرا بزاف على الخدمة!", 'rating' => 5],
            ],
        ];

        // Function to fix testimonials
        function fixTestimonials($data, $lang, $defaults) {
            if (!$data || !is_array($data)) {
                return ['testimonials' => $defaults[$lang] ?? $defaults['fr']];
            }
            
            $testimonials = $data['testimonials'] ?? null;
            $defaultLang = $defaults[$lang] ?? $defaults['fr'];
            
            if (!$testimonials || !is_array($testimonials) || count($testimonials) === 0) {
                $data['testimonials'] = $defaultLang;
                return $data;
            }
            
            $invalidTexts = ['testimonial text', 'test', 'testimonial', 'positive testimonial quote', 'customer review', 'detailed', 'authentic'];
            
            foreach ($testimonials as $index => &$t) {
                $text = $t['text'] ?? $t['review'] ?? $t['comment'] ?? '';
                $isInvalid = empty($text) || strlen(trim($text)) < 10;
                
                if (!$isInvalid) {
                    foreach ($invalidTexts as $invalid) {
                        if (stripos($text, $invalid) !== false) {
                            $isInvalid = true;
                            break;
                        }
                    }
                }
                
                if ($isInvalid) {
                    $t['text'] = $defaultLang[$index % count($defaultLang)]['text'];
                }
                
                if (empty($t['name'])) {
                    $t['name'] = $defaultLang[$index % count($defaultLang)]['name'];
                }
                
                if (empty($t['rating'])) {
                    $t['rating'] = 5;
                }
            }
            
            $data['testimonials'] = $testimonials;
            return $data;
        }

        // Function to sanitize strings for JavaScript
        function sanitizeForJs($data) {
            if (is_string($data)) {
                // Remove control characters and normalize whitespace
                $data = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $data);
                $data = preg_replace('/\s+/', ' ', $data);
                return trim($data);
            }
            if (is_array($data)) {
                return array_map('sanitizeForJs', $data);
            }
            return $data;
        }

        // Fix all language data
        $fixedFr = sanitizeForJs(fixTestimonials($product->landing_page_fr, 'fr', $defaultTestimonials));
        $fixedEn = sanitizeForJs(fixTestimonials($product->landing_page_en, 'en', $defaultTestimonials));
        $fixedAr = sanitizeForJs(fixTestimonials($product->landing_page_ar, 'ar', $defaultTestimonials));
        
        // Sanitize product name and description for JavaScript
        $safeName = sanitizeForJs($product->name);
        $safeDescription = sanitizeForJs(strip_tags($product->description ?? ''));
    @endphp
    <script>
        const productName = {!! json_encode($safeName, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) !!};
        const productDescription = {!! json_encode($safeDescription, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) !!};
        const pageData = {
            fr: {!! json_encode($fixedFr, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) !!},
            en: {!! json_encode($fixedEn, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) !!},
            ar: {!! json_encode($fixedAr, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) !!}
        };
    </script>


    <!-- Hero Section -->
    <section class="landing-hero">
        <div class="landing-hero__pattern"></div>

        @php
            $heroName = trim($product->name ?? '');
            $heroParts = preg_split('/\s*[–—\-]\s*/u', $heroName, 2);
            $heroMain = trim($heroParts[0] ?? $heroName);
            $heroAccent = trim($heroParts[1] ?? '');
            $heroDescription = $product->landing_page_hero_description
                ?? ($product->{'landing_page_' . $lpLang}['hero_description'] ?? null)
                ?? ($product->landing_page_ar['hero_description'] ?? null)
                ?? ($product->landing_page_fr['hero_description'] ?? null)
                ?? ($product->landing_page_en['hero_description'] ?? null);
        @endphp

        <!-- Title -->
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center hero-fade-in">
                <h1 class="hero-title text-3xl sm:text-4xl lg:text-5xl font-black mb-0">
                    {{ $heroMain }}
                    @if($heroAccent !== '')
                        <span class="hero-title-accent">{{ $heroAccent }}</span>
                    @endif
                </h1>
                <div class="hero-title-underline" aria-hidden="true"></div>
            </div>
        </div>

        <!-- Product Image — full-bleed, before description -->
        @if($product->first_image)
        <div class="hero-image-fullbleed hero-fade-in-delay relative z-10">
            <img src="{{ $product->first_image }}"
                 alt="{{ $product->name }}"
                 width="1254"
                 height="1254"
                 loading="eager"
                 decoding="async">
        </div>
        @endif

        <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            @if($heroDescription)
            <div class="text-center hero-fade-in mb-6">
                <p class="hero-description">
                    {{ $heroDescription }}
                </p>
            </div>
            @endif

            <div class="hero-grid hero-grid--cta-only">
                <!-- Right Side: Order CTA & description -->
                <div class="space-y-6">
                    <!-- Order Now CTA — scrolls to order form -->
                    <div class="flex justify-center hero-fade-in">
                        <button type="button"
                                onclick="document.getElementById('order-form').scrollIntoView({behavior: 'smooth', block: 'start'})"
                                class="w-full max-w-md py-4 px-8 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-black text-xl rounded-xl shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 flex items-center justify-center gap-3">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span x-show="currentLang === 'fr'">Commander Maintenant</span>
                            <span x-show="currentLang === 'en'">Order Now</span>
                            <span x-show="currentLang === 'ar'">اطلب الآن</span>
                        </button>
                    </div>

                    <!-- Product Description Content (Same Column) -->
                    @php
                        $hasRealContentRight = function($html) {
                            if (empty($html)) return false;
                            $text = trim(strip_tags($html));
                            $hasImage = stripos($html, '<img') !== false;
                            $hasVideo = stripos($html, '<video') !== false || stripos($html, '<iframe') !== false;
                            return !empty($text) || $hasImage || $hasVideo;
                        };

                        // Hide description images that duplicate the hero/main image
                        $productDesc = $product->stripDuplicateMainImagesFromHtml($product->description ?? '');
                        $hasProductDesc = $hasRealContentRight($productDesc);

                        $descFrRight = $product->stripDuplicateMainImagesFromHtml($product->landing_page_fr['description'] ?? '');
                        $descEnRight = $product->stripDuplicateMainImagesFromHtml($product->landing_page_en['description'] ?? '');
                        $descArRight = $product->stripDuplicateMainImagesFromHtml($product->landing_page_ar['description'] ?? '');

                        $showFrRight = $hasRealContentRight($descFrRight);
                        $showEnRight = $hasRealContentRight($descEnRight);
                        $showArRight = $hasRealContentRight($descArRight);

                        $hasLandingDesc = $showFrRight || $showEnRight || $showArRight;
                        $hasAnyDescription = $hasProductDesc || $hasLandingDesc;
                    @endphp
                    @if($hasAnyDescription)
                    <div class="mt-6 max-w-none text-gray-900 leading-relaxed description-content-right product-desc-centered" x-cloak>
                        {{-- Show product description (from Edit Product page) --}}
                        @if($hasProductDesc)
                        <div class="mb-6">
                            {!! $productDesc !!}
                        </div>
                        @endif
                        
                        {{-- Show landing page builder descriptions if available --}}
                        @if($showFrRight)
                        <div x-show="currentLang === 'fr'" class="{{ $hasProductDesc ? 'mt-6 pt-6' : '' }}">
                            {!! $descFrRight !!}
                        </div>
                        @endif
                        @if($showEnRight)
                        <div x-show="currentLang === 'en'" class="{{ $hasProductDesc ? 'mt-6 pt-6' : '' }}">
                            {!! $descEnRight !!}
                        </div>
                        @endif
                        @if($showArRight)
                        <div x-show="currentLang === 'ar'" dir="rtl" class="{{ $hasProductDesc ? 'mt-6 pt-6' : '' }}">
                            {!! $descArRight !!}
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <style>
        .description-content img,
        .description-content-right img {
            max-width: 100%;
            width: 100%;
            height: auto;
            border-radius: 0;
            margin: 1rem 0;
            display: block;
            box-shadow: none;
        }
        .description-content h1, .description-content h2, .description-content h3, .description-content h4, .description-content h5, .description-content h6,
        .description-content-right h1, .description-content-right h2, .description-content-right h3, .description-content-right h4, .description-content-right h5, .description-content-right h6,
        .description-content p, .description-content-right p,
        .description-content li, .description-content-right li,
        .description-content strong, .description-content-right strong,
        .description-content b, .description-content-right b,
        .description-content em, .description-content-right em,
        .description-content span, .description-content-right span,
        .description-content font, .description-content-right font {
            color: #111827 !important;
        }
        .description-content h1, .description-content h2, .description-content h3,
        .description-content-right h1, .description-content-right h2, .description-content-right h3 {
            font-weight: 700;
            margin-top: 1.25rem;
            margin-bottom: 0.75rem;
        }
        .description-content h1, .description-content-right h1 { font-size: 1.75rem; }
        .description-content h2, .description-content-right h2 { font-size: 1.5rem; }
        .description-content h3, .description-content-right h3 { font-size: 1.25rem; }
        .description-content p, .description-content-right p { margin: 0.75rem 0; line-height: 1.75; }

        /* Center title + small description above product images */
        .product-desc-centered,
        .product-desc-centered *,
        .description-content-right.product-desc-centered h1,
        .description-content-right.product-desc-centered h2,
        .description-content-right.product-desc-centered h3,
        .description-content-right.product-desc-centered p {
            text-align: center !important;
        }
        .description-content-right.product-desc-centered h1,
        .description-content-right.product-desc-centered h2,
        .description-content-right.product-desc-centered h3 {
            margin-left: auto;
            margin-right: auto;
            max-width: 40rem;
            font-weight: 800;
        }
        .description-content-right.product-desc-centered p {
            margin-left: auto !important;
            margin-right: auto !important;
            max-width: 36rem;
            color: #1f2937 !important;
        }
        .description-content-right.product-desc-centered p:has(img),
        .description-content-right.product-desc-centered img {
            max-width: none;
            width: 100vw;
            margin-left: calc(50% - 50vw) !important;
            margin-right: calc(50% - 50vw) !important;
            border-radius: 0;
            display: block;
        }
        
        /* Quill editor text alignment classes */
        .ql-align-center,
        .description-content .ql-align-center,
        .description-content-right .ql-align-center,
        .prose .ql-align-center {
            text-align: center !important;
        }
        .ql-align-right,
        .description-content .ql-align-right,
        .description-content-right .ql-align-right,
        .prose .ql-align-right {
            text-align: right !important;
        }
        .ql-align-left,
        .description-content .ql-align-left,
        .description-content-right .ql-align-left,
        .prose .ql-align-left {
            text-align: left !important;
        }
        .ql-align-justify,
        .description-content .ql-align-justify,
        .description-content-right .ql-align-justify,
        .prose .ql-align-justify {
            text-align: justify !important;
        }
        
        /* Also support inline styles from editor */
        [style*="text-align: center"],
        [style*="text-align:center"] {
            text-align: center !important;
        }
        [style*="text-align: right"],
        [style*="text-align:right"] {
            text-align: right !important;
        }
        [style*="text-align: left"],
        [style*="text-align:left"] {
            text-align: left !important;
        }
        
        .description-content ul, .description-content ol,
        .description-content-right ul, .description-content-right ol {
            margin: 1rem 0;
            padding-left: 1.5rem;
        }
        .description-content ul, .description-content-right ul { list-style-type: disc; }
        .description-content ol, .description-content-right ol { list-style-type: decimal; }
        .description-content li, .description-content-right li { margin: 0.5rem 0; }
        .description-content a, .description-content-right a {
            color: #1d4ed8 !important;
            text-decoration: underline;
        }
        .description-content blockquote, .description-content-right blockquote {
            border-left: 4px solid #3b82f6;
            padding-left: 1rem;
            margin: 1.25rem 0;
            font-style: italic;
            color: #374151 !important;
        }
        .description-content strong, .description-content-right strong { font-weight: 700; }
        .description-content em, .description-content-right em { font-style: italic; }
        .description-content iframe, .description-content video,
        .description-content-right iframe, .description-content-right video {
            max-width: 100%;
            border-radius: 0.75rem;
            margin: 1.5rem 0;
        }
    </style>

    {{-- Description Section removed from here - now displayed inside the contact form box above --}}

    <!-- Order Form Section (before features) -->
    <section class="py-16 lg:py-20 landing-order-band" id="order-section">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl mx-auto space-y-6">
                <!-- Price Display (above form) -->
                <div class="flex flex-wrap items-center justify-center gap-4" id="priceDisplayContainer">
                    @if($product->has_variations && $product->activeVariations->isNotEmpty())
                        <div class="bg-white rounded-2xl px-8 py-4 shadow-xl ring-1 ring-black/5">
                            <div class="text-4xl font-black text-blue-900" id="variationPriceForm">{{ $product->price_range }}</div>
                        </div>
                    @elseif($product->has_promotions && $product->activePromotions->isNotEmpty())
                        @php $firstPromotion = $product->activePromotions->first(); @endphp
                        <div class="bg-white rounded-2xl px-8 py-4 shadow-xl ring-1 ring-black/5 text-center">
                            <div class="text-4xl font-black text-blue-900" id="promotionPriceDisplay">{{ formatPrice($firstPromotion->price) }} <span class="text-xl">{{ $lpCurrencySymbol }}</span></div>
                            @if($product->compare_at_price && $product->compare_at_price > $firstPromotion->price)
                            <div class="text-sm line-through text-gray-400 mt-1" id="promotionComparePriceDisplay">{{ formatPrice($product->compare_at_price) }} {{ $lpCurrencySymbol }}</div>
                            @endif
                        </div>
                        @if($firstPromotion->discount_percentage > 0)
                        <div class="bg-gradient-to-l from-yellow-300 to-amber-400 text-blue-950 px-5 py-2 rounded-xl font-black text-xl shadow-lg shadow-yellow-500/30" id="promotionDiscountDisplay">
                            -{{ $firstPromotion->discount_percentage }}%
                        </div>
                        @endif
                    @else
                        <div class="bg-white rounded-2xl px-8 py-4 shadow-xl ring-1 ring-black/5 text-center">
                            <div class="text-4xl font-black text-blue-900">{{ formatPrice($product->price) }} <span class="text-xl font-bold">{{ $lpCurrencySymbol }}</span></div>
                            @if($product->compare_at_price && $product->compare_at_price > $product->price)
                            <div class="text-sm line-through text-gray-400 mt-1">{{ formatPrice($product->compare_at_price) }} {{ $lpCurrencySymbol }}</div>
                            @endif
                        </div>
                        @if($product->discount_percentage)
                        <div class="bg-gradient-to-l from-yellow-300 to-amber-400 text-blue-950 px-5 py-2 rounded-xl font-black text-xl shadow-lg shadow-yellow-500/30">
                            -{{ $product->discount_percentage }}%
                        </div>
                        @endif
                    @endif
                </div>

                <!-- Quantity-Based Promotions -->
                @if($product->has_promotions && $product->activePromotions->isNotEmpty())
                <div class="bg-white rounded-2xl p-5 shadow-xl ring-1 ring-black/5">
                    <h3 class="text-xl font-bold text-blue-900 mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span x-text="currentLang === 'ar' ? 'عروض الكمية' : (currentLang === 'en' ? 'Quantity Deals' : 'Offres de Quantité')">Offres de Quantité</span>
                    </h3>
                    <div class="space-y-2" id="promotionsContainerForm">
                        @foreach($product->activePromotions as $index => $promotion)
                        <label class="block p-4 bg-gray-50 rounded-xl border-2 cursor-pointer hover:border-amber-400 transition promotion-option-form {{ $index === 0 ? 'border-amber-400 bg-amber-50' : 'border-gray-200' }}"
                               data-promotion-id="{{ $promotion->id }}"
                               data-min-quantity="{{ $promotion->min_quantity }}"
                               data-max-quantity="{{ $promotion->max_quantity ?? '' }}"
                               data-price="{{ $promotion->price }}"
                               data-discount="{{ $promotion->discount_percentage }}">
                            <div style="display:flex;align-items:center;gap:0.5rem;">
                                <input type="radio"
                                       name="selected_promotion_form"
                                       value="{{ $promotion->id }}"
                                       class="w-5 h-5 text-amber-500 accent-amber-500"
                                       style="flex-shrink:0;"
                                       {{ $index === 0 ? 'checked' : '' }}
                                       onchange="updatePromotionDisplayForm(this)">
                                <span style="display:inline-flex;align-items:center;justify-content:center;min-width:2rem;height:2rem;padding:0 0.45rem;border-radius:0.5rem;background:#1e3a8a;color:#fff;font-size:0.9rem;font-weight:900;flex-shrink:0;line-height:1;" title="الكمية">
                                    x{{ $promotion->min_quantity }}
                                </span>
                                <div style="flex:1;min-width:0;font-weight:600;color:#1f2937;font-size:0.9rem;line-height:1.4;white-space:normal;word-break:break-word;">
                                    @if($promotion->label)
                                        {{ $promotion->label }}
                                    @else
                                        <span x-text="currentLang === 'ar' ? 'اشتري' : (currentLang === 'en' ? 'Buy' : 'Achetez')">Achetez</span>
                                    @endif
                                </div>
                                <div style="display:flex;align-items:center;gap:0.35rem;flex-shrink:0;margin-inline-start:auto;">
                                    <span style="font-size:1.1rem;font-weight:900;color:#1e3a8a;white-space:nowrap;">{{ formatPrice($promotion->price) }} <span style="font-size:0.8rem;font-weight:700;">{{ $lpCurrencySymbol }}</span></span>
                                    @if($promotion->discount_percentage > 0)
                                    <span class="text-xs bg-amber-400 text-blue-900 px-2 py-1 rounded-full font-bold">
                                        -{{ $promotion->discount_percentage }}%
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Variations Selector -->
                @if($product->has_variations && $product->activeVariations->isNotEmpty())
                <div class="bg-white rounded-2xl p-5 shadow-xl ring-1 ring-black/5">
                    <h3 class="text-xl font-bold text-blue-900 mb-4"
                        x-text="currentLang === 'ar' ? 'الخيارات المتاحة' : (currentLang === 'en' ? 'Available Options' : 'Options disponibles')">
                        Options disponibles
                    </h3>

                    <div class="space-y-2" id="variationsContainerForm">
                        @foreach($product->activeVariations as $index => $variation)
                        @php
                            $displayName = '';
                            if (!empty($variation->attributes) && is_array($variation->attributes)) {
                                $attrParts = [];
                                foreach ($variation->attributes as $key => $value) {
                                    $attrParts[] = ucfirst($key) . ': ' . $value;
                                }
                                $displayName = implode(' / ', $attrParts);
                            }
                            if (empty($displayName)) {
                                $displayName = 'Option ' . ($index + 1);
                            }
                        @endphp
                        <label class="block p-4 bg-gray-50 rounded-xl border-2 cursor-pointer hover:border-blue-400 transition variation-option-form {{ $variation->is_default ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}"
                               data-variation-id="{{ $variation->id }}"
                               data-price="{{ $variation->price }}"
                               data-compare-price="{{ $variation->compare_at_price ?? 0 }}"
                               data-discount="{{ $variation->discount_percentage }}">
                            <div class="flex items-center gap-3">
                                <input type="radio"
                                       name="selected_variation_form"
                                       value="{{ $variation->id }}"
                                       class="w-5 h-5 text-blue-600 accent-blue-600"
                                       {{ $variation->is_default ? 'checked' : '' }}
                                       onchange="updateVariationDisplayForm(this)">
                                <div class="flex-1 flex items-center justify-between gap-3">
                                    <div>
                                        <div class="font-semibold text-gray-900">{{ $displayName }}</div>
                                        <div class="text-xs text-gray-500">
                                            <span x-text="currentLang === 'ar' ? 'المخزون: {{ $variation->stock }}' : 'Stock: {{ $variation->stock }}'">Stock: {{ $variation->stock }}</span>
                                        </div>
                                    </div>
                                    <div class="text-left shrink-0">
                                        <div class="text-2xl font-black text-blue-900">{{ formatPrice($variation->price) }} <span class="text-base font-bold">{{ $lpCurrencySymbol }}</span></div>
                                        @if($variation->compare_at_price && $variation->compare_at_price > $variation->price)
                                        <div class="text-xs line-through text-gray-400">{{ formatPrice($variation->compare_at_price) }} {{ $lpCurrencySymbol }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Contact Form (White Box) -->
                @php
                    $formCopy = match ($lpLang) {
                        'fr' => [
                            'title' => 'Pour commander, veuillez remplir le formulaire ci-dessous',
                            'subtitle' => 'Renseignez vos informations et nous vous contacterons rapidement',
                            'errors' => 'Veuillez corriger les erreurs suivantes :',
                            'submit' => 'Envoyer',
                            'submitting' => 'Envoi en cours...',
                            'choose' => 'Choisir...',
                            'field' => 'Champ',
                            'defaults' => [
                                ['id' => 'name', 'type' => 'text', 'label' => 'Nom', 'placeholder' => 'Votre nom', 'required' => true],
                                ['id' => 'phone', 'type' => 'tel', 'label' => 'Téléphone', 'placeholder' => 'Votre téléphone', 'required' => true],
                                ['id' => 'note', 'type' => 'textarea', 'label' => 'Notes', 'placeholder' => 'Notes (optionnel)', 'required' => false],
                            ],
                        ],
                        'en' => [
                            'title' => 'To order, please fill out the form below',
                            'subtitle' => 'Enter your details and we will contact you shortly',
                            'errors' => 'Please correct the following errors:',
                            'submit' => 'Submit',
                            'submitting' => 'Submitting...',
                            'choose' => 'Choose...',
                            'field' => 'Field',
                            'defaults' => [
                                ['id' => 'name', 'type' => 'text', 'label' => 'Name', 'placeholder' => 'Your name', 'required' => true],
                                ['id' => 'phone', 'type' => 'tel', 'label' => 'Phone', 'placeholder' => 'Your phone', 'required' => true],
                                ['id' => 'note', 'type' => 'textarea', 'label' => 'Notes', 'placeholder' => 'Notes (optional)', 'required' => false],
                            ],
                        ],
                        default => [
                            'title' => 'للطلب المرجو ملئ الاستمارة أدناه',
                            'subtitle' => 'عبّي المعلومات ديالك و غادي نتصلو بيك في أقرب وقت',
                            'errors' => 'يرجى تصحيح الأخطاء التالية:',
                            'submit' => 'إرسال',
                            'submitting' => 'جاري الإرسال...',
                            'choose' => 'اختر...',
                            'field' => 'حقل',
                            'defaults' => [
                                ['id' => 'name', 'type' => 'text', 'label' => 'الاسم', 'placeholder' => 'الاسم', 'required' => true],
                                ['id' => 'phone', 'type' => 'tel', 'label' => 'الهاتف', 'placeholder' => 'الهاتف', 'required' => true],
                                ['id' => 'note', 'type' => 'textarea', 'label' => 'ملاحظات', 'placeholder' => 'ملاحظات', 'required' => false],
                            ],
                        ],
                    };
                    $formAlign = $lpIsRtl ? 'text-right' : 'text-left';
                @endphp
                <div id="order-form" class="bg-white rounded-2xl p-8 lg:p-10 shadow-2xl shadow-blue-950/30 ring-1 ring-black/5 scroll-mt-6">
                    <h2 class="text-2xl lg:text-3xl font-black mb-2 text-gray-900 text-center" @if($lpIsRtl) style="font-family: 'Tajawal', 'Cairo', sans-serif;" @endif>
                        {{ $formCopy['title'] }}
                    </h2>
                    <p class="text-center text-gray-500 mb-6 text-sm">{{ $formCopy['subtitle'] }}</p>

                    @if ($errors->any())
                    <div class="mb-6 rounded-xl border-2 border-red-500 bg-red-50 px-4 py-3 text-red-800">
                        <p class="font-semibold mb-2 text-center">{{ $formCopy['errors'] }}</p>
                        <ul class="list-disc list-inside text-sm {{ $formAlign }}">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form id="landing-order-form" method="POST" action="{{ \App\Support\StoreDomain::submitLeadUrl($store, $product->slug) }}" class="space-y-6" @if($lpIsRtl) dir="rtl" @endif>
                        @csrf
                        <input type="hidden" name="language" value="{{ $lpLang }}">

                        {{-- Hidden inputs for order details --}}
                        @if($product->has_promotions && $product->activePromotions->isNotEmpty())
                            @php $defaultPromotion = $product->activePromotions->first(); @endphp
                            <input type="hidden" name="selected_promotion_id" id="selected_promotion_id" value="{{ $defaultPromotion->id }}">
                            <input type="hidden" name="selected_price" id="selected_price" value="{{ $defaultPromotion->price }}">
                        @elseif($product->has_variations && $product->activeVariations->isNotEmpty())
                            @php $defaultVariation = $product->activeVariations->where('is_default', true)->first() ?? $product->activeVariations->first(); @endphp
                            <input type="hidden" name="selected_variation_id" id="selected_variation_id" value="{{ $defaultVariation->id }}">
                            <input type="hidden" name="selected_price" id="selected_price" value="{{ $defaultVariation->price }}">
                        @else
                            <input type="hidden" name="selected_price" id="selected_price" value="{{ $product->price }}">
                        @endif

                        @php
                            $formFields = $product->form_fields ?? $formCopy['defaults'];
                        @endphp

                        @foreach($formFields as $field)
                            @php
                                $fieldLabel = \App\Support\LandingFormFields::resolveLabel($field, $lpLang, $formCopy['field']);
                                $fieldPlaceholder = \App\Support\LandingFormFields::resolvePlaceholder($field, $lpLang);
                            @endphp
                            <div>
                                <label class="block text-gray-900 font-bold mb-2 {{ $formAlign }}">
                                    {{ $fieldLabel }}
                                    @if($field['required'] ?? false)
                                        <span class="text-red-500">*</span>
                                    @endif
                                </label>

                                @if(($field['type'] ?? 'text') === 'textarea')
                                    <textarea
                                        name="{{ $field['id'] }}"
                                        rows="3"
                                        {{ ($field['required'] ?? false) ? 'required' : '' }}
                                        placeholder="{{ $fieldPlaceholder }}"
                                        class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 focus:outline-none transition-all resize-none {{ $formAlign }}"></textarea>
                                @elseif(($field['type'] ?? 'text') === 'select')
                                    <select
                                        name="{{ $field['id'] }}"
                                        {{ ($field['required'] ?? false) ? 'required' : '' }}
                                        class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 focus:outline-none transition-all {{ $formAlign }}">
                                        <option value="">{{ $fieldPlaceholder ?: $formCopy['choose'] }}</option>
                                        @if(!empty($field['options']))
                                            @foreach($field['options'] as $option)
                                                <option value="{{ $option }}">{{ $option }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                @else
                                    <input
                                        type="{{ $field['type'] ?? 'text' }}"
                                        name="{{ $field['id'] }}"
                                        {{ ($field['required'] ?? false) ? 'required' : '' }}
                                        placeholder="{{ $fieldPlaceholder }}"
                                        class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 focus:outline-none transition-all {{ $formAlign }}">
                                @endif

                                @error($field['id'])
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach

                        <button type="submit" id="landing-order-submit"
                                class="w-full py-4 bg-gradient-to-r from-blue-600 to-blue-800 hover:from-blue-700 hover:to-blue-900 text-white font-black text-lg rounded-xl transition-all duration-300 shadow-xl hover:shadow-2xl transform hover:scale-105 flex items-center justify-center gap-3 disabled:opacity-70 disabled:cursor-not-allowed disabled:hover:scale-100 disabled:pointer-events-none">
                            <svg class="w-6 h-6 submit-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <span class="submit-label">{{ $formCopy['submit'] }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 lg:py-20" x-cloak>
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl lg:text-4xl font-black text-gray-900 mb-4">
                    <span x-show="currentLang === 'fr'">Les caractéristiques</span>
                    <span x-show="currentLang === 'en'">Features</span>
                    <span x-show="currentLang === 'ar'">المميزات</span>
                </h2>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <template x-for="(feature, index) in (pageData[currentLang]?.features || [])" :key="index">
                    <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-200 text-center">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-3xl" x-text="feature.icon">✓</span>
                        </div>
                        <h3 class="text-xl font-bold mb-3 text-gray-900" x-text="feature.title"></h3>
                        <p class="text-gray-600 leading-relaxed" x-text="feature.description"></p>
                    </div>
                </template>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-16 lg:py-20" x-cloak>
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl lg:text-4xl font-black text-gray-900 mb-4" x-text="pageData[currentLang]?.testimonials_title || 'Témoignages'"></h2>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <template x-for="(testimonial, index) in (pageData[currentLang]?.testimonials || [])" :key="index">
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl p-6 shadow-lg border-2 border-green-200">
                        <!-- Stars -->
                        <div class="flex gap-1 mb-4">
                            <template x-for="i in (testimonial.rating || 5)" :key="i">
                                <svg class="w-5 h-5 text-yellow-400 fill-current" viewBox="0 0 20 20">
                                    <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                </svg>
                            </template>
                        </div>
                        <p class="text-gray-700 mb-4 italic leading-relaxed" x-text="testimonial.text || ''"></p>
                        <p class="text-gray-900 font-bold" x-text="testimonial.name || ''"></p>
                    </div>
                </template>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-16 lg:py-20" x-cloak x-data="{ openFaq: null }">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl lg:text-4xl font-black text-gray-900 mb-4" x-text="pageData[currentLang]?.faqs_title || 'Questions Fréquentes'"></h2>
            </div>
            
            <div class="max-w-3xl mx-auto space-y-4">
                <template x-for="(faq, index) in (pageData[currentLang]?.faqs || [])" :key="index">
                    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                        <button @click="openFaq = openFaq === index ? null : index" 
                                class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 transition">
                            <span class="font-bold text-gray-900 text-lg" x-text="faq.question"></span>
                            <svg :class="{'rotate-180': openFaq === index}" class="w-6 h-6 text-blue-600 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="openFaq === index" x-collapse class="px-6 pb-4">
                            <p class="text-gray-600 leading-relaxed" x-text="faq.answer"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </section>

    <!-- Trust / COD Services -->
    <section class="py-12 lg:py-16" @if($lpIsRtl) dir="rtl" @endif>
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl lg:text-3xl font-black text-center mb-8 text-gray-900">
                <span x-show="currentLang === 'ar'">خدماتنا لراحتك</span>
                <span x-show="currentLang === 'fr'">Nos services pour vous</span>
                <span x-show="currentLang === 'en'">Our services for you</span>
            </h2>
            <div class="cod-services-grid" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:0.85rem;max-width:56rem;margin:0 auto;">
                <div style="background:#fff;border:1px solid #dbeafe;border-radius:1rem;padding:1.1rem 0.85rem;text-align:center;box-shadow:0 8px 20px rgba(30,58,138,0.06);">
                    <div style="width:3rem;height:3rem;margin:0 auto 0.65rem;border-radius:9999px;background:#dbeafe;display:flex;align-items:center;justify-content:center;">
                        <svg style="width:1.6rem;height:1.6rem;color:#1d4ed8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h11v8H3V7zm11 3h4l3 3v2h-7v-5zM6.5 18.5a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm10 0a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>
                        </svg>
                    </div>
                    <div style="font-weight:800;color:#1e3a8a;font-size:0.95rem;line-height:1.35;">
                        <span x-show="currentLang === 'ar'">توصيل مجاني</span>
                        <span x-show="currentLang === 'fr'">Livraison gratuite</span>
                        <span x-show="currentLang === 'en'">Free shipping</span>
                    </div>
                </div>

                <div style="background:#fff;border:1px solid #dbeafe;border-radius:1rem;padding:1.1rem 0.85rem;text-align:center;box-shadow:0 8px 20px rgba(30,58,138,0.06);">
                    <div style="width:3rem;height:3rem;margin:0 auto 0.65rem;border-radius:9999px;background:#dcfce7;display:flex;align-items:center;justify-content:center;">
                        <svg style="width:1.6rem;height:1.6rem;color:#15803d;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a1 1 0 11-2 0 1 1 0 012 0z"/>
                        </svg>
                    </div>
                    <div style="font-weight:800;color:#1e3a8a;font-size:0.95rem;line-height:1.35;">
                        <span x-show="currentLang === 'ar'">الدفع عند الاستلام</span>
                        <span x-show="currentLang === 'fr'">Paiement à la livraison</span>
                        <span x-show="currentLang === 'en'">Cash on delivery</span>
                    </div>
                </div>

                <div style="background:#fff;border:1px solid #dbeafe;border-radius:1rem;padding:1.1rem 0.85rem;text-align:center;box-shadow:0 8px 20px rgba(30,58,138,0.06);">
                    <div style="width:3rem;height:3rem;margin:0 auto 0.65rem;border-radius:9999px;background:#fef3c7;display:flex;align-items:center;justify-content:center;">
                        <svg style="width:1.6rem;height:1.6rem;color:#b45309;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div style="font-weight:800;color:#1e3a8a;font-size:0.95rem;line-height:1.35;">
                        <span x-show="currentLang === 'ar'">أداء آمن</span>
                        <span x-show="currentLang === 'fr'">Paiement sécurisé</span>
                        <span x-show="currentLang === 'en'">Secure payment</span>
                    </div>
                </div>

                <div style="background:#fff;border:1px solid #dbeafe;border-radius:1rem;padding:1.1rem 0.85rem;text-align:center;box-shadow:0 8px 20px rgba(30,58,138,0.06);">
                    <div style="width:3rem;height:3rem;margin:0 auto 0.65rem;border-radius:9999px;background:#e0e7ff;display:flex;align-items:center;justify-content:center;">
                        <svg style="width:1.6rem;height:1.6rem;color:#4338ca;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div style="font-weight:800;color:#1e3a8a;font-size:0.95rem;line-height:1.35;">
                        @if($isMoroccoMarket)
                            <span x-show="currentLang === 'ar'">التوصيل لجميع مدن المغرب</span>
                            <span x-show="currentLang === 'fr'">Livraison dans toutes les villes du Maroc</span>
                            <span x-show="currentLang === 'en'">Delivery to all cities of Morocco</span>
                        @else
                            <span x-show="currentLang === 'ar'">التوصيل لجميع المدن</span>
                            <span x-show="currentLang === 'fr'">Livraison dans tout le pays</span>
                            <span x-show="currentLang === 'en'">Nationwide delivery</span>
                        @endif
                    </div>
                </div>
            </div>
            <style>
                @media (min-width: 768px) {
                    .cod-services-grid { grid-template-columns: repeat(4, minmax(0, 1fr)) !important; }
                }
            </style>
        </div>
    </section>

    <!-- Product Gallery -->
    @if(false)
    <section class="py-16 lg:py-20 bg-white">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl lg:text-4xl font-black text-center mb-12 text-gray-900">
                <span x-show="currentLang === 'fr'">Galerie de Photos</span>
                <span x-show="currentLang === 'en'">Photo Gallery</span>
                <span x-show="currentLang === 'ar'">معرض الصور</span>
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($product->all_images as $image)
                <div class="relative group overflow-hidden rounded-xl shadow-lg transform hover:scale-105 transition-all duration-300 cursor-pointer"
                     onclick="openImageModal('{{ $image }}')">
                    <img src="{{ $image }}" 
                         alt="{{ $product->name }}" 
                         class="w-full h-64 object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-blue-900/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                        </svg>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <script>
        function openImageModal(imageSrc) {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black/90 z-[100] flex items-center justify-center p-4';
            modal.onclick = (e) => {
                if (e.target === modal || e.target.tagName === 'BUTTON') {
                    modal.remove();
                }
            };
            
            modal.innerHTML = `
                <div class="relative max-w-7xl w-full">
                    <button class="absolute top-4 right-4 text-white hover:text-gray-300 transition z-10">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    <img src="${imageSrc}" alt="Product image" class="w-full h-auto max-h-[90vh] object-contain rounded-lg shadow-2xl" />
                </div>
            `;
            
            document.body.appendChild(modal);
        }
    </script>
    @endif

    @if($product->has_promotions && $product->activePromotions->isNotEmpty())
    <script>
        // Helper function to format price
        function formatPriceJs(price) {
            if (price % 1 === 0) {
                return price.toFixed(0);
            } else {
                return parseFloat(price.toFixed(2)).toString();
            }
        }
        
        // Promotion selection handler for form
        function updatePromotionDisplayForm(radio) {
            const option = radio.closest('.promotion-option-form');
            const minQuantity = parseInt(option.dataset.minQuantity);
            const maxQuantity = option.dataset.maxQuantity ? parseInt(option.dataset.maxQuantity) : null;
            const price = parseFloat(option.dataset.price);
            const discount = parseFloat(option.dataset.discount) || 0;
            
            // Remove active state from all options
            document.querySelectorAll('.promotion-option-form').forEach(opt => {
                opt.classList.remove('border-amber-400', 'bg-amber-50');
                opt.classList.add('border-gray-200', 'bg-gray-50');
            });
            
            // Add active state to selected option
            option.classList.remove('border-gray-200');
            option.classList.add('border-amber-400', 'bg-amber-50');
            
            // Update the price display
            const priceDisplay = document.getElementById('promotionPriceDisplay');
            if (priceDisplay) {
                priceDisplay.innerHTML = formatPriceJs(price) + ' <span class="text-xl">{{ $lpCurrencySymbol }}</span>';
            }
            
            // Update discount badge
            const discountDisplay = document.getElementById('promotionDiscountDisplay');
            if (discountDisplay) {
                if (discount > 0) {
                    discountDisplay.textContent = '-' + discount + '%';
                    discountDisplay.style.display = '';
                } else {
                    discountDisplay.style.display = 'none';
                }
            }
            
            // Update hidden input for form submission
            const hiddenPromotionInput = document.getElementById('selected_promotion_id');
            if (hiddenPromotionInput) {
                hiddenPromotionInput.value = option.dataset.promotionId;
            }
            const hiddenPriceInput = document.getElementById('selected_price');
            if (hiddenPriceInput) {
                hiddenPriceInput.value = price;
            }
        }
        
        // Set default promotion on page load
        document.addEventListener('DOMContentLoaded', function() {
            const defaultRadio = document.querySelector('input[name="selected_promotion_form"]:checked');
            if (defaultRadio) {
                updatePromotionDisplayForm(defaultRadio);
            }
        });
    </script>
    @endif

    @if($product->has_variations && $product->activeVariations->isNotEmpty())
    <script>
        // Variation selection handler for form
        function updateVariationDisplayForm(radio) {
            const option = radio.closest('.variation-option-form');
            const price = parseFloat(option.dataset.price);
            const comparePrice = parseFloat(option.dataset.comparePrice) || 0;
            const discount = option.dataset.discount;
            
            // Remove active state from all options
            document.querySelectorAll('.variation-option-form').forEach(opt => {
                opt.classList.remove('border-blue-500', 'bg-blue-50');
                opt.classList.add('border-gray-200', 'bg-gray-50');
            });
            
            // Add active state to selected option
            option.classList.remove('border-gray-200');
            option.classList.add('border-blue-500', 'bg-blue-50');
            
            // Update price display in form
            const priceContainer = document.getElementById('variationPriceForm');
            if (priceContainer) {
                // Format price - show decimals only when needed
                let formattedPrice = price % 1 === 0 ? price.toFixed(0) : parseFloat(price.toFixed(2)).toString();
                let priceHtml = formattedPrice + ' <span class="text-xl">{{ $lpCurrencySymbol }}</span>';
                priceContainer.innerHTML = priceHtml;
            }
            
            // Update hidden input for form submission
            const hiddenVariationInput = document.getElementById('selected_variation_id');
            if (hiddenVariationInput) {
                hiddenVariationInput.value = option.dataset.variationId;
            }
            const hiddenPriceInput = document.getElementById('selected_price');
            if (hiddenPriceInput) {
                hiddenPriceInput.value = price;
            }
        }
        
        // Set default variation on page load
        document.addEventListener('DOMContentLoaded', function() {
            const defaultRadio = document.querySelector('input[name="selected_variation_form"]:checked');
            if (defaultRadio) {
                updateVariationDisplayForm(defaultRadio);
            }
        });
    </script>
    @endif


    <script>
        (function () {
            const form = document.getElementById('landing-order-form');
            if (!form) return;

            let isSubmitting = false;

            form.addEventListener('submit', function (event) {
                if (isSubmitting) {
                    event.preventDefault();
                    return;
                }

                isSubmitting = true;

                const btn = document.getElementById('landing-order-submit');
                if (btn) {
                    btn.disabled = true;
                    btn.setAttribute('aria-busy', 'true');
                    const label = btn.querySelector('.submit-label');
                    if (label) {
                        label.textContent = @json($formCopy['submitting'] ?? '...');
                    }
                }
            });
        })();
    </script>

    <!-- Sticky Order Button - Full Width Bottom Bar -->
    <button onclick="document.getElementById('order-form').scrollIntoView({behavior: 'smooth', block: 'center'})" 
            class="fixed bottom-0 left-0 right-0 z-50 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-black text-xl px-6 py-5 shadow-2xl hover:shadow-3xl transition-all duration-300 flex items-center justify-center gap-3 group"
            style="animation: gentle-pulse 2s ease-in-out infinite;"
            x-cloak>
        <svg class="w-7 h-7 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <span x-show="currentLang === 'fr'">Commander Maintenant</span>
        <span x-show="currentLang === 'en'">Order Now</span>
        <span x-show="currentLang === 'ar'">اطلب الآن</span>
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
        </svg>
    </button>

    <style>
        @keyframes gentle-pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.02); opacity: 0.95; }
        }
        .fixed button:hover {
            animation: none !important;
        }
    </style>

</body>
</html>
