<x-customer-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-white">Modifier la commande #{{ $lead->id }}</h2>
                <p class="text-sm text-gray-400 mt-1">{{ $lead->product->name ?? 'Produit' }}</p>
            </div>
            <a href="{{ route('app.orders') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
                Retour aux commandes
            </a>
        </div>
    </x-slot>

    @php
        $currencySymbol = isset($activeWorkspace) ? $activeWorkspace->getCurrencySymbol() : (config('workspace.currencies.MAD.symbol') ?? 'DHS');
    @endphp

    @if(session('success'))
    <div class="mb-6 bg-green-500/10 border border-green-500/30 rounded-lg px-4 py-3 text-green-400">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-[#0f1c2e] border border-white/10 rounded-xl p-6 max-w-3xl">
        <form method="POST" action="{{ route('app.orders.update', $lead) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Nom</label>
                    <input type="text" name="name" value="{{ old('name', $lead->name) }}"
                        class="w-full px-4 py-3 bg-[#1a2d42] border border-white/10 rounded-lg text-white focus:border-cyan-500 focus:outline-none">
                    @error('name')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Téléphone</label>
                    <input type="text" name="phone" value="{{ old('phone', $lead->phone) }}"
                        class="w-full px-4 py-3 bg-[#1a2d42] border border-white/10 rounded-lg text-white focus:border-cyan-500 focus:outline-none">
                    @error('phone')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Ville</label>
                    <input type="text" name="city" value="{{ old('city', $lead->city) }}"
                        class="w-full px-4 py-3 bg-[#1a2d42] border border-white/10 rounded-lg text-white focus:border-cyan-500 focus:outline-none">
                    @error('city')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Adresse</label>
                    <input type="text" name="address" value="{{ old('address', $lead->address) }}"
                        class="w-full px-4 py-3 bg-[#1a2d42] border border-white/10 rounded-lg text-white focus:border-cyan-500 focus:outline-none">
                    @error('address')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Statut</label>
                <select name="status" class="w-full px-4 py-3 bg-[#1a2d42] border border-white/10 rounded-lg text-white focus:border-cyan-500 focus:outline-none">
                    @foreach(['pending' => 'En attente', 'confirmed' => 'Confirmé', 'shipped' => 'Expédié', 'delivered' => 'Livré', 'cancelled' => 'Annulé'] as $value => $label)
                    <option value="{{ $value }}" {{ old('status', $lead->status ?? 'pending') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Note</label>
                <textarea name="note" rows="4"
                    class="w-full px-4 py-3 bg-[#1a2d42] border border-white/10 rounded-lg text-white focus:border-cyan-500 focus:outline-none resize-none">{{ old('note', $lead->note) }}</textarea>
                @error('note')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="bg-[#1a2d42] rounded-lg p-4 text-sm text-gray-400 space-y-2">
                <div class="flex justify-between"><span>Produit:</span><span class="text-white">{{ $lead->product->name ?? 'N/A' }}</span></div>
                <div class="flex justify-between"><span>Prix:</span><span class="text-green-400">{{ $lead->selected_price ? number_format($lead->selected_price, 2) . ' ' . $currencySymbol : 'N/A' }}</span></div>
                @if($lead->promotion)
                <div class="flex justify-between"><span>Quantité:</span><span class="text-yellow-400">{{ $lead->order_quantity }}</span></div>
                @endif
                <div class="flex justify-between"><span>Date:</span><span class="text-white">{{ $lead->created_at->format('d/m/Y H:i') }}</span></div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-6 py-3 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg font-medium transition">
                    Enregistrer les modifications
                </button>
                <a href="{{ route('app.orders') }}" class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium transition">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</x-customer-layout>
