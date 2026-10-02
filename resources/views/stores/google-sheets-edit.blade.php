<x-stores-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Google Sheet</h2>
            @if(isset($activeWorkspace))
                <p class="text-sm text-gray-600 mt-1">{{ $connection->name }} · {{ $activeWorkspace->name }}</p>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('stores.google-sheets.update', $connection) }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Connection Name</label>
                                <input type="text" name="name" id="name" required value="{{ old('name', $connection->name) }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition">
                            </div>
                            <div>
                                <label for="sheet_tab" class="block text-sm font-medium text-gray-700 mb-2">Sheet Tab Name</label>
                                <input type="text" name="sheet_tab" id="sheet_tab" value="{{ old('sheet_tab', $connection->sheet_tab) }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition">
                            </div>
                        </div>

                        <div>
                            <label for="spreadsheet_url" class="block text-sm font-medium text-gray-700 mb-2">Google Sheet URL</label>
                            <input type="url" name="spreadsheet_url" id="spreadsheet_url" value="{{ old('spreadsheet_url', $connection->spreadsheet_url) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition">
                        </div>

                        <div>
                            <label for="webhook_url" class="block text-sm font-medium text-gray-700 mb-2">Apps Script Webhook URL *</label>
                            <input type="url" name="webhook_url" id="webhook_url" required value="{{ old('webhook_url', $connection->webhook_url) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition">
                        </div>

                        <label class="flex items-center gap-3 text-sm text-gray-700 cursor-pointer select-none p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <input type="checkbox" name="is_enabled" value="1" {{ old('is_enabled', $connection->is_enabled) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="font-medium text-gray-900">Enable this connection</span>
                        </label>

                        <div class="flex gap-3 pt-2">
                            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition">
                                Save Changes
                            </button>
                            <a href="{{ route('stores.google-sheets') }}" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:border-gray-400 transition">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-stores-layout>
