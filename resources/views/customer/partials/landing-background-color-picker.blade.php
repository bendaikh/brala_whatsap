{{-- Landing page background color picker --}}
@php
    $bgColorValue = old('landing_page_background_color', $bgColor ?? (isset($product) ? ($product->landing_page_background_color ?? '#1e3a8a') : '#1e3a8a'));
    if (!is_string($bgColorValue) || !preg_match('/^#[0-9A-Fa-f]{6}$/', $bgColorValue)) {
        $bgColorValue = '#1e3a8a';
    }
    $bgColorValue = strtolower($bgColorValue);
@endphp
<div class="{{ $wrapperClass ?? 'pb-4 border-b border-white/10' }}"
     x-data="{ bgColor: '{{ $bgColorValue }}' }">
    <label class="block text-sm font-medium text-gray-300 mb-2">Landing Page Background Color</label>
    <p class="text-xs text-gray-500 mb-3">{{ $hint ?? 'Used as the full landing page background. AI can choose this when generating; you can override it anytime.' }}</p>
    <div class="flex flex-wrap items-center gap-3">
        <input
            type="color"
            x-model="bgColor"
            name="landing_page_background_color"
            id="landing_page_background_color"
            value="{{ $bgColorValue }}"
            class="h-12 w-16 px-1 border border-white/20 rounded-lg cursor-pointer bg-white"
        >
        <input
            type="text"
            x-model="bgColor"
            value="{{ $bgColorValue }}"
            maxlength="7"
            class="w-32 px-3 py-2 border border-white/20 rounded-lg text-gray-900 font-mono text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="#1e3a8a"
        >
        <div class="h-12 w-24 rounded-lg border border-white/20 shadow-inner"
             style="background-color: {{ $bgColorValue }};"
             x-bind:style="'background-color:' + bgColor"></div>
    </div>
</div>
