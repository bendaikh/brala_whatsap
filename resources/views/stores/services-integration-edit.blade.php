<x-stores-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Service Company
            </h2>
            @if(isset($activeWorkspace))
                <p class="text-sm text-gray-600 mt-1">{{ $integration->name }} · {{ $activeWorkspace->name }}</p>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('stores.services-integration.update', $integration) }}" class="space-y-4"
                          x-data="{ provider: '{{ old('provider', $integration->provider) }}' }">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Company Name</label>
                                <input type="text" name="name" id="name" required value="{{ old('name', $integration->name) }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Service Type</label>
                                <select name="type" id="type" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition">
                                    @foreach($types as $value => $label)
                                        <option value="{{ $value }}" @selected(old('type', $integration->type) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="provider" class="block text-sm font-medium text-gray-700 mb-2">API Provider</label>
                            <select name="provider" id="provider" required x-model="provider"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition">
                                @foreach($providers as $value => $label)
                                    <option value="{{ $value }}" @selected(old('provider', $integration->provider) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('provider')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="api_url" class="block text-sm font-medium text-gray-700 mb-2">Base API URL</label>
                            <input type="url" name="api_url" id="api_url" value="{{ old('api_url', $integration->api_url) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition"
                                :placeholder="provider === 'sureship' ? 'https://app.sureship.space/api/v1' : 'https://alfa-cod.com'">
                            @error('api_url')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="api_key" class="block text-sm font-medium text-gray-700 mb-2">
                                <span x-text="provider === 'sureship' ? 'API Token' : 'API Key'"></span>
                            </label>
                            <input type="password" name="api_key" id="api_key" autocomplete="off"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition"
                                placeholder="{{ $integration->hasApiKey() ? 'Enter new key to replace current one' : 'API authentication key' }}">
                            @error('api_key')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-show="provider === 'sureship'" x-cloak>
                            <div>
                                <label for="default_product_ref" class="block text-sm font-medium text-gray-700 mb-2">Default Product Ref (optional)</label>
                                <input type="text" name="default_product_ref" id="default_product_ref"
                                    value="{{ old('default_product_ref', $integration->getSetting('default_product_ref')) }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition"
                                    placeholder="Sureship product reference">
                                <p class="mt-1 text-xs text-gray-500">Must match a product ref that exists in Sureship.</p>
                            </div>
                            <div>
                                <label for="default_country" class="block text-sm font-medium text-gray-700 mb-2">Country Code</label>
                                <input type="text" name="default_country" id="default_country"
                                    value="{{ old('default_country', $integration->getSetting('default_country', 'CI')) }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition"
                                    placeholder="CI">
                            </div>
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes (optional)</label>
                            <textarea name="notes" id="notes" rows="2"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition">{{ old('notes', $integration->notes) }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-3">
                            <label class="flex items-center gap-3 text-sm text-gray-700 cursor-pointer select-none p-3 bg-gray-50 rounded-lg border border-gray-200 hover:border-emerald-300 transition">
                                <input type="checkbox" name="is_enabled" value="1" {{ old('is_enabled', $integration->is_enabled) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                <div>
                                    <div class="font-medium text-gray-900">Enable this integration</div>
                                    <div class="text-xs text-gray-500 mt-0.5">Activate once credentials are ready</div>
                                </div>
                            </label>

                            @if($integration->hasApiKey())
                                <label class="flex items-center gap-2 text-sm text-gray-500 cursor-pointer select-none">
                                    <input type="checkbox" name="clear_api_key" value="1" class="rounded border-gray-300 text-red-500 focus:ring-red-500">
                                    Remove saved API key
                                </label>
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-3 pt-2">
                            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition">
                                Save Changes
                            </button>
                            <a href="{{ route('stores.services-integration') }}"
                               class="px-5 py-2.5 bg-white border border-gray-300 hover:border-gray-400 text-gray-700 text-sm font-medium rounded-lg transition">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-stores-layout>
