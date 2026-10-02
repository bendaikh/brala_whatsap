<x-stores-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Services Integration') }}
            </h2>
            @if(isset($activeWorkspace))
                <p class="text-sm text-gray-600 mt-1">Connect service companies for {{ $activeWorkspace->name }}</p>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
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

            <!-- Add Service Company -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Add Service Company</h3>
                        <p class="text-sm text-gray-500 mt-1">Integrate a delivery, payment, or logistics company for this workspace.</p>
                    </div>

                    <form method="POST" action="{{ route('stores.services-integration.store') }}" class="space-y-4" x-data="{ provider: '{{ old('provider', 'sureship') }}' }">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Company Name</label>
                                <input type="text" name="name" id="name" required value="{{ old('name') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition"
                                    placeholder="e.g. Sureship, Alfa COD">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Service Type</label>
                                <select name="type" id="type" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition">
                                    @foreach($types as $value => $label)
                                        <option value="{{ $value }}" @selected(old('type', 'delivery') === $value)>{{ $label }}</option>
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
                                    <option value="{{ $value }}" @selected(old('provider', 'sureship') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Choose how orders are sent to this company.</p>
                            @error('provider')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="api_url" class="block text-sm font-medium text-gray-700 mb-2">Base API URL</label>
                            <input type="url" name="api_url" id="api_url" value="{{ old('api_url') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition"
                                :placeholder="provider === 'sureship' ? 'https://app.sureship.space/api/v1' : 'https://alfa-cod.com'">
                            <p class="mt-1 text-xs text-gray-500" x-show="provider === 'sureship'">
                                Sureship: use <code class="text-emerald-600">https://app.sureship.space/api/v1</code>
                            </p>
                            <p class="mt-1 text-xs text-gray-500" x-show="provider !== 'sureship'">
                                Base URL without <code class="text-emerald-600">/api</code> at the end.
                            </p>
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
                                :placeholder="provider === 'sureship' ? 'Sureship api_token' : 'API authentication key'">
                            <p class="mt-1 text-xs text-gray-500">Encrypted and stored securely. Never shown in plain text after saving.</p>
                            @error('api_key')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-show="provider === 'sureship'" x-cloak>
                            <div>
                                <label for="default_product_ref" class="block text-sm font-medium text-gray-700 mb-2">Default Product Ref (optional)</label>
                                <input type="text" name="default_product_ref" id="default_product_ref" value="{{ old('default_product_ref') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition"
                                    placeholder="Sureship product reference">
                                <p class="mt-1 text-xs text-gray-500">Used when the store product has no SKU. Must exist in Sureship.</p>
                            </div>
                            <div>
                                <label for="default_country" class="block text-sm font-medium text-gray-700 mb-2">Country Code</label>
                                <input type="text" name="default_country" id="default_country" value="{{ old('default_country', 'CI') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition"
                                    placeholder="CI">
                            </div>
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes (optional)</label>
                            <textarea name="notes" id="notes" rows="2"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition"
                                placeholder="Account ID, region, contact, etc.">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <label class="flex items-center gap-3 text-sm text-gray-700 cursor-pointer select-none p-3 bg-gray-50 rounded-lg border border-gray-200 hover:border-emerald-300 transition">
                            <input type="checkbox" name="is_enabled" value="1" {{ old('is_enabled') ? 'checked' : '' }}
                                class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <div class="font-medium text-gray-900">Enable this integration</div>
                                <div class="text-xs text-gray-500 mt-0.5">Activate once credentials are ready</div>
                            </div>
                        </label>

                        <div>
                            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition">
                                Add Service Company
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Integrated Companies -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Integrated Companies</h3>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ $integrations->count() }} service{{ $integrations->count() === 1 ? '' : 's' }} configured for this workspace
                        </p>
                    </div>

                    @if($integrations->isEmpty())
                        <div class="text-center py-10 border border-dashed border-gray-200 rounded-lg">
                            <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            <p class="mt-3 text-sm text-gray-500">No service companies yet. Add your first integration above.</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($integrations as $integration)
                                <div class="border border-gray-200 rounded-lg p-4 hover:border-emerald-200 transition">
                                    <div class="flex flex-wrap items-start justify-between gap-4">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                                <h4 class="text-base font-semibold text-gray-900">{{ $integration->name }}</h4>
                                                <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-700">
                                                    {{ $integration->provider_label }}
                                                </span>
                                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">
                                                    {{ $integration->type_label }}
                                                </span>
                                                @if($integration->isConfigured())
                                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">Active</span>
                                                @elseif($integration->is_enabled)
                                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700">Enabled · incomplete</span>
                                                @else
                                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-500">Disabled</span>
                                                @endif
                                            </div>
                                            @if($integration->api_url)
                                                <p class="text-sm text-gray-600 truncate">{{ $integration->api_url }}</p>
                                            @else
                                                <p class="text-sm text-gray-400">No API URL set</p>
                                            @endif
                                            @if($integration->notes)
                                                <p class="text-xs text-gray-500 mt-1">{{ $integration->notes }}</p>
                                            @endif
                                            <p class="text-xs text-gray-400 mt-2">
                                                API key: {{ $integration->hasApiKey() ? 'Saved' : 'Not set' }}
                                            </p>
                                            <div class="mt-3">
                                                <p class="text-xs font-medium text-gray-600 mb-1">Assigned stores</p>
                                                @if($integration->stores->isEmpty())
                                                    <p class="text-xs text-gray-400 mb-2">No store affected yet</p>
                                                @else
                                                    <div class="flex flex-wrap gap-1.5 mb-2">
                                                        @foreach($integration->stores as $store)
                                                            <form method="POST" action="{{ route('stores.services-integration.unassign-store', $integration) }}" class="inline">
                                                                @csrf
                                                                <input type="hidden" name="store_id" value="{{ $store->id }}">
                                                                <button type="submit" class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 hover:bg-red-50 hover:text-red-600 transition" title="Unassign store">
                                                                    {{ $store->name }}
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </form>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                @php
                                                    $unassignedStores = $workspaceStores->filter(fn ($s) => (int) $s->service_integration_id !== (int) $integration->id);
                                                @endphp
                                                @if($unassignedStores->isNotEmpty())
                                                    <form method="POST" action="{{ route('stores.services-integration.assign-store', $integration) }}" class="flex flex-wrap items-center gap-2 mt-2">
                                                        @csrf
                                                        <select name="store_id" required class="text-xs border border-gray-300 rounded-lg px-2 py-1.5 text-gray-700 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                                                            <option value="">Affect a store…</option>
                                                            @foreach($unassignedStores as $store)
                                                                <option value="{{ $store->id }}">
                                                                    {{ $store->name }}{{ $store->service_integration_id ? ' (reassign)' : '' }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <button type="submit" class="text-xs px-3 py-1.5 bg-emerald-600 text-white font-medium rounded-lg hover:bg-emerald-700 transition">
                                                            Assign
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-2">
                                            @if($integration->api_url && $integration->hasApiKey())
                                                <form method="POST" action="{{ route('stores.services-integration.test', $integration) }}">
                                                    @csrf
                                                    <button type="submit" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:border-emerald-400 transition">
                                                        Test
                                                    </button>
                                                </form>
                                            @endif
                                            <a href="{{ route('stores.services-integration.edit', $integration) }}"
                                               class="px-3 py-2 text-sm font-medium text-emerald-700 bg-emerald-50 rounded-lg hover:bg-emerald-100 transition">
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('stores.services-integration.destroy', $integration) }}"
                                                  onsubmit="return confirm('Remove {{ addslashes($integration->name) }} from this workspace?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition">
                                                    Remove
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-stores-layout>
