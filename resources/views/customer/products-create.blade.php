@extends('layouts.customer')

@section('content')
    @php
        $currencyCode = isset($activeWorkspace) ? $activeWorkspace->getCurrencyCode() : 'MAD';
        $currencySymbol = isset($activeWorkspace) ? $activeWorkspace->getCurrencySymbol() : 'DHS';
    @endphp
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-white flex items-center gap-3">
                    <svg class="w-8 h-8 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create New Product
                </h2>
                <p class="text-sm text-gray-400 mt-1">Add a new product to your catalog</p>
            </div>
            <a href="{{ route('app.products') }}" class="px-6 py-3 bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-lg transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Products
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 bg-emerald-500/20 border border-emerald-500/50 text-emerald-400 px-4 py-3 rounded-lg flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    
    @if(session('warning'))
    <div class="mb-6 bg-yellow-500/20 border border-yellow-500/50 text-yellow-400 px-4 py-3 rounded-lg flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <span>{{ session('warning') }}</span>
    </div>
    @endif

    @php
        $aiSetting = \App\Models\AiApiSetting::where('user_id', auth()->id())->first();
        $hasAiConfigured = $aiSetting && (!empty($aiSetting->openai_api_key_encrypted) || !empty($aiSetting->anthropic_api_key_encrypted));
    @endphp

    @if(!$hasAiConfigured)
    <div class="mb-6 bg-purple-500/20 border border-purple-500/50 text-purple-300 px-4 py-3 rounded-lg flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <span class="font-semibold">AI Landing Page Feature Requires Configuration</span>
            <span class="block text-sm mt-1">Please <a href="{{ route('workspaces.ai-settings') }}" class="underline hover:text-purple-200">configure your AI API settings</a> to use the AI landing page generation feature.</span>
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="mb-6 bg-red-500/20 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg">
        <p class="font-semibold flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Please fix the following errors:
        </p>
        <ul class="list-disc list-inside mt-2">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="max-w-4xl">
        <form action="{{ route('app.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <input type="hidden" name="theme" value="{{ $theme }}">
            
            <!-- Product Information Card -->
            <div class="bg-[#0f1c2e] border border-white/10 rounded-xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-white">Product Information</h3>
                    <button 
                        type="button" 
                        id="aiGenerateBtn"
                        class="px-4 py-2 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold rounded-lg transition flex items-center gap-2 text-sm"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span id="aiGenerateBtnText">AI Generate Landing Page</span>
                    </button>
                </div>
                
                <div id="aiNotice" class="mb-4 bg-blue-500/20 border border-blue-500/50 text-blue-400 px-4 py-3 rounded-lg flex items-start gap-2 hidden">
                    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="font-semibold">AI Landing Page Generation</p>
                        <p class="text-sm mt-1">Fill in the product details below, then click the button above to generate a professional landing page using AI.</p>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <!-- Product Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Product Name *</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            required
                            value="{{ old('name') }}"
                            class="w-full px-4 py-3 bg-[#0a1628] border border-white/10 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                            placeholder="Enter product name"
                            oninput="updateSlugFromName(this.value)"
                        />
                        @error('name')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Product Slug & Landing Page URL Preview -->
                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-300 mb-2">
                            URL Slug
                            <span class="text-xs text-gray-500 ml-2">(auto-generated from name)</span>
                        </label>
                        <input 
                            type="text" 
                            id="slug" 
                            name="slug" 
                            value="{{ old('slug') }}"
                            readonly
                            tabindex="-1"
                            class="w-full px-4 py-3 bg-[#0a1628]/80 border border-white/10 rounded-lg text-gray-400 placeholder-gray-500 cursor-not-allowed focus:outline-none"
                            placeholder="product-url-slug"
                        />
                        @error('slug')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                        
                        <!-- Landing Page URL Preview -->
                        @if($store)
                        <div class="mt-3 p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-lg">
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                                <span class="text-sm font-medium text-emerald-400">Landing Page URL Preview</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <code id="landingPageUrl" class="flex-1 text-sm text-emerald-300 bg-[#0a1628] px-3 py-2 rounded border border-emerald-500/20 break-all">
                                    {{ url('/store/' . $store->subdomain . '/product/') }}/<span id="slugPreview" class="text-yellow-300">your-product-slug</span>
                                </code>
                                <button type="button" onclick="copyLandingPageUrl()" class="p-2 text-emerald-400 hover:text-emerald-300 transition" title="Copy URL">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                </button>
                            </div>
                            <p class="text-xs text-gray-400 mt-2">This is the direct link to your product's landing page. Share it in ads or social media.</p>
                        </div>
                        @else
                        <div class="mt-3 p-4 bg-yellow-500/10 border border-yellow-500/30 rounded-lg">
                            <p class="text-sm text-yellow-400">Select a store to see the landing page URL preview.</p>
                        </div>
                        @endif
                    </div>

                    <!-- Product Images (before description) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Product Images</label>
                        <p class="text-xs text-gray-500 mb-3">Upload multiple images. <strong class="text-gray-300">Image 1</strong> is the main landing-page image. <strong class="text-gray-300">Images 2+</strong> get AI titles/descriptions and go into the Description below.</p>
                        <div class="border-2 border-dashed border-white/10 rounded-lg p-8 text-center hover:border-emerald-500/50 transition">
                            <input 
                                type="file" 
                                id="images" 
                                name="images[]" 
                                multiple
                                accept="image/*"
                                class="hidden"
                                onchange="previewImages(event)"
                            />
                            <label for="images" class="cursor-pointer">
                                <svg class="w-12 h-12 text-gray-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <p class="text-gray-400 mb-1">Click to upload images</p>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB each</p>
                            </label>
                        </div>
                        <div id="imagePreview" class="space-y-4 mt-4"></div>
                        <div id="preuploadedImagesContainer"></div>
                        <input type="hidden" id="mainImageIndex" name="main_image_index" value="0">
                        @error('images')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                        <p class="text-xs text-gray-500 mb-2">Auto-filled from images 2+ only (main image stays at the top of the landing page). You can still edit it.</p>
                        <!-- Quill Rich Text Editor -->
                        <div id="descriptionEditorWrap" class="relative rounded-lg">
                            <div id="description-editor" class="bg-white rounded-lg" style="min-height: 200px;"></div>
                            <div id="descriptionFillingOverlay" class="hidden absolute inset-0 z-10 rounded-lg overflow-hidden flex flex-col items-center justify-center gap-3 bg-[#0a1628]/90 backdrop-blur-[2px] border border-emerald-500/40">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 text-emerald-400 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    <p id="descriptionFillingMessage" class="text-sm font-medium text-emerald-300">Filling description…</p>
                                </div>
                                <div class="w-full max-w-sm px-6 space-y-2" aria-hidden="true">
                                    <div class="h-2.5 rounded bg-white/10 overflow-hidden">
                                        <div id="descriptionFillingBar" class="h-full w-1/3 rounded bg-emerald-500/70 animate-pulse" style="animation: descriptionFillSlide 1.4s ease-in-out infinite;"></div>
                                    </div>
                                    <div class="h-2 rounded bg-white/5 w-4/5 mx-auto overflow-hidden">
                                        <div class="h-full w-2/5 rounded bg-white/20 animate-pulse"></div>
                                    </div>
                                    <div class="h-2 rounded bg-white/5 w-3/5 mx-auto overflow-hidden">
                                        <div class="h-full w-1/2 rounded bg-white/15 animate-pulse"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <textarea 
                            id="description" 
                            name="description" 
                            class="hidden"
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <style>
                        @keyframes descriptionFillSlide {
                            0% { transform: translateX(-120%); }
                            50% { transform: translateX(180%); }
                            100% { transform: translateX(-120%); }
                        }
                        #descriptionEditorWrap.is-filling #description-editor {
                            pointer-events: none;
                            opacity: 0.55;
                        }
                    </style>

                    <!-- Price and Compare Price -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-300 mb-2">Price ({{ $currencyCode }}) *</label>
                            <input 
                                type="number" 
                                id="price" 
                                name="price" 
                                step="0.01"
                                min="0"
                                required
                                value="{{ old('price') }}"
                                class="w-full px-4 py-3 bg-[#0a1628] border border-white/10 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                placeholder="0.00"
                            />
                            @error('price')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="compare_at_price" class="block text-sm font-medium text-gray-300 mb-2">Compare at Price ({{ $currencyCode }})</label>
                            <input 
                                type="number" 
                                id="compare_at_price" 
                                name="compare_at_price" 
                                step="0.01"
                                min="0"
                                value="{{ old('compare_at_price') }}"
                                class="w-full px-4 py-3 bg-[#0a1628] border border-white/10 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                placeholder="0.00"
                            />
                            @error('compare_at_price')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Category -->
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-300 mb-2">Category</label>
                        <select 
                            id="category_id" 
                            name="category_id"
                            class="w-full px-4 py-3 bg-[#0a1628] border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                        >
                            <option value="">Select a category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Stock and SKU -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="stock" class="block text-sm font-medium text-gray-300 mb-2">Stock</label>
                            <input 
                                type="number" 
                                id="stock" 
                                name="stock" 
                                min="0"
                                value="{{ old('stock', 0) }}"
                                class="w-full px-4 py-3 bg-[#0a1628] border border-white/10 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                placeholder="0"
                            />
                            @error('stock')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="sku" class="block text-sm font-medium text-gray-300 mb-2">SKU</label>
                            <input 
                                type="text" 
                                id="sku" 
                                name="sku" 
                                value="{{ old('sku') }}"
                                class="w-full px-4 py-3 bg-[#0a1628] border border-white/10 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                placeholder="Enter SKU"
                            />
                            @error('sku')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Google Sheet (optional) -->
                    <div class="border-t border-white/10 pt-4">
                        <label for="google_sheet_connection_id" class="block text-sm font-medium text-gray-300 mb-2">
                            Google Sheet <span class="text-gray-500 font-normal">(optional)</span>
                        </label>
                        <select
                            id="google_sheet_connection_id"
                            name="google_sheet_connection_id"
                            class="w-full px-4 py-3 bg-[#0a1628] border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                        >
                            <option value="">No Google Sheet</option>
                            @foreach(($googleSheets ?? collect()) as $sheet)
                                <option value="{{ $sheet->id }}" @selected((string) old('google_sheet_connection_id') === (string) $sheet->id)>
                                    {{ $sheet->name }}{{ $sheet->is_enabled ? '' : ' (disabled)' }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            If selected, every order for this product will be added to that sheet.
                            @if(($googleSheets ?? collect())->isEmpty())
                                Connect a sheet first in Manage Stores → Google Sheets.
                            @endif
                        </p>
                        @error('google_sheet_connection_id')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Product Variations Toggle -->
                    <div class="border-t border-white/10 pt-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <label for="has_variations" class="block text-sm font-medium text-gray-300">Product has variations</label>
                                <p class="text-xs text-gray-500 mt-1">Enable if this product comes in different sizes, colors, or other options</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <!-- Hidden input to ensure a value is always sent -->
                                <input type="hidden" name="has_variations" value="0">
                                <input 
                                    type="checkbox" 
                                    id="has_variations" 
                                    name="has_variations" 
                                    value="1"
                                    class="sr-only peer"
                                    {{ old('has_variations') ? 'checked' : '' }}
                                    onchange="toggleVariations(this.checked)"
                                />
                                <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Variations Card -->
            <div id="variationsCard" class="bg-[#0f1c2e] border border-white/10 rounded-xl p-6 hidden">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-white">Product Variations</h3>
                        <p class="text-xs text-gray-500 mt-1">Add different options for this product (e.g., sizes, colors)</p>
                    </div>
                    <button 
                        type="button" 
                        onclick="addVariation()"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition flex items-center gap-2 text-sm"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Variation
                    </button>
                </div>

                <div id="variationsContainer" class="space-y-4">
                    <!-- Variations will be added here dynamically -->
                </div>

                <div id="noVariationsMessage" class="text-center py-8 text-gray-500 text-sm">
                    <svg class="w-12 h-12 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                    </svg>
                    Click "Add Variation" to create your first product variation
                </div>
                </div>
            </div>

            <!-- Quantity-Based Promotions Card -->
            <div id="promotionsCard" class="bg-[#0f1c2e] border border-white/10 rounded-xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-white flex items-center gap-2">
                            <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Quantity-Based Pricing
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">Set special prices when customers buy multiple items</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="has_promotions" value="0">
                            <input 
                                type="checkbox" 
                                id="has_promotions" 
                                name="has_promotions" 
                                value="1"
                                class="sr-only peer"
                                onchange="togglePromotions(this.checked)"
                            />
                            <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-yellow-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-600"></div>
                        </label>
                    </div>
                </div>

                <div id="promotionsContent" class="hidden">
                    <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4 mb-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="text-sm text-blue-300">
                                <p class="font-semibold mb-1">How it works:</p>
                                <ul class="list-disc list-inside space-y-1 text-xs">
                                    <li>Set different prices based on quantity purchased</li>
                                    <li>Example: Buy 1 for 100 {{ $currencyCode }}, Buy 2 for 90 {{ $currencyCode }} each, Buy 3+ for 80 {{ $currencyCode }} each</li>
                                    <li>Promotions apply automatically at checkout</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-semibold text-gray-300">Pricing Tiers</h4>
                        <button 
                            type="button" 
                            onclick="addPromotion()"
                            class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-semibold rounded-lg transition flex items-center gap-2 text-sm"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Tier
                        </button>
                    </div>

                    <div id="promotionsContainer" class="space-y-3">
                        <!-- Promotions will be added here dynamically -->
                    </div>
                    
                    <!-- Hidden field to ensure promotions data is always captured -->
                    <input type="hidden" id="promotions_json" name="promotions_json" value="[]">

                    <div id="noPromotionsMessage" class="text-center py-8 border-2 border-dashed border-yellow-500/30 rounded-lg bg-yellow-500/5">
                        <svg class="w-12 h-12 text-yellow-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <p class="text-yellow-300 font-semibold mb-1">⚠️ No pricing tiers added yet!</p>
                        <p class="text-gray-400 text-sm">Click <strong class="text-yellow-400">"Add Tier"</strong> above to create quantity-based pricing</p>
                        <p class="text-xs mt-2 text-gray-500">Example: Buy 2+ items → Pay 90 {{ $currencyCode }} each instead of 100 {{ $currencyCode }}</p>
                    </div>
                </div>
            </div>

            <!-- Settings Card -->
            <div class="bg-[#0f1c2e] border border-white/10 rounded-xl p-6">
                <h3 class="text-xl font-bold text-white mb-6">Settings</h3>
                
                <div class="space-y-4">
                    <!-- AI Landing Page Generation Toggle -->
                    <div class="flex items-center justify-between pb-4 border-b border-white/10">
                        <div>
                            <label for="generate_landing_page" class="block text-sm font-medium text-gray-300">AI Landing Page</label>
                            <p class="text-xs text-gray-500 mt-1">Automatically generate a professional landing page using AI</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input 
                                type="checkbox" 
                                id="generate_landing_page" 
                                name="generate_landing_page" 
                                class="sr-only peer"
                            />
                            <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-purple-600 peer-checked:to-blue-600"></div>
                        </label>
                    </div>

                    @include('customer.partials.landing-background-color-picker', [
                        'hint' => 'Default background for the landing page. If AI Landing Page is on, AI may pick a better color for this product; you can change it later.',
                    ])

                    <!-- AI Product Images Generation Toggle -->
                    <div class="flex items-center justify-between pb-4 border-b border-white/10">
                        <div>
                            <label for="generate_product_images" class="block text-sm font-medium text-gray-300">AI Product Images</label>
                            <p class="text-xs text-gray-500 mt-1">Generate 5 realistic product images using AI (requires at least 1 uploaded image)</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input 
                                type="checkbox" 
                                id="generate_product_images" 
                                name="generate_product_images" 
                                class="sr-only peer"
                            />
                            <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-orange-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-orange-600 peer-checked:to-yellow-600"></div>
                        </label>
                    </div>
                    
                    <!-- Active Status -->
                    <div class="flex items-center justify-between">
                        <div>
                            <label for="is_active" class="block text-sm font-medium text-gray-300">Active</label>
                            <p class="text-xs text-gray-500 mt-1">Make this product visible on your website</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input 
                                type="checkbox" 
                                id="is_active" 
                                name="is_active" 
                                class="sr-only peer"
                                {{ old('is_active', true) ? 'checked' : '' }}
                            />
                            <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>

                    <!-- Featured Status -->
                    <div class="flex items-center justify-between">
                        <div>
                            <label for="is_featured" class="block text-sm font-medium text-gray-300">Featured</label>
                            <p class="text-xs text-gray-500 mt-1">Show this product in featured section</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input 
                                type="checkbox" 
                                id="is_featured" 
                                name="is_featured" 
                                class="sr-only peer"
                                {{ old('is_featured') ? 'checked' : '' }}
                            />
                            <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>
                </div>
            </div>

            @include('customer.partials.landing-form-fields-builder', ['workspaceLang' => $workspaceLang ?? ($store->workspace?->getLanguage() ?? 'ar')])

            <!-- Submit Buttons -->
            <div class="flex justify-end gap-4">
                <a href="{{ route('app.products') }}" class="px-6 py-3 bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" id="createProductBtn" class="px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-semibold rounded-lg transition flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-emerald-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span id="createProductBtnLabel">Create Product</span>
                </button>
            </div>
        </form>
    </div>

    <script>
        let sectionCounter = 0;
        let uploadedFiles = [];
        let variationCounter = 0;
        let promotionCounter = 0;
        let productImageItems = []; // { id, file, previewUrl, uploadedUrl, path, title, description, status }
        let descriptionQuill = null;
        let isSyncingDescriptionFromImages = false;
        let isFillingDescription = false;
        let isSubmittingProduct = false;
        const uploadImageUrl = '{{ route("app.quill.upload-image") }}';
        const generateCaptionsUrl = '{{ route("app.products.generate-image-captions") }}';
        const storeProductUrl = '{{ route("app.products.store") }}';
        const createBtnDefaultLabel = 'Create Product';
        const createBtnFillingLabel = 'Waiting for description…';

        function setDescriptionFillingUI(active, message) {
            isFillingDescription = !!active;
            const wrap = document.getElementById('descriptionEditorWrap');
            const overlay = document.getElementById('descriptionFillingOverlay');
            const msgEl = document.getElementById('descriptionFillingMessage');

            if (wrap) {
                wrap.classList.toggle('is-filling', isFillingDescription);
            }
            if (overlay) {
                overlay.classList.toggle('hidden', !isFillingDescription);
            }
            if (msgEl && message) {
                msgEl.textContent = message;
            }
            updateCreateButtonState();
        }

        function updateCreateButtonState() {
            const submitBtn = document.getElementById('createProductBtn');
            const labelEl = document.getElementById('createProductBtnLabel');
            if (!submitBtn || isSubmittingProduct) return;

            const imagesBusy = productImageItems.some(i => i.status === 'uploading' || i.status === 'generating');
            const busy = isFillingDescription || imagesBusy;

            submitBtn.disabled = busy;
            if (labelEl) {
                labelEl.textContent = busy ? createBtnFillingLabel : createBtnDefaultLabel;
            }
        }
        const productsIndexUrl = '{{ route("app.products") }}';
        const csrfToken = '{{ csrf_token() }}';

        // Slug generation and URL preview functions
        function slugify(text) {
            return text.toString().toLowerCase()
                .replace(/\s+/g, '-')           // Replace spaces with -
                .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
                .replace(/\-\-+/g, '-')         // Replace multiple - with single -
                .replace(/^-+/, '')             // Trim - from start of text
                .replace(/-+$/, '');            // Trim - from end of text
        }

        function updateSlugFromName(name) {
            const slugInput = document.getElementById('slug');
            slugInput.value = slugify(name);
            updateLandingPageUrl();
        }

        function updateLandingPageUrl() {
            const slugInput = document.getElementById('slug');
            const slugPreview = document.getElementById('slugPreview');
            if (slugPreview) {
                slugPreview.textContent = slugInput.value || 'your-product-slug';
            }
        }

        function copyLandingPageUrl() {
            const slugInput = document.getElementById('slug');
            const slug = slugInput.value || 'your-product-slug';
            const baseUrl = '{{ $store ? url("/store/" . $store->subdomain . "/product/") : "" }}';
            const fullUrl = baseUrl + '/' + slug;
            
            navigator.clipboard.writeText(fullUrl).then(() => {
                showToast('URL copied to clipboard!', 'success');
            });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text || '';
            return div.innerHTML;
        }

        function showToast(message, type = 'info') {
            const colors = {
                info: 'bg-gradient-to-r from-purple-600 to-blue-600',
                success: 'bg-emerald-600',
                error: 'bg-red-600',
            };
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 ${colors[type] || colors.info} text-white px-6 py-4 rounded-lg shadow-lg z-50 flex items-center gap-3 max-w-md`;
            notification.innerHTML = `<div><p class="font-semibold">${escapeHtml(message)}</p></div>`;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 4000);
        }

        function renderImageCards() {
            const preview = document.getElementById('imagePreview');
            const preuploaded = document.getElementById('preuploadedImagesContainer');
            preview.innerHTML = '';
            preuploaded.innerHTML = '';

            productImageItems.forEach((item, index) => {
                const isMain = index === 0;
                const card = document.createElement('div');
                card.className = 'border border-white/10 rounded-lg p-4 bg-[#0a1628]';
                card.dataset.imageId = item.id;
                card.innerHTML = `
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="relative w-full md:w-40 flex-shrink-0">
                            <img src="${item.previewUrl}" class="w-full h-32 object-cover rounded-lg border border-white/10" />
                            <span class="absolute top-2 left-2 text-[10px] px-2 py-0.5 rounded ${isMain ? 'bg-emerald-600' : 'bg-black/60'} text-white">
                                ${isMain ? 'Main image' : 'Description image ' + index}
                            </span>
                        </div>
                        <div class="flex-1 space-y-3">
                            ${isMain ? `
                            <p class="text-sm text-emerald-300/90">Used as the main image at the top of the landing page. Not inserted into Description.</p>
                            <p class="text-xs ${item.status === 'error' ? 'text-red-400' : 'text-gray-500'}">
                                ${item.status === 'uploading' ? 'Uploading image...' :
                                  item.status === 'error' ? (item.error || 'Something went wrong') :
                                  'Ready — main landing image'}
                            </p>
                            ` : `
                            <div>
                                <label class="block text-xs font-medium text-gray-400 mb-1">Title</label>
                                <input type="text"
                                    class="image-title-input w-full px-3 py-2 bg-[#0f1c2e] border border-white/10 rounded text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    data-image-id="${item.id}"
                                    value="${escapeHtml(item.title || '')}"
                                    placeholder="${item.status === 'generating' ? 'AI is generating title...' : 'Image title'}"
                                    ${item.status === 'generating' ? 'disabled' : ''} />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-400 mb-1">Small description</label>
                                <textarea
                                    class="image-desc-input w-full px-3 py-2 bg-[#0f1c2e] border border-white/10 rounded text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    data-image-id="${item.id}"
                                    rows="2"
                                    placeholder="${item.status === 'generating' ? 'AI is generating description...' : 'Short description'}"
                                    ${item.status === 'generating' ? 'disabled' : ''}>${escapeHtml(item.description || '')}</textarea>
                            </div>
                            <p class="text-xs ${item.status === 'error' ? 'text-red-400' : 'text-gray-500'}">
                                ${item.status === 'uploading' ? 'Uploading image...' :
                                  item.status === 'generating' ? 'Generating title & description with AI...' :
                                  item.status === 'error' ? (item.error || 'Something went wrong') :
                                  'Ready — synced to Description'}
                            </p>
                            `}
                        </div>
                    </div>
                `;
                preview.appendChild(card);

                if (item.path) {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'preuploaded_images[]';
                    hidden.value = item.path;
                    preuploaded.appendChild(hidden);
                }
            });

            preview.querySelectorAll('.image-title-input').forEach(input => {
                input.addEventListener('input', function() {
                    const item = productImageItems.find(i => i.id === this.dataset.imageId);
                    if (item) {
                        item.title = this.value;
                        syncImagesToDescription();
                    }
                });
            });
            preview.querySelectorAll('.image-desc-input').forEach(input => {
                input.addEventListener('input', function() {
                    const item = productImageItems.find(i => i.id === this.dataset.imageId);
                    if (item) {
                        item.description = this.value;
                        syncImagesToDescription();
                    }
                });
            });

            updateCreateButtonState();
        }

        /** Images after the first (main) one — these fill the Description field. */
        function descriptionImageItems() {
            return productImageItems
                .map((item, index) => ({ item, index }))
                .filter(({ item, index }) => item.uploadedUrl && index > 0)
                .map(({ item }) => item);
        }

        function buildDescriptionHtmlFromImages() {
            return descriptionImageItems()
                .map((item) => {
                    const title = (item.title || '').trim();
                    const desc = (item.description || '').trim();
                    let html = '';
                    if (title) {
                        html += `<h2 class="ql-align-center"><strong>${escapeHtml(title)}</strong></h2>`;
                    }
                    if (desc) {
                        html += `<p class="ql-align-center">${escapeHtml(desc)}</p>`;
                    }
                    html += `<p class="ql-align-center"><img src="${escapeHtml(item.uploadedUrl)}"></p>`;
                    return html;
                })
                .join('');
        }

        function syncImagesToDescription() {
            const html = buildDescriptionHtmlFromImages();
            const textarea = document.getElementById('description');
            const items = descriptionImageItems();

            if (descriptionQuill) {
                isSyncingDescriptionFromImages = true;
                descriptionQuill.setText('');
                let index = 0;

                items.forEach((item, i) => {
                        const title = (item.title || '').trim();
                        const desc = (item.description || '').trim();

                        if (i > 0) {
                            descriptionQuill.insertText(index, '\n');
                            index += 1;
                        }

                        if (title) {
                            descriptionQuill.insertText(index, title + '\n', { bold: true });
                            descriptionQuill.formatLine(index, 1, { header: 2, align: 'center' });
                            index = descriptionQuill.getLength() - 1;
                        }

                        if (desc) {
                            descriptionQuill.insertText(index, desc + '\n');
                            descriptionQuill.formatLine(index, 1, { align: 'center' });
                            index = descriptionQuill.getLength() - 1;
                        }

                        descriptionQuill.insertEmbed(index, 'image', item.uploadedUrl);
                        descriptionQuill.formatLine(index, 1, { align: 'center' });
                        index = descriptionQuill.getLength() - 1;
                        descriptionQuill.insertText(index, '\n');
                        index = descriptionQuill.getLength() - 1;
                    });

                if (textarea) {
                    textarea.value = descriptionQuill.root.innerHTML || html;
                }
                isSyncingDescriptionFromImages = false;
                return;
            }

            // Quill not ready yet — still write HTML so create submit does not lose content
            if (textarea) {
                textarea.value = html;
            }
        }

        async function uploadImageFile(file) {
            const formData = new FormData();
            formData.append('image', file);
            formData.append('_token', csrfToken);

            const response = await fetch(uploadImageUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body: formData
            });
            const data = await response.json();
            if (!data.success || !data.url) {
                throw new Error(data.message || 'Image upload failed');
            }
            return data;
        }

        async function generateAiCaptions(count) {
            const name = document.getElementById('name')?.value?.trim() || '';
            const categoryId = document.getElementById('category_id')?.value || '';
            const categoryText = categoryId
                ? (document.querySelector(`#category_id option[value="${categoryId}"]`)?.textContent || '')
                : '';

            const response = await fetch(generateCaptionsUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    product_name: name || 'Product',
                    category: categoryText,
                    count: count,
                })
            });
            const data = await response.json();
            if (!data.success || !Array.isArray(data.captions)) {
                throw new Error(data.message || 'Failed to generate captions');
            }
            return data.captions;
        }

        async function processUploadedImages(files) {
            productImageItems = Array.from(files).map((file, index) => ({
                id: `img-${Date.now()}-${index}`,
                file,
                previewUrl: URL.createObjectURL(file),
                uploadedUrl: null,
                path: null,
                title: '',
                description: '',
                status: 'uploading',
                error: null,
            }));
            uploadedFiles = Array.from(files);
            setDescriptionFillingUI(true, 'Uploading images… description will fill automatically');
            document.getElementById('descriptionEditorWrap')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            renderImageCards();

            try {
                // Upload all images in parallel
                await Promise.all(productImageItems.map(async (item, index) => {
                    try {
                        const uploaded = await uploadImageFile(item.file);
                        item.uploadedUrl = uploaded.url;
                        item.path = uploaded.path;
                        // First image is main/hero only — no AI caption needed
                        item.status = index === 0 ? 'ready' : 'generating';
                    } catch (err) {
                        item.status = 'error';
                        item.error = err.message || 'Upload failed';
                    }
                }));
                setDescriptionFillingUI(true, 'AI is writing titles & description…');
                renderImageCards();

                const readyItems = productImageItems.filter((i, index) => index > 0 && i.status === 'generating');
                if (readyItems.length === 0) {
                    setDescriptionFillingUI(true, 'Filling description…');
                    syncImagesToDescription();
                    return;
                }

                try {
                    const captions = await generateAiCaptions(readyItems.length);
                    readyItems.forEach((item, index) => {
                        item.title = captions[index]?.title || `Feature ${index + 1}`;
                        item.description = captions[index]?.description || '';
                        item.status = 'ready';
                    });
                } catch (err) {
                    readyItems.forEach((item, index) => {
                        item.title = `Feature ${index + 1}`;
                        item.description = '';
                        item.status = 'ready';
                    });
                    showToast('AI captions unavailable — you can fill titles manually.', 'error');
                }

                setDescriptionFillingUI(true, 'Filling description…');
                renderImageCards();
                syncImagesToDescription();
            } finally {
                setDescriptionFillingUI(false);
            }
        }

        function previewImages(event) {
            const files = event.target.files;
            if (!files || files.length === 0) return;
            processUploadedImages(files);
        }

        function togglePromotions(enabled) {
            const promotionsContent = document.getElementById('promotionsContent');
            if (enabled) {
                promotionsContent.classList.remove('hidden');
            } else {
                promotionsContent.classList.add('hidden');
            }
        }

        function updatePromotionsJson() {
            const container = document.getElementById('promotionsContainer');
            const promotions = [];
            const promotionDivs = container.querySelectorAll('[id^="promotion-"]');
            
            promotionDivs.forEach((div) => {
                const minQty = div.querySelector('input[name*="[min_quantity]"]');
                const maxQty = div.querySelector('input[name*="[max_quantity]"]');
                const price = div.querySelector('input[name*="[price]"]');
                const compareAt = div.querySelector('input[name*="[compare_at_price]"]');
                const label = div.querySelector('input[name*="[label]"]');
                
                if (minQty && price) {
                    promotions.push({
                        label: label ? (label.value || null) : null,
                        min_quantity: minQty.value || '',
                        max_quantity: maxQty ? (maxQty.value || null) : null,
                        price: price.value || '',
                        compare_at_price: compareAt && compareAt.value ? compareAt.value : null
                    });
                }
            });
            
            document.getElementById('promotions_json').value = JSON.stringify(promotions);
            console.log('Updated promotions JSON:', promotions);
        }

        function addPromotion() {
            const container = document.getElementById('promotionsContainer');
            const noPromotionsMsg = document.getElementById('noPromotionsMessage');
            
            if (noPromotionsMsg) {
                noPromotionsMsg.style.display = 'none';
            }
            
            const promotionId = promotionCounter++;
            
            const promotionDiv = document.createElement('div');
            promotionDiv.className = 'border border-yellow-500/30 rounded-lg p-4 bg-[#0a1628] relative';
            promotionDiv.id = `promotion-${promotionId}`;
            promotionDiv.innerHTML = `
                <button 
                    type="button" 
                    onclick="removePromotion(${promotionId})"
                    class="absolute top-4 right-4 text-red-400 hover:text-red-300 transition"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-300 mb-1">Promotion Label</label>
                    <input 
                        type="text" 
                        name="promotions[${promotionId}][label]" 
                        placeholder="e.g., Shop Now, Buy Now, Order Now (leave empty for default)"
                        class="w-full px-3 py-2 text-sm bg-[#0f1c2e] border border-white/10 rounded text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-yellow-500"
                        oninput="updatePromotionsJson()"
                    />
                    <p class="text-xs text-gray-400 mt-1">This will replace "Buy" or "اشتري" in your landing page</p>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-300 mb-1">Min Quantity *</label>
                        <input 
                            type="number" 
                            name="promotions[${promotionId}][min_quantity]" 
                            min="1"
                            required
                            placeholder="e.g., 2"
                            class="w-full px-3 py-2 text-sm bg-[#0f1c2e] border border-white/10 rounded text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-yellow-500"
                            oninput="updatePromotionsJson()"
                        />
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-gray-300 mb-1">Max Quantity</label>
                        <input 
                            type="number" 
                            name="promotions[${promotionId}][max_quantity]" 
                            min="1"
                            placeholder="Leave empty for unlimited"
                            class="w-full px-3 py-2 text-sm bg-[#0f1c2e] border border-white/10 rounded text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-yellow-500"
                            oninput="updatePromotionsJson()"
                        />
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-gray-300 mb-1">Price per Unit ({{ $currencyCode }}) *</label>
                        <input 
                            type="number" 
                            name="promotions[${promotionId}][price]" 
                            step="0.01"
                            min="0"
                            required
                            placeholder="e.g., 90.00"
                            class="w-full px-3 py-2 text-sm bg-[#0f1c2e] border border-white/10 rounded text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-yellow-500"
                            oninput="updatePromotionsJson()"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-300 mb-1">Compare at Price ({{ $currencyCode }})</label>
                        <input 
                            type="number" 
                            name="promotions[${promotionId}][compare_at_price]" 
                            step="0.01"
                            min="0"
                            placeholder="Optional strikethrough"
                            class="w-full px-3 py-2 text-sm bg-[#0f1c2e] border border-white/10 rounded text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-yellow-500"
                            oninput="updatePromotionsJson()"
                        />
                    </div>
                </div>
                
                <div class="bg-yellow-500/10 border border-yellow-500/20 rounded p-2 text-xs text-yellow-300">
                    <strong>Example:</strong> Min: 2, Max: 4, Price: 90.00, Compare: 120.00 → Shows 90 with 120 struck through
                </div>
            `;
            
            container.appendChild(promotionDiv);
            updatePromotionsJson();
        }

        function removePromotion(promotionId) {
            const promotionDiv = document.getElementById(`promotion-${promotionId}`);
            if (promotionDiv) {
                promotionDiv.remove();
            }
            
            const container = document.getElementById('promotionsContainer');
            if (container.children.length === 0) {
                document.getElementById('noPromotionsMessage').style.display = 'block';
            }
            updatePromotionsJson();
        }

        function toggleVariations(enabled) {
            const variationsCard = document.getElementById('variationsCard');
            const priceField = document.getElementById('price');
            const comparePriceField = document.getElementById('compare_at_price');
            const stockField = document.getElementById('stock');
            const skuField = document.getElementById('sku');
            const basicPriceFields = [priceField, comparePriceField, stockField, skuField];
            
            if (enabled) {
                variationsCard.classList.remove('hidden');
                // Disable and clear basic price/stock fields when variations are enabled
                basicPriceFields.forEach(field => {
                    if (field) {
                        field.disabled = true;
                        field.classList.add('opacity-50', 'cursor-not-allowed');
                        // Remove required attribute and set value to empty or 0
                        field.removeAttribute('required');
                        if (field.type === 'number') {
                            field.value = '0';
                        }
                    }
                });
            } else {
                variationsCard.classList.add('hidden');
                // Re-enable basic price/stock fields
                basicPriceFields.forEach(field => {
                    if (field) {
                        field.disabled = false;
                        field.classList.remove('opacity-50', 'cursor-not-allowed');
                        // Re-add required attribute for price field
                        if (field.id === 'price') {
                            field.setAttribute('required', 'required');
                        }
                    }
                });
            }
        }

        function addVariation() {
            const container = document.getElementById('variationsContainer');
            const noVariationsMsg = document.getElementById('noVariationsMessage');
            
            if (noVariationsMsg) {
                noVariationsMsg.style.display = 'none';
            }
            
            const variationId = variationCounter++;
            
            const variationDiv = document.createElement('div');
            variationDiv.className = 'border border-blue-500/30 rounded-lg p-4 bg-[#0a1628] relative';
            variationDiv.id = `variation-${variationId}`;
            variationDiv.innerHTML = `
                <button 
                    type="button" 
                    onclick="removeVariation(${variationId})"
                    class="absolute top-4 right-4 text-red-400 hover:text-red-300 transition"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                
                <h4 class="text-sm font-semibold text-blue-400 mb-4">Variation ${variationId + 1}</h4>
                
                <div class="space-y-3">
                    <!-- Attributes Section -->
                    <div class="border border-white/10 rounded-lg p-3">
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-xs font-medium text-gray-300">Attributes</label>
                            <button 
                                type="button" 
                                onclick="addAttribute(${variationId})"
                                class="text-xs px-2 py-1 bg-cyan-600 hover:bg-cyan-700 text-white rounded transition"
                            >
                                + Add Attribute
                            </button>
                        </div>
                        <div id="attributes-container-${variationId}" class="space-y-2">
                            <!-- Attributes will be added here -->
                        </div>
                        <p class="text-xs text-gray-500 mt-2">e.g., Color: Red, Size: Large</p>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-300 mb-1">Price ({{ $currencyCode }}) *</label>
                            <input 
                                type="number" 
                                name="variations[${variationId}][price]" 
                                step="0.01"
                                min="0"
                                required
                                class="w-full px-3 py-2 text-sm bg-[#0f1c2e] border border-white/10 rounded text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="0.00"
                            />
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-300 mb-1">Compare at Price</label>
                            <input 
                                type="number" 
                                name="variations[${variationId}][compare_at_price]" 
                                step="0.01"
                                min="0"
                                class="w-full px-3 py-2 text-sm bg-[#0f1c2e] border border-white/10 rounded text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="0.00"
                            />
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-300 mb-1">Stock *</label>
                            <input 
                                type="number" 
                                name="variations[${variationId}][stock]" 
                                min="0"
                                value="0"
                                required
                                class="w-full px-3 py-2 text-sm bg-[#0f1c2e] border border-white/10 rounded text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="0"
                            />
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-300 mb-1">SKU</label>
                            <input 
                                type="text" 
                                name="variations[${variationId}][sku]" 
                                class="w-full px-3 py-2 text-sm bg-[#0f1c2e] border border-white/10 rounded text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="SKU-${variationId + 1}"
                            />
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2">
                            <input 
                                type="checkbox" 
                                name="variations[${variationId}][is_default]" 
                                value="1"
                                class="rounded bg-[#0f1c2e] border-white/10 text-blue-600 focus:ring-blue-500"
                            />
                            <span class="text-xs text-gray-300">Default variation</span>
                        </label>
                        
                        <label class="flex items-center gap-2">
                            <input 
                                type="checkbox" 
                                name="variations[${variationId}][is_active]" 
                                value="1"
                                checked
                                class="rounded bg-[#0f1c2e] border-white/10 text-blue-600 focus:ring-blue-500"
                            />
                            <span class="text-xs text-gray-300">Active</span>
                        </label>
                    </div>
                </div>
            `;
            
            container.appendChild(variationDiv);
            
            // Add first attribute by default
            addAttribute(variationId);
        }

        function addAttribute(variationId) {
            const container = document.getElementById(`attributes-container-${variationId}`);
            const attributeId = container.children.length;
            
            const attributeDiv = document.createElement('div');
            attributeDiv.className = 'flex gap-2';
            attributeDiv.innerHTML = `
                <input 
                    type="text" 
                    name="variations[${variationId}][attributes][${attributeId}][name]" 
                    placeholder="Attribute (e.g., Color)"
                    class="flex-1 px-3 py-2 text-sm bg-[#0f1c2e] border border-white/10 rounded text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-cyan-500"
                />
                <input 
                    type="text" 
                    name="variations[${variationId}][attributes][${attributeId}][value]" 
                    placeholder="Value (e.g., Red)"
                    class="flex-1 px-3 py-2 text-sm bg-[#0f1c2e] border border-white/10 rounded text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-cyan-500"
                />
                <button 
                    type="button" 
                    onclick="this.parentElement.remove()"
                    class="text-red-400 hover:text-red-300"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            `;
            
            container.appendChild(attributeDiv);
        }

        function removeVariation(variationId) {
            const variationDiv = document.getElementById(`variation-${variationId}`);
            if (variationDiv) {
                variationDiv.remove();
            }
            
            // Show no variations message if no variations left
            const container = document.getElementById('variationsContainer');
            if (container.children.length === 0) {
                document.getElementById('noVariationsMessage').style.display = 'block';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const generateToggle = document.getElementById('generate_landing_page');
            const aiNotice = document.getElementById('aiNotice');
            
            generateToggle.addEventListener('change', function() {
                if (this.checked) {
                    aiNotice.classList.remove('hidden');
                } else {
                    aiNotice.classList.add('hidden');
                }
            });

            // AJAX create — no full page reload; landing page job runs in background
            const form = document.querySelector('form');
            const submitBtn = document.getElementById('createProductBtn') || form.querySelector('button[type="submit"]');
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const busyImages = productImageItems.filter(i => i.status === 'uploading' || i.status === 'generating');
                if (busyImages.length > 0 || isFillingDescription) {
                    showToast('Please wait until the description finishes filling.', 'error');
                    return;
                }

                // Always sync image titles/descriptions into the description field before save
                syncImagesToDescription();

                const hasPromotionsCheckbox = document.getElementById('has_promotions');
                const promotionsContainer = document.getElementById('promotionsContainer');
                
                const promotions = [];
                const promotionDivs = promotionsContainer.querySelectorAll('[id^="promotion-"]');
                
                promotionDivs.forEach((div) => {
                    const minQty = div.querySelector('input[name*="[min_quantity]"]');
                    const maxQty = div.querySelector('input[name*="[max_quantity]"]');
                    const price = div.querySelector('input[name*="[price]"]');
                    const compareAt = div.querySelector('input[name*="[compare_at_price]"]');
                    const label = div.querySelector('input[name*="[label]"]');
                    
                    if (minQty && price && minQty.value && price.value) {
                        promotions.push({
                            label: label ? (label.value || null) : null,
                            min_quantity: minQty.value,
                            max_quantity: maxQty ? maxQty.value : null,
                            price: price.value,
                            compare_at_price: compareAt && compareAt.value ? compareAt.value : null
                        });
                    }
                });
                
                document.getElementById('promotions_json').value = JSON.stringify(promotions);
                
                if (hasPromotionsCheckbox.checked && promotions.length === 0) {
                    showToast('Promotions enabled but no tiers added! Add at least one pricing tier.', 'error');
                    document.getElementById('promotionsCard').scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }

                if (descriptionQuill) {
                    document.getElementById('description').value = descriptionQuill.root.innerHTML;
                } else if (productImageItems.some(i => i.uploadedUrl)) {
                    document.getElementById('description').value = buildDescriptionHtmlFromImages();
                }

                // Prefer pre-uploaded paths; clear file input to avoid double upload
                const fileInput = document.getElementById('images');
                if (productImageItems.some(i => i.path) && fileInput) {
                    fileInput.value = '';
                }

                const originalBtnHtml = submitBtn.innerHTML;
                isSubmittingProduct = true;
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span id="createProductBtnLabel">Creating...</span>
                `;

                try {
                    const formData = new FormData(form);
                    const response = await fetch(storeProductUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: formData
                    });

                    const data = await response.json().catch(() => ({}));

                    if (!response.ok || data.success === false) {
                        const firstError = data.errors
                            ? Object.values(data.errors).flat()[0]
                            : (data.message || 'Failed to create product');
                        throw new Error(firstError);
                    }

                    showToast(data.message || 'Product created successfully!', 'success');
                    // Soft navigate after short delay so toast is visible
                    setTimeout(() => {
                        window.location.href = data.redirect || productsIndexUrl;
                    }, 800);
                } catch (err) {
                    showToast(err.message || 'Failed to create product', 'error');
                    isSubmittingProduct = false;
                    submitBtn.innerHTML = originalBtnHtml;
                    updateCreateButtonState();
                }
            });

            const aiGenerateBtn = document.getElementById('aiGenerateBtn');
            
            aiGenerateBtn.addEventListener('click', async function() {
                const nameField = document.getElementById('name');
                const priceField = document.getElementById('price');
                
                if (!nameField.value || !priceField.value) {
                    alert('Please fill in at least the product name and price before generating the landing page.');
                    return;
                }
                
                const generateToggle = document.getElementById('generate_landing_page');
                generateToggle.checked = true;
                aiNotice.classList.remove('hidden');

                // Re-generate image captions for description images only (skip main/first)
                const readyItems = productImageItems
                    .map((item, index) => ({ item, index }))
                    .filter(({ item, index }) => item.uploadedUrl && index > 0)
                    .map(({ item }) => item);
                if (readyItems.length > 0) {
                    readyItems.forEach(i => { i.status = 'generating'; });
                    renderImageCards();
                    try {
                        const captions = await generateAiCaptions(readyItems.length);
                        readyItems.forEach((item, index) => {
                            item.title = captions[index]?.title || item.title || `Feature ${index + 1}`;
                            item.description = captions[index]?.description || item.description || '';
                            item.status = 'ready';
                        });
                        renderImageCards();
                        syncImagesToDescription();
                    } catch (err) {
                        readyItems.forEach(i => { i.status = 'ready'; });
                        renderImageCards();
                    }
                }
                
                showToast('AI Landing Page enabled. It will generate in the background when you create the product.', 'info');
            });
        });
    </script>

    <!-- Quill Rich Text Editor -->
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <style>
        #description-editor {
            min-height: 200px;
            background: white;
            border-radius: 0 0 0.5rem 0.5rem;
        }
        .ql-toolbar.ql-snow {
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
            background: white;
            border-color: rgba(255,255,255,0.1) !important;
        }
        .ql-container.ql-snow {
            border-bottom-left-radius: 0.5rem;
            border-bottom-right-radius: 0.5rem;
            border-color: rgba(255,255,255,0.1) !important;
            min-height: 180px;
            font-size: 16px;
        }
        .ql-editor {
            min-height: 180px;
            color: #1f2937;
        }
        .ql-editor.ql-blank::before {
            color: #9ca3af;
            font-style: normal;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const descriptionTextarea = document.getElementById('description');
            const editorElement = document.getElementById('description-editor');
            
            if (editorElement && descriptionTextarea) {
                const uploadUrl = '{{ route("app.quill.upload-image") }}';
                const csrfToken = '{{ csrf_token() }}';
                
                function imageHandler() {
                    const input = document.createElement('input');
                    input.setAttribute('type', 'file');
                    input.setAttribute('accept', 'image/*');
                    input.click();
                    
                    const quill = this.quill;
                    
                    input.onchange = function() {
                        const file = input.files[0];
                        if (!file) return;
                        
                        const formData = new FormData();
                        formData.append('image', file);
                        formData.append('_token', csrfToken);
                        
                        const range = quill.getSelection(true);
                        quill.insertText(range.index, 'Uploading...', { italic: true });
                        
                        fetch(uploadUrl, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrfToken },
                            body: formData
                        })
                        .then(r => r.json())
                        .then(data => {
                            quill.deleteText(range.index, 'Uploading...'.length);
                            if (data.success && data.url) {
                                quill.insertEmbed(range.index, 'image', data.url);
                                quill.setSelection(range.index + 1);
                            } else {
                                alert('Image upload failed');
                            }
                        })
                        .catch(err => {
                            quill.deleteText(range.index, 'Uploading...'.length);
                            console.error(err);
                            alert('Image upload failed');
                        });
                    };
                }
                
                const quill = new Quill('#description-editor', {
                    theme: 'snow',
                    placeholder: 'Enter product description...',
                    modules: {
                        toolbar: {
                            container: [
                                [{ 'header': [1, 2, 3, false] }],
                                ['bold', 'italic', 'underline', 'strike'],
                                [{ 'color': [] }, { 'background': [] }],
                                [{ 'align': [] }],
                                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                ['link', 'image', 'video'],
                                [{ 'indent': '-1'}, { 'indent': '+1' }],
                                ['blockquote', 'code-block'],
                                ['clean']
                            ],
                            handlers: { 'image': imageHandler }
                        }
                    }
                });

                // Expose for image → description sync
                descriptionQuill = quill;

                // Set initial value, or re-sync if images were uploaded before Quill finished loading
                if (descriptionTextarea.value) {
                    quill.root.innerHTML = descriptionTextarea.value;
                } else if (productImageItems.some(i => i.uploadedUrl)) {
                    syncImagesToDescription();
                }

                // Sync editor content to textarea (skip when we are writing from images)
                quill.on('text-change', function() {
                    if (isSyncingDescriptionFromImages) return;
                    descriptionTextarea.value = quill.root.innerHTML;
                });
            }
        });
    </script>
@endsection
