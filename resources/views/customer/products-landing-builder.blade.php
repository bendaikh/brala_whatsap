@extends('layouts.customer')

@section('content')
<!-- Quill Rich Text Editor -->
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<style>
    [id^="description-editor-"] {
        min-height: 200px;
        background: white;
    }
    .ql-toolbar.ql-snow {
        border-top-left-radius: 0.5rem;
        border-top-right-radius: 0.5rem;
    }
    .ql-container.ql-snow {
        border-bottom-left-radius: 0.5rem;
        border-bottom-right-radius: 0.5rem;
        min-height: 180px;
        font-size: 16px;
    }
    .ql-editor {
        min-height: 180px;
    }
</style>
@php
    $sectionsJson = json_encode($product->landing_page_sections ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    $pageDataFrJson = json_encode($product->landing_page_fr ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    $pageDataEnJson = json_encode($product->landing_page_en ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    $pageDataArJson = json_encode($product->landing_page_ar ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
@endphp

<div x-data="landingPageBuilder()" x-init="init()" class="min-h-screen">
    <div class="fixed top-0 left-0 right-0 bg-[#0f1c2e] border-b border-white/10 z-50 shadow-lg">
        <div class="px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('app.products') }}" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-lg transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back
                </a>
                <h1 class="text-xl font-bold text-white">Edit Landing Page: {{ $product->name }}</h1>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-2 bg-gray-800 rounded-lg px-3 py-1.5 border border-white/10">
                    <label class="text-xs text-gray-400 whitespace-nowrap">BG Color</label>
                    <input type="color"
                           x-model="backgroundColor"
                           class="h-8 w-10 p-0 border-0 rounded cursor-pointer bg-transparent">
                    <input type="text"
                           x-model="backgroundColor"
                           maxlength="7"
                           class="w-24 px-2 py-1 rounded text-xs font-mono text-gray-900 bg-white border border-gray-300">
                </div>

                <div class="flex gap-2 bg-gray-800 rounded-lg p-1">
                    <button type="button" x-on:click="currentLang = 'fr'" 
                            x-bind:class="currentLang === 'fr' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white'"
                            class="px-3 py-2 rounded font-medium text-sm transition">
                        FR
                    </button>
                    <button type="button" x-on:click="currentLang = 'en'" 
                            x-bind:class="currentLang === 'en' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white'"
                            class="px-3 py-2 rounded font-medium text-sm transition">
                        EN
                    </button>
                    <button type="button" x-on:click="currentLang = 'ar'" 
                            x-bind:class="currentLang === 'ar' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white'"
                            class="px-3 py-2 rounded font-medium text-sm transition">
                        AR
                    </button>
                </div>
                
                @if($store)
                <a href="{{ \App\Support\StoreDomain::productUrl($store, $product->slug) }}" target="_blank" 
                   class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-lg transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    Preview Live
                </a>
                @endif
                
                <button type="button" x-on:click="saveChanges()" x-bind:disabled="saving"
                        class="px-6 py-2 bg-emerald-500 hover:bg-emerald-600 disabled:bg-gray-600 text-white font-bold rounded-lg transition flex items-center gap-2">
                    <svg class="w-5 h-5" x-bind:class="{'animate-spin': saving}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span x-text="saving ? 'Saving...' : 'Save Changes'"></span>
                </button>
            </div>
        </div>
    </div>

    <div class="pt-20">
        <div x-show="showSuccess" x-transition 
             class="fixed top-24 right-6 bg-emerald-500 text-white px-6 py-3 rounded-lg shadow-xl z-50 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>Changes saved successfully!</span>
        </div>

        <div class="container mx-auto px-4 py-8 space-y-8">
            
            <div class="bg-[#0f1c2e] rounded-xl shadow-lg p-6 border border-white/10">
                <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-2">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                    </svg>
                    Page Theme — Background Color
                </h2>
                <div class="flex flex-wrap items-center gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Background Color</label>
                        <div class="flex items-center gap-3">
                            <input type="color"
                                   x-model="backgroundColor"
                                   class="h-12 w-16 px-1 border border-white/20 rounded-lg cursor-pointer bg-white">
                            <input type="text"
                                   x-model="backgroundColor"
                                   maxlength="7"
                                   pattern="^#[0-9A-Fa-f]{6}$"
                                   class="w-32 px-3 py-2 border border-white/20 rounded-lg text-gray-900 font-mono text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="#1e3a8a">
                            <div class="h-12 w-32 rounded-lg border border-white/20 shadow-inner"
                                 x-bind:style="'background-color:' + backgroundColor"></div>
                        </div>
                        <p class="mt-2 text-sm text-gray-400">AI picks this from the product when generating the landing page. You can change it anytime and click Save Changes.</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                    </svg>
                    Hero Section
                </h2>
                <div class="space-y-4">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-sm text-blue-800">
                            <strong>Note:</strong> The title will use your product name automatically. Edit the product name in the main product details if you want to change the title.
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <span x-text="'Hero Description (' + currentLang.toUpperCase() + ')'"></span>
                        </label>
                        <textarea x-bind:value="(pageData[currentLang] && pageData[currentLang].hero_description) || ''"
                                  x-on:input="if (!pageData[currentLang]) pageData[currentLang] = {}; pageData[currentLang].hero_description = $event.target.value"
                                  rows="4"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Enter hero description"></textarea>
                    </div>
                </div>
            </div>

            <!-- Description Section -->
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Description Section
                </h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <span x-text="'Description (' + currentLang.toUpperCase() + ')'"></span>
                        </label>
                        <!-- Quill editors per language (only visible one is active) -->
                        <div x-show="currentLang === 'fr'">
                            <div id="description-editor-fr" class="bg-white rounded-lg"></div>
                        </div>
                        <div x-show="currentLang === 'en'">
                            <div id="description-editor-en" class="bg-white rounded-lg"></div>
                        </div>
                        <div x-show="currentLang === 'ar'">
                            <div id="description-editor-ar" class="bg-white rounded-lg"></div>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Use the toolbar to format your description with bold, italic, colors, lists, and more.</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <div class="flex items-center gap-4">
                        <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
                            </svg>
                            Product Sections
                        </h2>
                        <button type="button" x-on:click="showProductSections = !showProductSections"
                                class="px-4 py-2 rounded-lg font-semibold text-sm transition flex items-center gap-2"
                                x-bind:class="showProductSections ? 'bg-gray-200 text-gray-700 hover:bg-gray-300' : 'bg-gray-700 text-white hover:bg-gray-600'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" x-bind:d="showProductSections ? 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z' : 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21'"></path>
                            </svg>
                            <span x-text="showProductSections ? 'Hiding' : 'Showing'"></span>
                        </button>
                    </div>
                    <button type="button" x-on:click="addSection()" x-show="showProductSections"
                            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Section
                    </button>
                </div>

                <div class="space-y-6" x-show="showProductSections" x-transition>
                    <template x-for="(section, index) in sections" x-bind:key="index">
                        <div class="border-2 border-gray-200 hover:border-purple-400 rounded-xl p-6 transition relative">
                            <div class="absolute top-4 right-4 flex gap-2">
                                <button type="button" x-on:click="moveSection(index, 'up')" x-show="index > 0"
                                        class="p-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                    </svg>
                                </button>
                                <button type="button" x-on:click="moveSection(index, 'down')" x-show="index < sections.length - 1"
                                        class="p-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <button type="button" x-on:click="deleteSection(index)"
                                        class="p-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>

                            <div class="grid md:grid-cols-2 gap-6 pr-32">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Section Image</label>
                                    <div x-show="section.image" class="relative mb-3">
                                        <img x-bind:src="section.image && section.image.startsWith('http') ? section.image : '/storage/' + section.image" 
                                             class="w-full h-48 object-cover rounded-lg border border-gray-300"
                                             onerror="this.src='https://via.placeholder.com/400x300?text=Image+Not+Found'">
                                        <button type="button" x-on:click="section.image = null"
                                                class="absolute top-2 right-2 p-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <label x-show="!section.image" 
                                           class="block w-full h-48 border-2 border-dashed border-gray-300 rounded-lg hover:border-purple-500 transition cursor-pointer flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="text-sm text-gray-500">Click to upload</span>
                                        <input type="file" accept="image/*" class="hidden" x-on:change="uploadSectionImage($event, index)">
                                    </label>
                                </div>

                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">
                                            <span x-text="'Title (' + currentLang.toUpperCase() + ')'"></span>
                                        </label>
                                        <input type="text" 
                                               x-bind:value="section['title_' + currentLang]"
                                               x-on:input="section['title_' + currentLang] = $event.target.value"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-2 focus:ring-purple-500"
                                               placeholder="Enter title">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">
                                            <span x-text="'Description (' + currentLang.toUpperCase() + ')'"></span>
                                        </label>
                                        <textarea x-bind:value="section['description_' + currentLang]"
                                                  x-on:input="section['description_' + currentLang] = $event.target.value"
                                                  rows="5"
                                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-2 focus:ring-purple-500"
                                                  placeholder="Enter description"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div x-show="sections.length === 0" class="text-center py-12 border-2 border-dashed border-gray-300 rounded-xl">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <p class="text-gray-500 text-lg mb-4">No sections yet</p>
                        <button type="button" x-on:click="addSection()"
                                class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition">
                            Add Your First Section
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function landingPageBuilder() {
    return {
        currentLang: 'fr',
        saving: false,
        showSuccess: false,
        backgroundColor: {!! json_encode($product->landing_page_background_color ?? '#1e3a8a') !!},
        showProductSections: {!! json_encode($product->landing_page_fr['show_product_sections'] ?? true) !!},
        sections: {!! $sectionsJson !!},
        pageData: {
            fr: {!! $pageDataFrJson !!},
            en: {!! $pageDataEnJson !!},
            ar: {!! $pageDataArJson !!}
        },
        
        init() {
            console.log('Landing page builder initialized');
            this.initQuillEditors();
        },
        
        initQuillEditors() {
            var self = this;
            var toolbar = [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link', 'image', 'video'],
                [{ 'indent': '-1'}, { 'indent': '+1' }],
                ['blockquote', 'code-block'],
                ['clean']
            ];
            
            var uploadUrl = '{{ route("app.quill.upload-image") }}';
            var csrfToken = '{{ csrf_token() }}';
            
            function imageHandler() {
                var input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/*');
                input.click();
                
                var quill = this.quill;
                
                input.onchange = function() {
                    var file = input.files[0];
                    if (!file) return;
                    
                    var formData = new FormData();
                    formData.append('image', file);
                    formData.append('_token', csrfToken);
                    
                    var range = quill.getSelection(true);
                    quill.insertText(range.index, 'Uploading image...', { italic: true });
                    
                    fetch(uploadUrl, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken },
                        body: formData
                    })
                    .then(function(response) { return response.json(); })
                    .then(function(data) {
                        quill.deleteText(range.index, 'Uploading image...'.length);
                        if (data.success && data.url) {
                            quill.insertEmbed(range.index, 'image', data.url);
                            quill.setSelection(range.index + 1);
                        } else {
                            alert('Image upload failed');
                        }
                    })
                    .catch(function(err) {
                        quill.deleteText(range.index, 'Uploading image...'.length);
                        console.error('Upload error:', err);
                        alert('Image upload failed');
                    });
                };
            }
            
            ['fr', 'en', 'ar'].forEach(function(lang) {
                setTimeout(function() {
                    var el = document.getElementById('description-editor-' + lang);
                    if (el && !el.classList.contains('ql-container')) {
                        var quill = new Quill('#description-editor-' + lang, {
                            theme: 'snow',
                            placeholder: 'Enter description...',
                            modules: {
                                toolbar: {
                                    container: toolbar,
                                    handlers: { 'image': imageHandler }
                                }
                            }
                        });
                        
                        if (lang === 'ar') {
                            quill.root.setAttribute('dir', 'rtl');
                        }
                        
                        if (self.pageData[lang] && self.pageData[lang].description) {
                            quill.root.innerHTML = self.pageData[lang].description;
                        }
                        
                        quill.on('text-change', function() {
                            if (!self.pageData[lang]) self.pageData[lang] = {};
                            self.pageData[lang].description = quill.root.innerHTML;
                        });
                    }
                }, 200);
            });
        },
        
        addSection() {
            this.sections.push({
                title_fr: '',
                description_fr: '',
                title_en: '',
                description_en: '',
                title_ar: '',
                description_ar: '',
                image: null
            });
        },
        
        deleteSection(index) {
            if (confirm('Are you sure you want to delete this section?')) {
                this.sections.splice(index, 1);
            }
        },
        
        moveSection(index, direction) {
            var newIndex = direction === 'up' ? index - 1 : index + 1;
            if (newIndex >= 0 && newIndex < this.sections.length) {
                var temp = this.sections[index];
                this.sections[index] = this.sections[newIndex];
                this.sections[newIndex] = temp;
            }
        },
        
        uploadSectionImage(event, index) {
            var file = event.target.files[0];
            if (!file) return;
            
            var formData = new FormData();
            formData.append('image', file);
            var self = this;
            
            fetch('{{ route("app.products.upload-image", $product->id) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    self.sections[index].image = data.path;
                } else {
                    alert('Failed to upload image');
                }
            })
            .catch(function(error) {
                console.error('Upload error:', error);
                alert('Failed to upload image');
            });
        },
        
        saveChanges() {
            this.saving = true;
            var self = this;
            
            fetch('{{ route("app.products.save-landing-builder", $product->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    sections: this.sections,
                    page_data: this.pageData,
                    show_product_sections: this.showProductSections,
                    landing_page_background_color: this.backgroundColor
                })
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    self.showSuccess = true;
                    setTimeout(function() {
                        self.showSuccess = false;
                    }, 3000);
                } else {
                    alert('Failed to save changes');
                }
            })
            .catch(function(error) {
                console.error('Save error:', error);
                alert('Failed to save changes');
            })
            .finally(function() {
                self.saving = false;
            });
        }
    };
}
</script>
@endsection
