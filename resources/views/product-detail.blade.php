<!DOCTYPE html>
@php
    $workspace = $workspace ?? $store->workspace ?? null;
    $currencySymbol = $workspace?->getCurrencySymbol() ?? 'DHS';
    $currencyCode = $workspace?->getCurrencyCode() ?? 'MAD';
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $product->name }} - {{ config('app.name') }}</title>
    <meta name="description" content="{{ Str::limit(strip_tags($product->landing_page_hero_description ?? $product->description), 160) }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    @include('partials.facebook-pixels', [
        'store' => $store,
        'facebookPixelEvents' => [[
            'name' => 'ViewContent',
            'params' => [
                'content_name' => $product->name,
                'content_ids' => [(string) $product->id],
                'content_type' => 'product',
                'value' => (float) $product->price,
                'currency' => $currencyCode,
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
            currency: '{{ $currencyCode }}'
          });
        }(window, document, 'ttq');
    </script>
    <!-- End TikTok Pixel Code -->
    @endif
</head>
<body class="antialiased bg-gradient-to-br from-slate-50 to-blue-50 text-gray-900">
    @php
        // Helper function to format price - show decimals only when needed
        if (!function_exists('formatPriceDetail')) {
            function formatPriceDetail($price) {
                if ($price == floor($price)) {
                    return number_format($price, 0);
                } else {
                    return rtrim(rtrim(number_format($price, 2), '0'), '.');
                }
            }
        }
    @endphp
    <!-- Navigation -->
    <nav class="sticky top-0 z-50 bg-white/95 backdrop-blur-sm shadow-md">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center">
                    <a href="{{ \App\Support\StoreDomain::homeUrl($store) }}" class="text-3xl font-bold bg-gradient-to-r from-emerald-500 to-blue-600 bg-clip-text text-transparent">
                        {{ config('app.name') }}
                    </a>
                </div>
                <div class="hidden md:flex items-center gap-8">
                    <a href="{{ \App\Support\StoreDomain::homeUrl($store) }}" class="text-gray-700 hover:text-emerald-600 font-medium transition">
                        <span class="material-icons align-middle mr-1">arrow_back</span>
                        Back to Store
                    </a>
                </div>
            </div>
        </div>
    </nav>

    @if($product->landing_page_hero_description || $product->landing_page_ar)
    <!-- AI-Generated Landing Page -->
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Hero Section -->
        <section class="mb-16">
            <div class="bg-gradient-to-r from-purple-600 to-blue-600 rounded-3xl p-12 text-white shadow-2xl">
                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div>
                        <h1 class="text-5xl font-bold mb-6 leading-tight">{{ $product->name }}</h1>
                        
                        <div class="flex items-center gap-6 mb-8">
                            <div>
                                @if($product->has_variations && $product->activeVariations->isNotEmpty())
                                    <div class="text-5xl font-bold" id="variationPriceHero">
                                        {{ $product->price_range }}
                                    </div>
                                @else
                                    <div class="text-5xl font-bold">{{ formatPriceDetail($product->price) }} {{ $currencySymbol }}</div>
                                    @if($product->compare_at_price && $product->compare_at_price > $product->price)
                                    <div class="text-lg line-through text-white/70">{{ formatPriceDetail($product->compare_at_price) }} {{ $currencySymbol }}</div>
                                    @endif
                                @endif
                            </div>
                            @if(!$product->has_variations && $product->discount_percentage)
                            <div class="bg-yellow-400 text-purple-900 px-4 py-2 rounded-full font-bold text-xl">
                                -{{ $product->discount_percentage }}%
                            </div>
                            @endif
                        </div>

                        <!-- Quantity-Based Promotions -->
                        @if($product->has_promotions && $product->activePromotions->isNotEmpty())
                        <div class="mb-8 bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
                            <h3 class="text-2xl font-bold text-white mb-4 flex items-center gap-2">
                                <svg class="w-6 h-6 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Special Quantity Pricing
                            </h3>
                            <p class="text-sm text-white/80 mb-4">Save more when you buy more items</p>
                            <div class="space-y-3" id="promotionsContainerHero">
                                @foreach($product->activePromotions as $index => $promotion)
                                <label class="block p-5 bg-white/20 backdrop-blur-sm rounded-xl border-2 cursor-pointer hover:border-yellow-300 transition promotion-option-hero {{ $index === 0 ? 'border-yellow-300' : 'border-white/30' }}"
                                       data-promotion-id="{{ $promotion->id }}"
                                       data-min-quantity="{{ $promotion->min_quantity }}"
                                       data-max-quantity="{{ $promotion->max_quantity ?? '' }}"
                                       data-price="{{ $promotion->price }}"
                                       data-discount="{{ $promotion->discount_percentage }}">
                                    <div class="flex items-start gap-4">
                                        <input type="radio" 
                                               name="selected_promotion_hero" 
                                               value="{{ $promotion->id }}" 
                                               class="mt-1 w-5 h-5 text-yellow-300"
                                               {{ $index === 0 ? 'checked' : '' }}
                                               onchange="updatePromotionDisplayHero(this)">
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between flex-wrap gap-3">
                                                <div class="text-lg font-semibold text-yellow-300">
                                                    {{ $promotion->label ?? 'Buy' }} {{ $promotion->quantity_range }}
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-3xl font-bold text-white">{{ formatPriceDetail($promotion->price) }} <span class="text-base text-white/80">{{ $currencySymbol }}</span></div>
                                                    @if($promotion->discount_percentage > 0)
                                                    <div class="inline-block text-xs bg-yellow-400 text-purple-900 px-3 py-1 rounded-full font-bold mt-1">
                                                        -{{ $promotion->discount_percentage }}%
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if($product->has_variations && $product->activeVariations->isNotEmpty())
                        <!-- Variations Selector -->
                        <div class="mb-8 bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
                            <h3 class="text-lg font-semibold mb-4 text-white">Select Options:</h3>
                            
                            <div class="space-y-4" id="variationsContainerHero">
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
                                <label class="block p-4 bg-white/20 backdrop-blur-sm rounded-xl border-2 cursor-pointer hover:border-white/60 transition variation-option-hero {{ $variation->is_default ? 'border-white/60' : 'border-white/30' }}"
                                       data-variation-id="{{ $variation->id }}"
                                       data-price="{{ $variation->price }}"
                                       data-compare-price="{{ $variation->compare_at_price ?? 0 }}"
                                       data-stock="{{ $variation->stock }}"
                                       data-discount="{{ $variation->discount_percentage }}">
                                    <div class="flex items-start gap-4">
                                        <input type="radio" 
                                               name="selected_variation_hero" 
                                               value="{{ $variation->id }}" 
                                               class="mt-1 w-5 h-5 text-white"
                                               {{ $variation->is_default ? 'checked' : '' }}
                                               onchange="updateVariationDisplayHero(this)">
                                        <div class="flex-1">
                                            <div class="flex items-start justify-between flex-wrap gap-3">
                                                <div>
                                                    <div class="font-bold text-white text-lg mb-1">{{ $displayName }}</div>
                                                    @if($variation->sku)
                                                    <div class="text-xs text-white/70 font-mono">{{ Str::limit($variation->sku, 30) }}</div>
                                                    @endif
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-xl font-bold text-white">{{ formatPriceDetail($variation->price) }} {{ $currencySymbol }}</div>
                                                    @if($variation->compare_at_price && $variation->compare_at_price > $variation->price)
                                                    <div class="text-sm line-through text-white/70">{{ formatPriceDetail($variation->compare_at_price) }} {{ $currencySymbol }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <div class="flex items-center justify-between mt-3 pt-3 border-t border-white/20">
                                                <div class="text-sm text-white/80 flex items-center gap-2">
                                                    <span class="material-icons text-sm">inventory_2</span>
                                                    <span>Stock: {{ $variation->stock }}</span>
                                                </div>
                                                @if($variation->discount_percentage)
                                                <div class="inline-block text-xs bg-yellow-400 text-purple-900 px-3 py-1 rounded-full font-bold">
                                                    -{{ $variation->discount_percentage }}%
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <a href="#order" class="inline-block px-8 py-4 bg-white text-purple-600 font-bold text-lg rounded-full hover:bg-yellow-400 hover:text-purple-900 transition shadow-lg hover:shadow-xl hover:scale-105 transform">
                            <span class="material-icons align-middle mr-2">shopping_cart</span>
                            {{ $product->landing_page_cta ?? 'Order Now' }}
                        </a>
                    </div>
                    
                    <div class="relative">
                        @if($product->first_image)
                        <img src="{{ $product->first_image }}" alt="{{ $product->name }}" class="w-full h-96 object-cover rounded-2xl shadow-2xl transform hover:scale-105 transition">
                        @endif
                    </div>
                </div>
            </div>
        </section>

        @if($product->landing_page_features && count($product->landing_page_features) > 0)
        <!-- Features Section -->
        <section class="mb-16">
            <h2 class="text-4xl font-bold text-center mb-12 bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
                Why Choose {{ $product->name }}?
            </h2>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                @foreach($product->landing_page_features as $feature)
                <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition transform hover:-translate-y-2">
                    <div class="text-5xl mb-4">{{ $feature['icon'] ?? '✓' }}</div>
                    <h3 class="text-xl font-bold mb-3 text-gray-900">{{ $feature['title'] }}</h3>
                    <p class="text-gray-600">{{ $feature['description'] }}</p>
                </div>
                @endforeach
            </div>
        </section>
        @endif

        <!-- Full Description Section -->
        <section class="mb-16">
            <div class="bg-white rounded-3xl p-12 shadow-lg">
                <h2 class="text-4xl font-bold mb-8 bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
                    About {{ $product->name }}
                </h2>
                <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
                    {!! $product->landing_page_content ?? $product->description !!}
                </div>
            </div>
        </section>

        <!-- Product Gallery -->
        @if($product->images && count($product->images) > 1)
        <section class="mb-16">
            <h2 class="text-4xl font-bold text-center mb-12 bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
                Product Gallery
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                @foreach($product->images as $image)
                <div class="relative group overflow-hidden rounded-2xl shadow-lg">
                    <img src="/storage/{{ $image }}" alt="{{ $product->name }}" class="w-full h-64 object-cover transform group-hover:scale-110 transition">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition"></div>
                </div>
                @endforeach
            </div>
        </section>
        @endif

    @else
    <!-- Standard Product Page (No AI Landing Page) -->
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
            <div class="grid md:grid-cols-2 gap-12 p-12">
                <div>
                    @if($product->first_image)
                    <img src="{{ $product->first_image }}" alt="{{ $product->name }}" class="w-full h-96 object-cover rounded-2xl shadow-lg">
                    @endif
                    
                    @if($product->images && count($product->images) > 1)
                    <div class="grid grid-cols-4 gap-4 mt-6">
                        @foreach(array_slice($product->images, 1, 4) as $image)
                        <img src="/storage/{{ $image }}" alt="{{ $product->name }}" class="w-full h-24 object-cover rounded-lg">
                        @endforeach
                    </div>
                    @endif
                </div>
                
                <div>
                    @if($product->category)
                    <div class="text-purple-600 font-semibold mb-3">{{ $product->category->name }}</div>
                    @endif
                    
                    <h1 class="text-4xl font-bold mb-6">{{ $product->name }}</h1>
                    
                    <div class="flex items-center gap-6 mb-8">
                        <div>
                            @if($product->has_variations && $product->activeVariations->isNotEmpty())
                                <div class="text-4xl font-bold text-purple-600" id="variationPrice">
                                    {{ $product->price_range }}
                                </div>
                            @else
                                <div class="text-4xl font-bold text-purple-600">{{ formatPriceDetail($product->price) }} {{ $currencySymbol }}</div>
                                @if($product->compare_at_price && $product->compare_at_price > $product->price)
                                <div class="text-lg line-through text-gray-500">{{ formatPriceDetail($product->compare_at_price) }} {{ $currencySymbol }}</div>
                                @endif
                            @endif
                        </div>
                        @if(!$product->has_variations && $product->discount_percentage)
                        <div class="bg-red-500 text-white px-4 py-2 rounded-full font-bold">
                            -{{ $product->discount_percentage }}%
                        </div>
                        @endif
                    </div>

                    <!-- Quantity-Based Promotions -->
                    @if($product->has_promotions && $product->activePromotions->isNotEmpty())
                    <div class="mb-8 bg-gradient-to-br from-yellow-50 to-orange-50 rounded-2xl p-6 border border-yellow-200 shadow-lg">
                        <h3 class="text-2xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Special Quantity Pricing
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">Save more when you buy more items</p>
                        <div class="space-y-3" id="promotionsContainer">
                            @foreach($product->activePromotions as $index => $promotion)
                            <label class="block p-5 bg-white rounded-xl border-2 cursor-pointer hover:border-yellow-500 transition shadow-md promotion-option {{ $index === 0 ? 'border-yellow-500' : 'border-yellow-300' }}"
                                   data-promotion-id="{{ $promotion->id }}"
                                   data-min-quantity="{{ $promotion->min_quantity }}"
                                   data-max-quantity="{{ $promotion->max_quantity ?? '' }}"
                                   data-price="{{ $promotion->price }}"
                                   data-discount="{{ $promotion->discount_percentage }}">
                                <div class="flex items-start gap-4">
                                    <input type="radio" 
                                           name="selected_promotion" 
                                           value="{{ $promotion->id }}" 
                                           class="mt-1 w-5 h-5 text-yellow-500 focus:ring-yellow-500"
                                           {{ $index === 0 ? 'checked' : '' }}
                                           onchange="updatePromotionDisplay(this)">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between flex-wrap gap-3">
                                            <div class="text-lg font-semibold text-yellow-700">
                                                {{ $promotion->label ?? 'Buy' }} {{ $promotion->quantity_range }}
                                            </div>
                                            <div class="text-right">
                                                <div class="text-3xl font-bold text-gray-800">{{ formatPriceDetail($promotion->price) }} <span class="text-base text-gray-600">{{ $currencySymbol }}</span></div>
                                                @if($promotion->discount_percentage > 0)
                                                <div class="inline-block text-xs bg-yellow-500 text-white px-3 py-1 rounded-full font-bold mt-1">
                                                    -{{ $promotion->discount_percentage }}%
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($product->has_variations && $product->activeVariations->isNotEmpty())
                    <!-- Variations Selector -->
                    <div class="mb-8 p-6 bg-gradient-to-br from-purple-50 to-blue-50 rounded-2xl border border-purple-200">
                        <h3 class="text-lg font-semibold mb-4 text-gray-800">Select Options:</h3>
                        
                        <div class="space-y-4" id="variationsContainer">
                            @foreach($product->activeVariations as $index => $variation)
                            @php
                                // Build display name from attributes
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
                            <label class="block p-4 bg-white rounded-xl border-2 cursor-pointer hover:border-purple-500 transition variation-option {{ $variation->is_default ? 'border-purple-500 bg-purple-50' : 'border-gray-200' }}"
                                   data-variation-id="{{ $variation->id }}"
                                   data-price="{{ $variation->price }}"
                                   data-compare-price="{{ $variation->compare_at_price ?? 0 }}"
                                   data-stock="{{ $variation->stock }}"
                                   data-discount="{{ $variation->discount_percentage }}">
                                <div class="flex items-start gap-4">
                                    <input type="radio" 
                                           name="selected_variation" 
                                           value="{{ $variation->id }}" 
                                           class="mt-1 w-5 h-5 text-purple-600"
                                           {{ $variation->is_default ? 'checked' : '' }}
                                           onchange="updateVariationDisplay(this)">
                                    <div class="flex-1">
                                        <div class="flex items-start justify-between flex-wrap gap-3">
                                            <div>
                                                <div class="font-bold text-gray-900 text-lg mb-1">{{ $displayName }}</div>
                                                @if($variation->sku)
                                                <div class="text-xs text-gray-500 font-mono">{{ Str::limit($variation->sku, 30) }}</div>
                                                @endif
                                            </div>
                                            <div class="text-right">
                                                <div class="text-xl font-bold text-purple-600">{{ formatPriceDetail($variation->price) }} {{ $currencySymbol }}</div>
                                                @if($variation->compare_at_price && $variation->compare_at_price > $variation->price)
                                                <div class="text-sm line-through text-gray-500">{{ formatPriceDetail($variation->compare_at_price) }} {{ $currencySymbol }}</div>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-200">
                                            <div class="text-sm text-gray-600 flex items-center gap-2">
                                                <span class="material-icons text-sm">inventory_2</span>
                                                <span>Stock: {{ $variation->stock }}</span>
                                            </div>
                                            @if($variation->discount_percentage)
                                            <div class="inline-block text-xs bg-red-500 text-white px-3 py-1 rounded-full font-bold">
                                                -{{ $variation->discount_percentage }}%
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    <div class="prose prose-lg max-w-none mb-8 text-gray-700">
                        {!! $product->description !!}
                    </div>
                    
                    @if(!$product->has_variations && $product->stock !== null)
                    <div class="mb-6 flex items-center gap-2 text-gray-600">
                        <span class="material-icons">inventory_2</span>
                        <span>Stock: {{ $product->stock }}</span>
                    </div>
                    @endif
                    
                    <a href="#order" class="inline-block px-8 py-4 bg-gradient-to-r from-purple-600 to-blue-600 text-white font-bold text-lg rounded-full hover:shadow-xl transition transform hover:scale-105">
                        <span class="material-icons align-middle mr-2">shopping_cart</span>
                        Order Now
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Order Section -->
    <section id="order" class="bg-gradient-to-r from-purple-900 to-blue-900 py-16">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl mx-auto text-center">
                <h2 class="text-4xl font-bold text-white mb-6">Ready to Order?</h2>
                <p class="text-xl text-white/90 mb-8">Contact us now to place your order</p>
                
                @if(isset($settings) && ($settings->whatsapp_number || $settings->contact_phone))
                <div class="grid md:grid-cols-2 gap-6">
                    @if($settings->whatsapp_number)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $settings->whatsapp_number) }}?text=Hello, I'm interested in {{ urlencode($product->name) }}" 
                       target="_blank" 
                       class="flex items-center justify-center gap-3 px-8 py-4 bg-green-500 hover:bg-green-600 text-white font-bold text-lg rounded-full transition shadow-lg hover:shadow-xl">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                        </svg>
                        <span>Order via WhatsApp</span>
                    </a>
                    @endif
                    
                    @if($settings->contact_phone)
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $settings->contact_phone) }}" 
                       class="flex items-center justify-center gap-3 px-8 py-4 bg-blue-500 hover:bg-blue-600 text-white font-bold text-lg rounded-full transition shadow-lg hover:shadow-xl">
                        <span class="material-icons text-3xl">phone</span>
                        <span>Call Us</span>
                    </a>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </section>

    @if($relatedProducts && $relatedProducts->count() > 0)
    <!-- Related Products -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-4xl font-bold text-center mb-12 bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
                Related Products
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                @foreach($relatedProducts as $related)
                <a href="{{ \App\Support\StoreDomain::productUrl($store, $related->slug) }}" class="group">
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition transform hover:-translate-y-2">
                        <div class="relative overflow-hidden">
                            <img src="{{ $related->first_image }}" alt="{{ $related->name }}" class="w-full h-64 object-cover group-hover:scale-110 transition">
                            @if($related->discount_percentage)
                            <div class="absolute top-4 right-4 bg-red-500 text-white px-3 py-1 rounded-full font-bold text-sm">
                                -{{ $related->discount_percentage }}%
                            </div>
                            @endif
                        </div>
                        <div class="p-6">
                            <h3 class="font-bold text-xl mb-2 text-gray-900 group-hover:text-purple-600 transition">{{ $related->name }}</h3>
                            <div class="flex items-center gap-3">
                                <span class="text-2xl font-bold text-purple-600">{{ formatPriceDetail($related->price) }} {{ $currencySymbol }}</span>
                                @if($related->compare_at_price)
                                <span class="text-sm line-through text-gray-500">{{ formatPriceDetail($related->compare_at_price) }} {{ $currencySymbol }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-lg">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </footer>

    @if($product->has_variations)
    <script>
        function updateVariationDisplay(radio) {
            const option = radio.closest('.variation-option');
            const price = parseFloat(option.dataset.price);
            const comparePrice = parseFloat(option.dataset.comparePrice) || 0;
            const stock = option.dataset.stock;
            const discount = option.dataset.discount;
            
            // Remove active state from all options
            document.querySelectorAll('.variation-option').forEach(opt => {
                opt.classList.remove('border-purple-500', 'bg-purple-50');
                opt.classList.add('border-gray-200');
            });
            
            // Add active state to selected option
            option.classList.remove('border-gray-200');
            option.classList.add('border-purple-500', 'bg-purple-50');
            
            // Helper function to format price - show decimals only when needed
            function formatPriceJs(p) {
                return p % 1 === 0 ? p.toFixed(0) : parseFloat(p.toFixed(2)).toString();
            }
            
            // Update price display
            const priceContainer = document.getElementById('variationPrice');
            if (priceContainer) {
                let priceHtml = `<div class="text-4xl font-bold text-purple-600">${formatPriceJs(price)} {{ $currencySymbol }}</div>`;
                
                if (comparePrice > price) {
                    priceHtml += `<div class="text-lg line-through text-gray-500">${formatPriceJs(comparePrice)} {{ $currencySymbol }}</div>`;
                }
                
                priceContainer.innerHTML = priceHtml;
            }
        }

        function updateVariationDisplayHero(radio) {
            const option = radio.closest('.variation-option-hero');
            const price = parseFloat(option.dataset.price);
            const comparePrice = parseFloat(option.dataset.comparePrice) || 0;
            const stock = option.dataset.stock;
            const discount = option.dataset.discount;
            
            // Remove active state from all options
            document.querySelectorAll('.variation-option-hero').forEach(opt => {
                opt.classList.remove('border-white/60');
                opt.classList.add('border-white/30');
            });
            
            // Add active state to selected option
            option.classList.remove('border-white/30');
            option.classList.add('border-white/60');
            
            // Helper function to format price - show decimals only when needed
            function formatPriceHeroJs(p) {
                return p % 1 === 0 ? p.toFixed(0) : parseFloat(p.toFixed(2)).toString();
            }
            
            // Update price display in hero section
            const priceContainer = document.getElementById('variationPriceHero');
            if (priceContainer) {
                let priceHtml = `<div class="text-5xl font-bold">${formatPriceHeroJs(price)} {{ $currencySymbol }}</div>`;
                
                if (comparePrice > price) {
                    priceHtml += `<div class="text-lg line-through text-white/70">${formatPriceHeroJs(comparePrice)} {{ $currencySymbol }}</div>`;
                }
                
                priceContainer.innerHTML = priceHtml;
            }
        }
        
        // Set default variation on page load
        document.addEventListener('DOMContentLoaded', function() {
            const defaultRadio = document.querySelector('input[name="selected_variation"]:checked');
            if (defaultRadio) {
                updateVariationDisplay(defaultRadio);
            }
            
            const defaultRadioHero = document.querySelector('input[name="selected_variation_hero"]:checked');
            if (defaultRadioHero) {
                updateVariationDisplayHero(defaultRadioHero);
            }
        });
    </script>
    @endif

    @if($product->has_promotions && $product->activePromotions->isNotEmpty())
    <script>
        // Promotion selection handler
        function updatePromotionDisplay(radio) {
            const option = radio.closest('.promotion-option');
            const minQuantity = parseInt(option.dataset.minQuantity);
            const maxQuantity = option.dataset.maxQuantity ? parseInt(option.dataset.maxQuantity) : null;
            const price = parseFloat(option.dataset.price);
            const discount = option.dataset.discount;
            
            // Remove active state from all options
            document.querySelectorAll('.promotion-option').forEach(opt => {
                opt.classList.remove('border-yellow-500');
                opt.classList.add('border-yellow-300');
            });
            
            // Add active state to selected option
            option.classList.remove('border-yellow-300');
            option.classList.add('border-yellow-500');
            
            // Update quantity input to match the promotion minimum
            const quantityInput = document.querySelector('input[name="quantity"]');
            if (quantityInput) {
                quantityInput.value = minQuantity;
                quantityInput.min = minQuantity;
                if (maxQuantity) {
                    quantityInput.max = maxQuantity;
                } else {
                    quantityInput.removeAttribute('max');
                }
            }
        }

        // Promotion selection handler for hero section
        function updatePromotionDisplayHero(radio) {
            const option = radio.closest('.promotion-option-hero');
            const minQuantity = parseInt(option.dataset.minQuantity);
            const maxQuantity = option.dataset.maxQuantity ? parseInt(option.dataset.maxQuantity) : null;
            const price = parseFloat(option.dataset.price);
            const discount = option.dataset.discount;
            
            // Remove active state from all options
            document.querySelectorAll('.promotion-option-hero').forEach(opt => {
                opt.classList.remove('border-yellow-300');
                opt.classList.add('border-white/30');
            });
            
            // Add active state to selected option
            option.classList.remove('border-white/30');
            option.classList.add('border-yellow-300');
        }
        
        // Set default promotion on page load
        document.addEventListener('DOMContentLoaded', function() {
            const defaultRadio = document.querySelector('input[name="selected_promotion"]:checked');
            if (defaultRadio) {
                updatePromotionDisplay(defaultRadio);
            }
            
            const defaultRadioHero = document.querySelector('input[name="selected_promotion_hero"]:checked');
            if (defaultRadioHero) {
                updatePromotionDisplayHero(defaultRadioHero);
            }
        });
    </script>
    @endif
</body>
</html>
