<x-customer-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-white">System Connect - API Integration</h2>
            <p class="text-sm text-gray-400 mt-1">Connect your external application to automatically receive orders from your landing pages</p>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-6 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-200">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Settings -->
        <div class="lg:col-span-2 space-y-6">
            <!-- External API Integration -->
            <div class="bg-[#0f1c2e] border border-white/10 rounded-xl p-6">
                <div class="flex flex-wrap items-start justify-between gap-4 mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-white">Custom API Integration</h3>
                        <p class="text-sm text-gray-400 mt-1">Configure your external API to receive orders automatically when customers submit the landing page form</p>
                    </div>
                    @if($user->external_api_enabled && $user->external_api_url && $user->external_api_key_encrypted)
                        <span class="inline-flex items-center rounded-full bg-emerald-500/20 px-3 py-1 text-xs font-medium text-emerald-300">Active</span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-amber-500/15 px-3 py-1 text-xs font-medium text-amber-200">Not configured</span>
                    @endif
                </div>

                <form method="POST" action="{{ route('app.external-api-settings.save') }}" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label for="external_api_url" class="block text-sm font-medium text-gray-300 mb-2">Base API URL</label>
                        <input type="url" name="external_api_url" id="external_api_url"
                            value="{{ old('external_api_url', $user->external_api_url) }}"
                            class="w-full px-4 py-3 bg-[#0a1628] border border-white/10 rounded-lg text-white placeholder-gray-500 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 focus:outline-none transition"
                            placeholder="https://smanager.site">
                        <p class="mt-1 text-xs text-gray-400">
                            Enter the base URL of your application (WITHOUT /api at the end).<br>
                            ✅ Correct: <code class="text-emerald-400">https://smanager.site</code><br>
                            ❌ Wrong: <code class="text-red-400">https://smanager.site/api</code>
                        </p>
                        @error('external_api_url')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="external_api_key" class="block text-sm font-medium text-gray-300 mb-2">API Key</label>
                        <input type="password" name="external_api_key" id="external_api_key" autocomplete="off"
                            class="w-full px-4 py-3 bg-[#0a1628] border border-white/10 rounded-lg text-white placeholder-gray-500 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 focus:outline-none transition"
                            placeholder="{{ $user->external_api_key_encrypted ? 'New key to replace the current one' : 'Your API authentication key' }}">
                        <p class="mt-1 text-xs text-gray-400">Your API key is encrypted and securely stored. It never appears in plain text after saving.</p>
                        @error('external_api_key')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-3">
                        <label class="flex items-center gap-3 text-sm text-gray-300 cursor-pointer select-none p-3 bg-[#0a1628] rounded-lg border border-white/10 hover:border-emerald-500/50 transition">
                            <input type="checkbox" name="external_api_enabled" value="1" 
                                {{ $user->external_api_enabled ? 'checked' : '' }}
                                class="rounded border-white/20 bg-[#0a1628] text-emerald-500 focus:ring-emerald-500/30">
                            <div>
                                <div class="font-medium text-white">Enable API Integration</div>
                                <div class="text-xs text-gray-400 mt-0.5">Automatically push orders to your external API when landing page forms are submitted</div>
                            </div>
                        </label>
                        @if($user->external_api_key_encrypted)
                        <label class="flex items-center gap-2 text-sm text-gray-400 cursor-pointer select-none">
                            <input type="checkbox" name="clear_external_api_key" value="1" class="rounded border-white/20 bg-[#0a1628] text-cyan-500 focus:ring-cyan-500/30">
                            Remove saved API key
                        </label>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="px-5 py-2.5 bg-cyan-500 hover:bg-cyan-600 text-white text-sm font-semibold rounded-lg transition">
                            Save Settings
                        </button>
                    </div>
                </form>

                @if($user->external_api_url && $user->external_api_key_encrypted)
                    <form method="POST" action="{{ route('app.external-api-settings.test') }}" class="mt-4 pt-4 border-t border-white/10">
                        @csrf
                        <button type="submit" class="px-5 py-2.5 bg-[#0a1628] border border-white/15 hover:border-cyan-500/50 text-white text-sm font-medium rounded-lg transition">
                            Test API Connection
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Right Column - Info -->
        <div class="space-y-6">
            <!-- How it works -->
            <div class="bg-[#0f1c2e] border border-white/10 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4">How it works</h3>
                <ol class="space-y-3 text-sm text-gray-300">
                    <li class="flex gap-3">
                        <span class="flex-shrink-0 flex items-center justify-center w-6 h-6 rounded-full bg-cyan-500/20 text-cyan-400 text-xs font-bold">1</span>
                        <span>Create your Custom API Integration in your external app</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex-shrink-0 flex items-center justify-center w-6 h-6 rounded-full bg-cyan-500/20 text-cyan-400 text-xs font-bold">2</span>
                        <span>Generate an API Key for authentication</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex-shrink-0 flex items-center justify-center w-6 h-6 rounded-full bg-cyan-500/20 text-cyan-400 text-xs font-bold">3</span>
                        <span>Copy the Base API URL and API Key</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex-shrink-0 flex items-center justify-center w-6 h-6 rounded-full bg-cyan-500/20 text-cyan-400 text-xs font-bold">4</span>
                        <span>Paste them here and enable the integration</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex-shrink-0 flex items-center justify-center w-6 h-6 rounded-full bg-cyan-500/20 text-cyan-400 text-xs font-bold">5</span>
                        <span>Test the connection to verify it's working</span>
                    </li>
                </ol>
            </div>

            <!-- API Endpoints -->
            <div class="bg-[#0f1c2e] border border-white/10 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Expected API Endpoints</h3>
                <div class="space-y-3 text-xs">
                    <div class="bg-[#0a1628] border border-white/5 rounded-lg p-3">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-300 rounded text-[10px] font-bold">POST</span>
                            <code class="text-gray-300">/api/orders</code>
                        </div>
                        <p class="text-gray-400">Create a new order</p>
                    </div>
                    <div class="bg-[#0a1628] border border-white/5 rounded-lg p-3">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-2 py-0.5 bg-blue-500/20 text-blue-300 rounded text-[10px] font-bold">GET</span>
                            <code class="text-gray-300">/api/orders</code>
                        </div>
                        <p class="text-gray-400">Get all orders</p>
                    </div>
                </div>
            </div>

            <!-- Data Structure -->
            <div class="bg-[#0f1c2e] border border-white/10 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Order Data Structure</h3>
                <div class="bg-[#0a1628] border border-white/5 rounded-lg p-3">
                    <pre class="text-xs text-gray-300 overflow-x-auto"><code>{
  "client_name": "string",
  "client_phone": "string",
  "source": "whatsapp",
  "platform": "Bralam",
  "store_name": "string",
  "website": {
    "id": "number",
    "name": "Bralam - Store Name",
    "platform": "Bralam",
    "store_name": "string",
    "site_name": "string",
    "subdomain": "string",
    "domain": "string|null",
    "url": "string",
    "product_url": "string",
    "owner": {
      "id": "number",
      "name": "string",
      "company_name": "string",
      "email": "string",
      "phone": "string"
    },
    "contact": {
      "phone": "string",
      "email": "string"
    }
  },
  "items": [{
    "product_id": "number",
    "sku": "string",
    "name": "string",
    "quantity": "number",
    "price": "number"
  }],
  "notes": "string",
  "metadata": {
    "platform": "Bralam",
    "store_name": "string",
    "bralam_lead_id": "number",
    "bralam_product_id": "number",
    "bralam_variation_id": "number|null",
    "bralam_store_id": "number|null",
    "sku": "string",
    "language": "fr|en|ar",
    "created_at": "ISO8601",
    "website": { "...": "same as top-level website object" }
  }
}</code></pre>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-4">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <h4 class="text-sm font-semibold text-amber-300 mb-1">Security Notice</h4>
                        <p class="text-xs text-amber-200/80">Your API key is encrypted using Laravel's encryption and stored securely. Never share your API credentials with anyone.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-customer-layout>
