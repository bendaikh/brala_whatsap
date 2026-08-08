<!DOCTYPE html>
<html lang="ar" class="scroll-smooth" x-data="{ currentLang: 'ar' }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $product->name }}</title>
    <meta name="description" content="{{ Str::limit(strip_tags($product->description), 160) }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
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
                'currency' => 'MAD',
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
            currency: 'درهم'
          });
        }(window, document, 'ttq');
    </script>
    <!-- End TikTok Pixel Code -->
    @endif
    
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }
        .rtl { direction: rtl; font-family: 'Cairo', sans-serif; }
        .section-divider {
            height: 40px;
            background: linear-gradient(180deg, #1e40af 0%, #3b82f6 100%);
        }
    </style>
</head>
<body class="antialiased bg-white rtl">
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
                ['name' => 'أحمد', 'text' => "طلبت هذا المنتج وأنا معجب جداً بالجودة. خدمة العملاء كانت ممتازة والتوصيل سريع جداً. أنصح به بشدة!", 'rating' => 5],
                ['name' => 'فاطمة', 'text' => "بالضبط ما كنت أبحث عنه! القيمة مقابل المال لا تُضاهى. صديقاتي سألنني من أين اشتريته. راضية جداً عن مشترياتي!", 'rating' => 5],
                ['name' => 'حسن', 'text' => "المنتج مطابق للوصف. الفريق كان محترفاً جداً من البداية إلى النهاية. سأطلب مرة أخرى دون تردد. شكراً جزيلاً!", 'rating' => 5],
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
    <section class="relative bg-gradient-to-br from-blue-700 via-blue-600 to-blue-800 py-16 lg:py-24">
        <div class="absolute inset-0 opacity-10 overflow-hidden pointer-events-none">
            <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.4\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        </div>

        <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid lg:grid-cols-2 gap-12 items-start">
                <!-- Left Side: Title + Image (STICKY - stays when scrolling) -->
                <div class="lg:sticky lg:top-8 self-start space-y-8">
                    <!-- Title Only -->
                    <div class="text-white text-center">
                        <h1 class="text-4xl lg:text-5xl xl:text-6xl font-black mb-6 leading-tight drop-shadow-lg">
                            {{ $product->name }}
                        </h1>
                    </div>

                    <!-- Product Image -->
                    @if($product->first_image)
                    <div class="relative rounded-2xl overflow-hidden shadow-2xl">
                        <img src="{{ $product->first_image }}" 
                             alt="{{ $product->name }}" 
                             class="w-full h-[400px] lg:h-[500px] object-cover">
                    </div>
                    @endif

                </div>

                <!-- Right Side: Price, Promotions, Variations & Contact Form (SCROLLS) -->
                <div class="space-y-6">
                    <!-- Price Display -->
                    <div class="flex flex-wrap items-center justify-center gap-4" id="priceDisplayContainer">
                        @if($product->has_variations && $product->activeVariations->isNotEmpty())
                            <div class="bg-white/20 backdrop-blur-sm rounded-2xl px-6 py-3 border border-white/30">
                                <div class="text-4xl font-black text-white" id="variationPriceForm">{{ $product->price_range }}</div>
                            </div>
                        @elseif($product->has_promotions && $product->activePromotions->isNotEmpty())
                            @php $firstPromotion = $product->activePromotions->first(); @endphp
                            <div class="bg-white/20 backdrop-blur-sm rounded-2xl px-6 py-3 border border-white/30">
                                <div class="text-4xl font-black text-white" id="promotionPriceDisplay">{{ formatPrice($firstPromotion->price) }} <span class="text-xl">درهم</span></div>
                                @if($product->compare_at_price && $product->compare_at_price > $firstPromotion->price)
                                <div class="text-sm line-through text-white/70" id="promotionComparePriceDisplay">{{ formatPrice($product->compare_at_price) }} درهم</div>
                                @endif
                            </div>
                            @if($firstPromotion->discount_percentage > 0)
                            <div class="bg-yellow-400 text-blue-900 px-5 py-2 rounded-xl font-black text-xl shadow-lg" id="promotionDiscountDisplay">
                                -{{ $firstPromotion->discount_percentage }}%
                            </div>
                            @endif
                        @else
                            <div class="bg-white/20 backdrop-blur-sm rounded-2xl px-6 py-3 border border-white/30">
                                <div class="text-4xl font-black text-white">{{ formatPrice($product->price) }} <span class="text-xl">درهم</span></div>
                                @if($product->compare_at_price && $product->compare_at_price > $product->price)
                                <div class="text-sm line-through text-white/70">{{ formatPrice($product->compare_at_price) }} درهم</div>
                                @endif
                            </div>
                            @if($product->discount_percentage)
                            <div class="bg-yellow-400 text-blue-900 px-5 py-2 rounded-xl font-black text-xl shadow-lg">
                                -{{ $product->discount_percentage }}%
                            </div>
                            @endif
                        @endif
                    </div>

                    <!-- Quantity-Based Promotions -->
                    @if($product->has_promotions && $product->activePromotions->isNotEmpty())
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-5 border border-white/20">
                        <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                            <svg class="w-6 h-6 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span x-text="currentLang === 'ar' ? 'عروض الكمية' : (currentLang === 'en' ? 'Quantity Deals' : 'Offres de Quantité')">Offres de Quantité</span>
                        </h3>
                        <div class="space-y-2" id="promotionsContainerForm">
                            @foreach($product->activePromotions as $index => $promotion)
                            <label class="block p-4 bg-white/10 backdrop-blur-sm rounded-xl border-2 cursor-pointer hover:border-yellow-300 transition promotion-option-form {{ $index === 0 ? 'border-yellow-300' : 'border-white/20' }}"
                                   data-promotion-id="{{ $promotion->id }}"
                                   data-min-quantity="{{ $promotion->min_quantity }}"
                                   data-max-quantity="{{ $promotion->max_quantity ?? '' }}"
                                   data-price="{{ $promotion->price }}"
                                   data-discount="{{ $promotion->discount_percentage }}">
                                <div class="flex items-center gap-3">
                                    <input type="radio" 
                                           name="selected_promotion_form" 
                                           value="{{ $promotion->id }}" 
                                           class="w-5 h-5 text-yellow-400"
                                           {{ $index === 0 ? 'checked' : '' }}
                                           onchange="updatePromotionDisplayForm(this)">
                                    <div class="flex-1 flex items-center justify-between">
                                        <div class="font-semibold text-yellow-200">
                                            @if($promotion->label)
                                                {{ $promotion->label }}
                                            @else
                                                <span x-text="currentLang === 'ar' ? 'اشتري' : (currentLang === 'en' ? 'Buy' : 'Achetez')">Achetez</span>
                                            @endif
                                            {{ $promotion->quantity_range }}
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-2xl font-black text-white">{{ formatPrice($promotion->price) }} درهم</span>
                                            @if($promotion->discount_percentage > 0)
                                            <span class="text-xs bg-yellow-400 text-blue-900 px-2 py-1 rounded-full font-bold">
                                                -{{ $promotion->discount_percentage }}%
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Variations Selector -->
                    @if($product->has_variations && $product->activeVariations->isNotEmpty())
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-5 border border-white/20">
                        <h3 class="text-xl font-bold text-white mb-4" 
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
                            <label class="block p-4 bg-white/10 backdrop-blur-sm rounded-xl border-2 cursor-pointer hover:border-white/50 transition variation-option-form {{ $variation->is_default ? 'border-white/50' : 'border-white/20' }}"
                                   data-variation-id="{{ $variation->id }}"
                                   data-price="{{ $variation->price }}"
                                   data-compare-price="{{ $variation->compare_at_price ?? 0 }}"
                                   data-discount="{{ $variation->discount_percentage }}">
                                <div class="flex items-center gap-3">
                                    <input type="radio" 
                                           name="selected_variation_form" 
                                           value="{{ $variation->id }}" 
                                           class="w-5 h-5 text-blue-400"
                                           {{ $variation->is_default ? 'checked' : '' }}
                                           onchange="updateVariationDisplayForm(this)">
                                    <div class="flex-1 flex items-center justify-between">
                                        <div>
                                            <div class="font-semibold text-white">{{ $displayName }}</div>
                                            <div class="text-xs text-white/70">
                                                <span x-text="currentLang === 'ar' ? 'المخزون: {{ $variation->stock }}' : 'Stock: {{ $variation->stock }}'">Stock: {{ $variation->stock }}</span>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-2xl font-black text-white">{{ formatPrice($variation->price) }} درهم</div>
                                            @if($variation->compare_at_price && $variation->compare_at_price > $variation->price)
                                            <div class="text-xs line-through text-white/70">{{ formatPrice($variation->compare_at_price) }} درهم</div>
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
                    <div id="order-form" class="bg-white rounded-2xl p-8 lg:p-10 shadow-2xl">
                        <h2 class="text-2xl lg:text-3xl font-black mb-6 text-gray-900 text-center">
                            للطلب المرجو ملئ الاستمارة أدناه
                        </h2>

                        @if ($errors->any())
                        <div class="mb-6 rounded-xl border-2 border-red-500 bg-red-50 px-4 py-3 text-red-800">
                            <p class="font-semibold mb-2 text-center">يرجى تصحيح الأخطاء التالية:</p>
                            <ul class="list-disc list-inside text-sm text-right">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <form id="landing-order-form" method="POST" action="{{ \App\Support\StoreDomain::submitLeadUrl($store, $product->slug) }}" class="space-y-6" dir="rtl">
                            @csrf
                            <input type="hidden" name="language" value="ar">
                            
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
                                // Get form fields or use defaults
                                $formFields = $product->form_fields ?? [
                                    ['id' => 'name', 'type' => 'text', 'label_ar' => 'الاسم', 'placeholder_ar' => 'الاسم', 'required' => true],
                                    ['id' => 'phone', 'type' => 'tel', 'label_ar' => 'الهاتف', 'placeholder_ar' => 'الهاتف', 'required' => true],
                                    ['id' => 'note', 'type' => 'textarea', 'label_ar' => 'ملاحظات', 'placeholder_ar' => 'ملاحظات', 'required' => false],
                                ];
                            @endphp

                            @foreach($formFields as $field)
                                <div>
                                    <label class="block text-gray-900 font-bold mb-2 text-right">
                                        {{ $field['label_ar'] ?? $field['label'] ?? 'حقل' }}
                                        @if($field['required'] ?? false)
                                            <span class="text-red-500">*</span>
                                        @endif
                                    </label>
                                    
                                    @if(($field['type'] ?? 'text') === 'textarea')
                                        <textarea 
                                            name="{{ $field['id'] }}" 
                                            rows="3"
                                            {{ ($field['required'] ?? false) ? 'required' : '' }}
                                            placeholder="{{ $field['placeholder_ar'] ?? $field['placeholder'] ?? '' }}"
                                            class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 focus:outline-none transition-all resize-none text-right"></textarea>
                                    @elseif(($field['type'] ?? 'text') === 'select')
                                        <select 
                                            name="{{ $field['id'] }}"
                                            {{ ($field['required'] ?? false) ? 'required' : '' }}
                                            class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 focus:outline-none transition-all text-right">
                                            <option value="">{{ $field['placeholder_ar'] ?? 'اختر...' }}</option>
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
                                            placeholder="{{ $field['placeholder_ar'] ?? $field['placeholder'] ?? '' }}"
                                            class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 focus:outline-none transition-all text-right">
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
                                <span class="submit-label">إرسال</span>
                            </button>
                        </form>

                        <!-- Alternative Contact Methods -->
                        @if(isset($settings) && ($settings->whatsapp_number || $settings->contact_phone))
                        <div class="mt-8 pt-8 border-t-2 border-gray-200">
                            <p class="text-gray-700 text-center mb-4 font-semibold">
                                أو اتصل بنا مباشرة:
                            </p>
                            <div class="grid grid-cols-2 gap-4">
                                @if($settings->whatsapp_number)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $settings->whatsapp_number) }}?text={{ urlencode('مرحباً، أنا مهتم بـ ' . $product->name) }}" 
                                   target="_blank" 
                                   class="flex items-center justify-center gap-2 px-4 py-3 bg-green-500 hover:bg-green-600 text-white font-bold rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                    </svg>
                                    <span class="text-sm">واتساب</span>
                                </a>
                                @endif
                                
                                @if($settings->contact_phone)
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $settings->contact_phone) }}" 
                                   class="flex items-center justify-center gap-2 px-4 py-3 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    <span class="text-sm">اتصل</span>
                                </a>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Product Description Content (After Form, Same Column) -->
                    @php
                        $hasRealContentRight = function($html) {
                            if (empty($html)) return false;
                            $text = trim(strip_tags($html));
                            $hasImage = stripos($html, '<img') !== false;
                            $hasVideo = stripos($html, '<video') !== false || stripos($html, '<iframe') !== false;
                            return !empty($text) || $hasImage || $hasVideo;
                        };
                        
                        // Check product description field
                        $productDesc = $product->description ?? '';
                        $hasProductDesc = $hasRealContentRight($productDesc);
                        
                        // Check landing page builder descriptions
                        $descFrRight = $product->landing_page_fr['description'] ?? '';
                        $descEnRight = $product->landing_page_en['description'] ?? '';
                        $descArRight = $product->landing_page_ar['description'] ?? '';
                        
                        $showFrRight = $hasRealContentRight($descFrRight);
                        $showEnRight = $hasRealContentRight($descEnRight);
                        $showArRight = $hasRealContentRight($descArRight);
                        
                        $hasLandingDesc = $showFrRight || $showEnRight || $showArRight;
                        $hasAnyDescription = $hasProductDesc || $hasLandingDesc;
                    @endphp
                    @if($hasAnyDescription)
                    <div class="mt-6 prose prose-lg max-w-none text-white leading-relaxed description-content-right" x-cloak>
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
            height: auto;
            border-radius: 0.75rem;
            margin: 1.5rem auto;
            display: block;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
        }
        .description-content h1, .description-content h2, .description-content h3,
        .description-content-right h1, .description-content-right h2, .description-content-right h3 {
            color: #ffffff;
            font-weight: 700;
            margin-top: 1.25rem;
            margin-bottom: 0.75rem;
        }
        .description-content h1, .description-content-right h1 { font-size: 1.75rem; }
        .description-content h2, .description-content-right h2 { font-size: 1.5rem; }
        .description-content h3, .description-content-right h3 { font-size: 1.25rem; }
        .description-content p, .description-content-right p { margin: 0.75rem 0; line-height: 1.75; }
        
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
            color: #60a5fa;
            text-decoration: underline;
        }
        .description-content blockquote, .description-content-right blockquote {
            border-left: 4px solid #3b82f6;
            padding-left: 1rem;
            margin: 1.25rem 0;
            font-style: italic;
            color: #93c5fd;
        }
        .description-content strong, .description-content-right strong { font-weight: 700; color: #ffffff; }
        .description-content em, .description-content-right em { font-style: italic; }
        .description-content iframe, .description-content video,
        .description-content-right iframe, .description-content-right video {
            max-width: 100%;
            border-radius: 0.75rem;
            margin: 1.5rem 0;
        }
    </style>

    {{-- Description Section removed from here - now displayed inside the contact form box above --}}

    <!-- Section Divider -->
    <div class="section-divider"></div>

    <!-- Landing Page Sections (Image with Description) - BEFORE Features -->
    @if($product->landing_page_sections && count($product->landing_page_sections) > 0 && ($product->landing_page_fr['show_product_sections'] ?? true))
    <section class="py-16 lg:py-20 bg-white">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-6xl mx-auto space-y-12">
                @foreach($product->landing_page_sections as $index => $section)
                <div class="grid md:grid-cols-2 gap-8 items-center {{ $index % 2 == 1 ? 'md:flex-row-reverse' : '' }}">
                    <!-- Image -->
                    <div class="relative {{ $index % 2 == 1 ? 'md:order-2' : '' }}">
                        @if(!empty($section['image']))
                        <div class="rounded-2xl overflow-hidden shadow-xl">
                            <img src="/storage/{{ $section['image'] }}" 
                                 alt="{{ $section['title_fr'] ?? 'Product feature' }}" 
                                 class="w-full h-[350px] object-cover">
                        </div>
                        @endif
                    </div>
                    
                    <!-- Content -->
                    <div class="{{ $index % 2 == 1 ? 'md:order-1' : '' }}" x-cloak>
                        <h3 class="text-2xl lg:text-3xl font-black mb-4 text-gray-900">
                            <span x-show="currentLang === 'fr'">{{ $section['title_fr'] ?? '' }}</span>
                            <span x-show="currentLang === 'en'">{{ $section['title_en'] ?? $section['title_fr'] ?? '' }}</span>
                            <span x-show="currentLang === 'ar'">{{ $section['title_ar'] ?? $section['title_fr'] ?? '' }}</span>
                        </h3>
                        <p class="text-gray-600 text-lg leading-relaxed">
                            <span x-show="currentLang === 'fr'">{{ $section['description_fr'] ?? '' }}</span>
                            <span x-show="currentLang === 'en'">{{ $section['description_en'] ?? $section['description_fr'] ?? '' }}</span>
                            <span x-show="currentLang === 'ar'">{{ $section['description_ar'] ?? $section['description_fr'] ?? '' }}</span>
                        </p>
                    </div>
                </div>
                @if($index < count($product->landing_page_sections) - 1)
                <div class="border-t border-gray-200"></div>
                @endif
                @endforeach
            </div>
        </div>
    </section>
    
    <!-- Section Divider -->
    <div class="section-divider"></div>
    @endif

    <!-- Features Section -->
    <section class="py-16 lg:py-20 bg-gray-50" x-cloak>
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl lg:text-4xl font-black text-gray-900 mb-4">
                    <span x-show="currentLang === 'fr'">المميزات / Les Caractéristiques</span>
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

    <!-- Steps Section (How It Works) -->
    <section class="py-16 lg:py-20 bg-white" x-cloak>
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl lg:text-4xl font-black text-gray-900 mb-4" x-text="pageData[currentLang]?.steps_title || 'Comment ça marche'"></h2>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                <template x-for="(step, index) in (pageData[currentLang]?.steps || [])" :key="index">
                    <div class="relative">
                        <div class="text-center">
                            <div class="w-20 h-20 bg-gradient-to-br from-blue-600 to-blue-800 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                                <span class="text-3xl font-black text-white" x-text="step.number"></span>
                            </div>
                            <h3 class="text-xl font-bold mb-3 text-gray-900" x-text="step.title"></h3>
                            <p class="text-gray-600 leading-relaxed" x-text="step.description"></p>
                        </div>
                        <!-- Arrow for desktop -->
                        <div class="hidden md:block absolute top-10 right-0 transform translate-x-1/2" x-show="index < 2">
                            <svg class="w-8 h-8 text-blue-300" :class="{'rotate-180': currentLang === 'ar'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </section>


    <!-- Testimonials Section -->
    <section class="py-16 lg:py-20 bg-white" x-cloak>
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
    <section class="py-16 lg:py-20 bg-gray-50" x-cloak x-data="{ openFaq: null }">
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
                opt.classList.remove('border-yellow-300');
                opt.classList.add('border-white/20');
            });
            
            // Add active state to selected option
            option.classList.remove('border-white/20');
            option.classList.add('border-yellow-300');
            
            // Update the price display
            const priceDisplay = document.getElementById('promotionPriceDisplay');
            if (priceDisplay) {
                priceDisplay.innerHTML = formatPriceJs(price) + ' <span class="text-xl">درهم</span>';
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
                opt.classList.remove('border-white/50');
                opt.classList.add('border-white/20');
            });
            
            // Add active state to selected option
            option.classList.remove('border-white/20');
            option.classList.add('border-white/50');
            
            // Update price display in form
            const priceContainer = document.getElementById('variationPriceForm');
            if (priceContainer) {
                // Format price - show decimals only when needed
                let formattedPrice = price % 1 === 0 ? price.toFixed(0) : parseFloat(price.toFixed(2)).toString();
                let priceHtml = formattedPrice + ' <span class="text-xl">درهم</span>';
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
                        label.textContent = 'جاري الإرسال...';
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

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-8 pb-24">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-lg">&copy; {{ date('Y') }} {{ config('app.name') }}. 
                <span x-show="currentLang === 'fr'">Tous droits réservés.</span>
                <span x-show="currentLang === 'en'">All rights reserved.</span>
                <span x-show="currentLang === 'ar'">جميع الحقوق محفوظة.</span>
            </p>
        </div>
    </footer>
</body>
</html>
