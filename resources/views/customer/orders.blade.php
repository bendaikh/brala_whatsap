<x-customer-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-white">Commandes</h2>
                <p class="text-sm text-gray-400 mt-1">Gérer vos commandes et leads</p>
            </div>
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

    <!-- Filters -->
    <div class="bg-[#0f1c2e] border border-white/10 rounded-xl p-4 mb-6">
        <form method="GET" action="{{ route('app.orders') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm text-gray-400 mb-1">Rechercher</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Nom ou téléphone..." 
                    class="w-full px-4 py-2 bg-[#1a2d42] border border-white/10 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-cyan-500">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Statut</label>
                <select name="status" class="px-4 py-2 bg-[#1a2d42] border border-white/10 rounded-lg text-white focus:outline-none focus:border-cyan-500">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Tous</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmé</option>
                    <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Expédié</option>
                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Livré</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulé</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Du</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" 
                    class="px-4 py-2 bg-[#1a2d42] border border-white/10 rounded-lg text-white focus:outline-none focus:border-cyan-500">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Au</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" 
                    class="px-4 py-2 bg-[#1a2d42] border border-white/10 rounded-lg text-white focus:outline-none focus:border-cyan-500">
            </div>
            <button type="submit" class="px-6 py-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Filtrer
            </button>
            @if(request()->hasAny(['search', 'status', 'date_from', 'date_to']))
            <a href="{{ route('app.orders') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
                Réinitialiser
            </a>
            @endif
        </form>
    </div>

    @if(isset($orders) && $orders->count() > 0)
    <!-- Bulk actions bar -->
    <div id="bulkActionsBar" class="hidden mb-4 bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3 flex flex-wrap items-center justify-between gap-3">
        <p class="text-red-300 text-sm">
            <span id="selectedCount">0</span> commande(s) sélectionnée(s)
        </p>
        <button type="button" onclick="confirmBulkDelete()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition flex items-center gap-2 text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Supprimer la sélection
        </button>
    </div>

    <form id="bulkDeleteForm" method="POST" action="{{ route('app.orders.bulk-destroy') }}" class="hidden">
        @csrf
        <input type="hidden" name="from" value="orders">
        <div id="bulkDeleteIds"></div>
    </form>

    <!-- Orders Table -->
    <div class="bg-[#0f1c2e] border border-white/10 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/10 bg-[#1a2d42]">
                        <th class="px-4 py-3 text-left">
                            <input type="checkbox" id="selectAllOrders" onchange="toggleSelectAll(this)"
                                class="w-4 h-4 rounded border-white/20 bg-[#0f1c2e] text-cyan-500 focus:ring-cyan-500 focus:ring-offset-0 cursor-pointer">
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Produit</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Client</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Téléphone</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Prix</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Promotion/Variante</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Statut</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($orders as $order)
                    <tr class="hover:bg-white/5 transition">
                        <td class="px-4 py-4 whitespace-nowrap">
                            <input type="checkbox" class="order-checkbox w-4 h-4 rounded border-white/20 bg-[#0f1c2e] text-cyan-500 focus:ring-cyan-500 focus:ring-offset-0 cursor-pointer"
                                value="{{ $order->id }}" onchange="updateBulkSelection()">
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="text-white font-medium">#{{ $order->id }}</span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3">
                                @if($order->product && $order->product->first_image)
                                <img src="{{ $order->product->first_image }}" alt="{{ $order->product->name }}" class="w-10 h-10 rounded-lg object-cover">
                                @else
                                <div class="w-10 h-10 rounded-lg bg-gray-700 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                @endif
                                <div class="max-w-[200px]">
                                    <div class="text-white font-medium truncate">{{ $order->product->name ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="text-white">{{ $order->name ?? 'N/A' }}</span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <a href="tel:{{ $order->phone }}" class="text-cyan-400 hover:text-cyan-300">{{ $order->phone ?? 'N/A' }}</a>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="text-green-400 font-semibold">
                                {{ $order->selected_price ? number_format($order->selected_price, 2) . ' ' . $currencySymbol : ($order->product ? number_format($order->product->price, 2) . ' ' . $currencySymbol : 'N/A') }}
                            </span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            @if($order->promotion)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-500/20 text-yellow-400">
                                    {{ $order->promotion->label ?? $order->promotion->quantity_range }}
                                </span>
                            @elseif($order->variation)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-500/20 text-blue-400">
                                    @php
                                        $varName = 'Variante';
                                        if (!empty($order->variation->attributes) && is_array($order->variation->attributes)) {
                                            $parts = [];
                                            foreach ($order->variation->attributes as $key => $value) {
                                                $parts[] = $value;
                                            }
                                            $varName = implode(' / ', $parts);
                                        }
                                    @endphp
                                    {{ $varName }}
                                </span>
                            @else
                                <span class="text-gray-500">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-500/20 text-yellow-400',
                                    'confirmed' => 'bg-blue-500/20 text-blue-400',
                                    'shipped' => 'bg-purple-500/20 text-purple-400',
                                    'delivered' => 'bg-green-500/20 text-green-400',
                                    'cancelled' => 'bg-red-500/20 text-red-400',
                                ];
                                $statusLabels = [
                                    'pending' => 'En attente',
                                    'confirmed' => 'Confirmé',
                                    'shipped' => 'Expédié',
                                    'delivered' => 'Livré',
                                    'cancelled' => 'Annulé',
                                ];
                                $status = $order->status ?? 'pending';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$status] ?? 'bg-gray-500/20 text-gray-400' }}">
                                {{ $statusLabels[$status] ?? $status }}
                            </span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="text-gray-400 text-sm">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-1">
                                <button onclick="toggleOrderDetails({{ $order->id }})" class="p-2 text-gray-400 hover:text-white transition" title="Voir les détails">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                                <a href="{{ route('app.orders.edit', $order) }}" class="p-2 text-gray-400 hover:text-cyan-400 transition" title="Modifier">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <button type="button" onclick="confirmDeleteOrder({{ $order->id }})" class="p-2 text-gray-400 hover:text-red-400 transition" title="Supprimer">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <!-- Expandable Details Row -->
                    <tr id="order-details-{{ $order->id }}" class="hidden bg-[#1a2d42]/50">
                        <td colspan="10" class="px-4 py-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Customer Info -->
                                <div class="bg-[#0f1c2e] rounded-lg p-4">
                                    <h4 class="text-sm font-semibold text-cyan-400 mb-3">Informations Client</h4>
                                    <dl class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <dt class="text-gray-400">Nom:</dt>
                                            <dd class="text-white">{{ $order->name ?? 'N/A' }}</dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-gray-400">Téléphone:</dt>
                                            <dd class="text-white">{{ $order->phone ?? 'N/A' }}</dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-gray-400">Adresse:</dt>
                                            <dd class="text-white">{{ $order->address ?? 'N/A' }}</dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-gray-400">Ville:</dt>
                                            <dd class="text-white">{{ $order->city ?? 'N/A' }}</dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-gray-400">Langue:</dt>
                                            <dd class="text-white">{{ strtoupper($order->language ?? 'N/A') }}</dd>
                                        </div>
                                        @if($order->note)
                                        <div>
                                            <dt class="text-gray-400 mb-1">Note:</dt>
                                            <dd class="text-white bg-[#1a2d42] p-2 rounded">{{ $order->note }}</dd>
                                        </div>
                                        @endif
                                        @if($order->custom_fields && count($order->display_custom_fields) > 0)
                                        @foreach($order->display_custom_fields as $key => $value)
                                        <div class="flex justify-between">
                                            <dt class="text-gray-400">{{ ucfirst($key) }}:</dt>
                                            <dd class="text-white">{{ $value }}</dd>
                                        </div>
                                        @endforeach
                                        @endif
                                    </dl>
                                </div>
                                
                                <!-- Product Info -->
                                <div class="bg-[#0f1c2e] rounded-lg p-4">
                                    <h4 class="text-sm font-semibold text-cyan-400 mb-3">Informations Produit</h4>
                                    <dl class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <dt class="text-gray-400">Produit:</dt>
                                            <dd class="text-white">{{ $order->product->name ?? 'N/A' }}</dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-gray-400">Prix sélectionné:</dt>
                                            <dd class="text-green-400 font-semibold">{{ $order->selected_price ? number_format($order->selected_price, 2) . ' ' . $currencySymbol : 'N/A' }}</dd>
                                        </div>
                                        @if($order->promotion)
                                        <div class="flex justify-between">
                                            <dt class="text-gray-400">Promotion:</dt>
                                            <dd class="text-yellow-400">{{ $order->promotion->label ?? $order->promotion->quantity_range }}</dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-gray-400">Prix promotion:</dt>
                                            <dd class="text-yellow-400">{{ $order->promotion->price ? number_format($order->promotion->price, 2) . ' ' . $currencySymbol : 'N/A' }}</dd>
                                        </div>
                                        @endif
                                        @if($order->variation)
                                        <div class="flex justify-between">
                                            <dt class="text-gray-400">Variante:</dt>
                                            <dd class="text-blue-400">
                                                @php
                                                    $variantDisplay = 'Variante';
                                                    if (!empty($order->variation->attributes) && is_array($order->variation->attributes)) {
                                                        $parts = [];
                                                        foreach ($order->variation->attributes as $key => $value) {
                                                            $parts[] = ucfirst($key) . ': ' . $value;
                                                        }
                                                        $variantDisplay = implode(', ', $parts);
                                                    }
                                                @endphp
                                                {{ $variantDisplay }}
                                            </dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-gray-400">Prix variante:</dt>
                                            <dd class="text-blue-400">{{ $order->variation->price ? number_format($order->variation->price, 2) . ' ' . $currencySymbol : 'N/A' }}</dd>
                                        </div>
                                        @endif
                                    </dl>
                                </div>
                                
                                <!-- Order Info -->
                                <div class="bg-[#0f1c2e] rounded-lg p-4">
                                    <h4 class="text-sm font-semibold text-cyan-400 mb-3">Informations Commande</h4>
                                    <dl class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <dt class="text-gray-400">ID Commande:</dt>
                                            <dd class="text-white">#{{ $order->id }}</dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-gray-400">Statut:</dt>
                                            <dd>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$status] ?? 'bg-gray-500/20 text-gray-400' }}">
                                                    {{ $statusLabels[$status] ?? $status }}
                                                </span>
                                            </dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-gray-400">Créé le:</dt>
                                            <dd class="text-white">{{ $order->created_at->format('d/m/Y à H:i') }}</dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-gray-400">Adresse IP:</dt>
                                            <dd class="flex items-center gap-2 flex-wrap">
                                                <span class="text-white font-mono text-xs">{{ $order->ip_address ?? 'N/A' }}</span>
                                                @if($order->ip_address)
                                                    @include('customer.partials.ip-access-actions', [
                                                        'ipAddress' => $order->ip_address,
                                                        'blockedIpAddresses' => $blockedIpAddresses ?? [],
                                                    ])
                                                @endif
                                            </dd>
                                        </div>
                                        @if($order->user_agent)
                                        <div>
                                            <dt class="text-gray-400 mb-1">User Agent:</dt>
                                            <dd class="text-white text-xs bg-[#1a2d42] p-2 rounded break-all">{{ Str::limit($order->user_agent, 100) }}</dd>
                                        </div>
                                        @endif
                                    </dl>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($orders->hasPages())
        <div class="px-4 py-3 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-sm text-gray-400">
                Affichage de {{ $orders->firstItem() }} à {{ $orders->lastItem() }} sur {{ $orders->total() }} commandes
            </p>
            <div class="orders-pagination">
                {{ $orders->links() }}
            </div>
        </div>
        @else
        <div class="px-4 py-3 border-t border-white/10">
            <p class="text-sm text-gray-400">
                {{ $orders->total() }} commande(s)
            </p>
        </div>
        @endif
    </div>

    <!-- Single Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black/80 z-50 hidden items-center justify-center p-4">
        <div class="bg-[#0f1c2e] border border-white/10 rounded-xl p-6 max-w-md w-full">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-red-500/20 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white">Supprimer la commande</h3>
                    <p class="text-sm text-gray-400">Cette action est irréversible</p>
                </div>
            </div>
            <p class="text-gray-300 mb-6">Êtes-vous sûr de vouloir supprimer la commande <span id="deleteOrderLabel" class="font-semibold text-white"></span> ?</p>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition">
                    Annuler
                </button>
                <form id="deleteForm" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="from" value="orders">
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                        Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Delete Confirmation Modal -->
    <div id="bulkDeleteModal" class="fixed inset-0 bg-black/80 z-50 hidden items-center justify-center p-4">
        <div class="bg-[#0f1c2e] border border-white/10 rounded-xl p-6 max-w-md w-full">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-red-500/20 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white">Supprimer les commandes</h3>
                    <p class="text-sm text-gray-400">Cette action est irréversible</p>
                </div>
            </div>
            <p class="text-gray-300 mb-6">Êtes-vous sûr de vouloir supprimer <span id="bulkDeleteCount" class="font-semibold text-white">0</span> commande(s) sélectionnée(s) ?</p>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeBulkDeleteModal()" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition">
                    Annuler
                </button>
                <button type="button" onclick="submitBulkDelete()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                    Supprimer
                </button>
            </div>
        </div>
    </div>

    <style>
        .orders-pagination nav {
            display: flex;
            justify-content: center;
        }
        .orders-pagination nav > div:first-child {
            display: none;
        }
        .orders-pagination span, .orders-pagination a {
            display: inline-flex;
            align-items: center;
        }
        .orders-pagination .relative > span,
        .orders-pagination .relative > a {
            margin: 0 2px;
        }
        .orders-pagination span[aria-disabled="true"] span,
        .orders-pagination span[aria-current="page"] span {
            background-color: #1a2d42 !important;
            border-color: rgba(255,255,255,0.1) !important;
            color: #9ca3af !important;
        }
        .orders-pagination span[aria-current="page"] span {
            background-color: #0891b2 !important;
            border-color: #0891b2 !important;
            color: #fff !important;
        }
        .orders-pagination a span {
            background-color: #1a2d42 !important;
            border-color: rgba(255,255,255,0.1) !important;
            color: #e5e7eb !important;
        }
        .orders-pagination a:hover span {
            background-color: #243b55 !important;
            color: #fff !important;
        }
    </style>
    @else
    <!-- Empty State -->
    <div class="bg-[#0f1c2e] border border-white/10 rounded-xl p-16 text-center">
        <svg class="w-24 h-24 text-gray-600 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
        </svg>
        <h3 class="text-2xl font-bold text-white mb-3">Aucune commande</h3>
        <p class="text-gray-400 max-w-md mx-auto">
            Vous n'avez pas encore reçu de commandes. Les commandes apparaîtront ici lorsque les clients passeront des commandes via vos pages produits.
        </p>
    </div>
    @endif
    
    <script>
        function toggleOrderDetails(orderId) {
            const detailsRow = document.getElementById('order-details-' + orderId);
            if (detailsRow) {
                detailsRow.classList.toggle('hidden');
            }
        }

        function toggleSelectAll(master) {
            document.querySelectorAll('.order-checkbox').forEach(cb => {
                cb.checked = master.checked;
            });
            updateBulkSelection();
        }

        function updateBulkSelection() {
            const checkboxes = document.querySelectorAll('.order-checkbox');
            const checked = document.querySelectorAll('.order-checkbox:checked');
            const bar = document.getElementById('bulkActionsBar');
            const countEl = document.getElementById('selectedCount');
            const selectAll = document.getElementById('selectAllOrders');

            if (countEl) countEl.textContent = checked.length;

            if (bar) {
                if (checked.length > 0) {
                    bar.classList.remove('hidden');
                } else {
                    bar.classList.add('hidden');
                }
            }

            if (selectAll && checkboxes.length) {
                selectAll.checked = checked.length === checkboxes.length;
                selectAll.indeterminate = checked.length > 0 && checked.length < checkboxes.length;
            }
        }

        function confirmDeleteOrder(orderId) {
            const modal = document.getElementById('deleteModal');
            const form = document.getElementById('deleteForm');
            const label = document.getElementById('deleteOrderLabel');

            form.action = `/app/orders/${orderId}`;
            label.textContent = '#' + orderId;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function confirmBulkDelete() {
            const checked = document.querySelectorAll('.order-checkbox:checked');
            if (!checked.length) return;

            document.getElementById('bulkDeleteCount').textContent = checked.length;
            const modal = document.getElementById('bulkDeleteModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeBulkDeleteModal() {
            const modal = document.getElementById('bulkDeleteModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function submitBulkDelete() {
            const checked = document.querySelectorAll('.order-checkbox:checked');
            const container = document.getElementById('bulkDeleteIds');
            container.innerHTML = '';

            checked.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = cb.value;
                container.appendChild(input);
            });

            document.getElementById('bulkDeleteForm').submit();
        }

        document.getElementById('deleteModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeDeleteModal();
        });
        document.getElementById('bulkDeleteModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeBulkDeleteModal();
        });
    </script>
</x-customer-layout>
