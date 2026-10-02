<x-stores-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Google Sheets') }}
            </h2>
            @if(isset($activeWorkspace))
                <p class="text-sm text-gray-600 mt-1">Connect Google Sheets for {{ $activeWorkspace->name }} — then optionally link them to products</p>
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

            <!-- Setup guide -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-5">
                <h3 class="text-sm font-semibold text-blue-900 mb-2">How to connect a Google Sheet</h3>
                <ol class="list-decimal list-inside space-y-1 text-sm text-blue-800">
                    <li>Open your Google Sheet → <strong>Extensions → Apps Script</strong></li>
                    <li>Paste the script below, then click <strong>Deploy → New deployment → Web app</strong></li>
                    <li>Set <strong>Execute as: Me</strong> and <strong>Who has access: Anyone</strong></li>
                    <li>Copy the Web App URL and paste it below as the Webhook URL</li>
                </ol>
                @include('stores.partials.google-sheets-apps-script')
            </div>

            <!-- Add connection -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Connect Google Sheet</h3>
                        <p class="text-sm text-gray-500 mt-1">Add a sheet for this workspace. Linking it to a product is optional.</p>
                    </div>

                    <form method="POST" action="{{ route('stores.google-sheets.store') }}" class="space-y-4">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Connection Name</label>
                                <input type="text" name="name" id="name" required value="{{ old('name') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition"
                                    placeholder="e.g. Ivoiremarket Orders">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="sheet_tab" class="block text-sm font-medium text-gray-700 mb-2">Sheet Tab Name</label>
                                <input type="text" name="sheet_tab" id="sheet_tab" value="{{ old('sheet_tab', 'Sheet1') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition"
                                    placeholder="Sheet1">
                            </div>
                        </div>

                        <div>
                            <label for="spreadsheet_url" class="block text-sm font-medium text-gray-700 mb-2">Google Sheet URL (optional)</label>
                            <input type="url" name="spreadsheet_url" id="spreadsheet_url" value="{{ old('spreadsheet_url') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition"
                                placeholder="https://docs.google.com/spreadsheets/d/...">
                            @error('spreadsheet_url')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="webhook_url" class="block text-sm font-medium text-gray-700 mb-2">Apps Script Webhook URL *</label>
                            <input type="url" name="webhook_url" id="webhook_url" required value="{{ old('webhook_url') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition"
                                placeholder="https://script.google.com/macros/s/.../exec">
                            @error('webhook_url')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <label class="flex items-center gap-3 text-sm text-gray-700 cursor-pointer select-none p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <input type="checkbox" name="is_enabled" value="1" {{ old('is_enabled', true) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="font-medium text-gray-900">Enable this connection</span>
                        </label>

                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition">
                            Connect Sheet
                        </button>
                    </form>
                </div>
            </div>

            <!-- Connected sheets -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Connected Sheets</h3>

                    @if($connections->isEmpty())
                        <div class="text-center py-10 border border-dashed border-gray-200 rounded-lg">
                            <p class="text-sm text-gray-500">No Google Sheets connected yet.</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($connections as $connection)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex flex-wrap items-start justify-between gap-4">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                                <h4 class="text-base font-semibold text-gray-900">{{ $connection->name }}</h4>
                                                @if($connection->is_enabled)
                                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">Enabled</span>
                                                @else
                                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-500">Disabled</span>
                                                @endif
                                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">
                                                    {{ $connection->products_count }} product{{ $connection->products_count === 1 ? '' : 's' }}
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-600">Tab: {{ $connection->sheet_tab }}</p>
                                            @if($connection->spreadsheet_url)
                                                <a href="{{ $connection->spreadsheet_url }}" target="_blank" rel="noopener" class="text-sm text-emerald-600 hover:underline truncate block">
                                                    Open spreadsheet →
                                                </a>
                                            @endif
                                        </div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <form method="POST" action="{{ route('stores.google-sheets.test', $connection) }}">
                                                @csrf
                                                <button type="submit" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:border-emerald-400 transition">
                                                    Test
                                                </button>
                                            </form>
                                            <a href="{{ route('stores.google-sheets.edit', $connection) }}"
                                               class="px-3 py-2 text-sm font-medium text-emerald-700 bg-emerald-50 rounded-lg hover:bg-emerald-100 transition">
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('stores.google-sheets.destroy', $connection) }}"
                                                  onsubmit="return confirm('Disconnect this Google Sheet? Products will be unlinked.');">
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
