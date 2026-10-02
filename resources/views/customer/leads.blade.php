<x-customer-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-white">Leads</h2>
            <p class="text-sm text-gray-400 mt-1">Gérez les demandes de contact de vos produits</p>
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

    <div class="bg-[#0f1c2e] border border-white/10 rounded-xl overflow-hidden">
        @if($leads->isEmpty())
            <div class="text-center py-16">
                <svg class="w-24 h-24 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <h3 class="text-xl font-semibold text-gray-400 mb-2">Aucun lead pour le moment</h3>
                <p class="text-gray-500">Les demandes de contact des visiteurs apparaîtront ici</p>
            </div>
        @else
            <!-- Bulk actions bar -->
            <div id="bulkActionsBar" class="hidden px-6 py-3 bg-red-500/10 border-b border-red-500/30 flex flex-wrap items-center justify-between gap-3">
                <p class="text-red-300 text-sm">
                    <span id="selectedCount">0</span> lead(s) sélectionné(s)
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
                <input type="hidden" name="from" value="leads">
                <div id="bulkDeleteIds"></div>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-[#0a1628] border-b border-white/10">
                        <tr>
                            <th class="px-6 py-4 text-left">
                                <input type="checkbox" id="selectAllLeads" onchange="toggleSelectAll(this)"
                                    class="w-4 h-4 rounded border-white/20 bg-[#0f1c2e] text-cyan-500 focus:ring-cyan-500 focus:ring-offset-0 cursor-pointer">
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Produit</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Nom</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Téléphone</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Langue</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Note</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($leads as $lead)
                            <tr class="hover:bg-white/5 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" class="lead-checkbox w-4 h-4 rounded border-white/20 bg-[#0f1c2e] text-cyan-500 focus:ring-cyan-500 focus:ring-offset-0 cursor-pointer"
                                        value="{{ $lead->id }}" onchange="updateBulkSelection()">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                    {{ $lead->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex items-center gap-3">
                                        @if($lead->product && $lead->product->first_image)
                                            <img src="{{ $lead->product->first_image }}" alt="{{ $lead->product->name }}" class="w-10 h-10 rounded object-cover">
                                        @endif
                                        <span class="text-white font-medium">{{ $lead->product->name ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        <span class="text-white font-medium">{{ $lead->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="tel:{{ $lead->phone }}" class="flex items-center gap-2 text-emerald-400 hover:text-emerald-300 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        {{ $lead->phone }}
                                    </a>
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->phone) }}" target="_blank" class="text-xs text-green-400 hover:text-green-300 transition mt-1 block">
                                        WhatsApp
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        @if($lead->language === 'fr') bg-blue-500/20 text-blue-300
                                        @elseif($lead->language === 'en') bg-purple-500/20 text-purple-300
                                        @else bg-green-500/20 text-green-300
                                        @endif">
                                        {{ strtoupper($lead->language) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-300 max-w-xs">
                                    @if($lead->note)
                                        <div class="truncate" title="{{ $lead->note }}">
                                            {{ $lead->note }}
                                        </div>
                                    @else
                                        <span class="text-gray-500 italic">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-1">
                                        <button onclick="toggleLeadDetails({{ $lead->id }})" class="p-2 text-gray-400 hover:text-cyan-400 transition rounded-lg hover:bg-white/10" title="Voir les détails">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </button>
                                        <a href="{{ route('app.orders.edit', $lead) }}" class="p-2 text-gray-400 hover:text-cyan-400 transition rounded-lg hover:bg-white/10" title="Modifier">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <button type="button" onclick="confirmDeleteLead({{ $lead->id }})" class="p-2 text-gray-400 hover:text-red-400 transition rounded-lg hover:bg-white/10" title="Supprimer">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <!-- Expandable Details Row -->
                            <tr id="lead-details-{{ $lead->id }}" class="hidden bg-[#1a2d42]/50">
                                <td colspan="8" class="px-6 py-4">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <!-- Customer Info -->
                                        <div class="bg-[#0f1c2e] rounded-lg p-4">
                                            <h4 class="text-sm font-semibold text-cyan-400 mb-3 flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                                Informations Client
                                            </h4>
                                            <dl class="space-y-2 text-sm">
                                                <div class="flex justify-between">
                                                    <dt class="text-gray-400">Nom:</dt>
                                                    <dd class="text-white font-medium">{{ $lead->name ?? 'N/A' }}</dd>
                                                </div>
                                                <div class="flex justify-between">
                                                    <dt class="text-gray-400">Téléphone:</dt>
                                                    <dd class="text-white">{{ $lead->phone ?? 'N/A' }}</dd>
                                                </div>
                                                <div class="flex justify-between">
                                                    <dt class="text-gray-400">Adresse:</dt>
                                                    <dd class="text-white">{{ $lead->address ?? 'N/A' }}</dd>
                                                </div>
                                                <div class="flex justify-between">
                                                    <dt class="text-gray-400">Ville:</dt>
                                                    <dd class="text-white">{{ $lead->city ?? 'N/A' }}</dd>
                                                </div>
                                                <div class="flex justify-between">
                                                    <dt class="text-gray-400">Langue:</dt>
                                                    <dd class="text-white">{{ strtoupper($lead->language ?? 'N/A') }}</dd>
                                                </div>
                                                @if($lead->note)
                                                <div>
                                                    <dt class="text-gray-400 mb-1">Note:</dt>
                                                    <dd class="text-white bg-[#1a2d42] p-2 rounded text-xs">{{ $lead->note }}</dd>
                                                </div>
                                                @endif
                                                @if($lead->custom_fields && count($lead->display_custom_fields) > 0)
                                                @foreach($lead->display_custom_fields as $key => $value)
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
                                            <h4 class="text-sm font-semibold text-cyan-400 mb-3 flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                </svg>
                                                Informations Produit
                                            </h4>
                                            <dl class="space-y-2 text-sm">
                                                <div class="flex justify-between">
                                                    <dt class="text-gray-400">Produit:</dt>
                                                    <dd class="text-white font-medium">{{ $lead->product->name ?? 'N/A' }}</dd>
                                                </div>
                                                <div class="flex justify-between">
                                                    <dt class="text-gray-400">Prix sélectionné:</dt>
                                                    <dd class="text-green-400 font-semibold">{{ $lead->selected_price ? number_format($lead->selected_price, 2) . ' ' . $currencySymbol : 'N/A' }}</dd>
                                                </div>
                                                @if($lead->promotion)
                                                <div class="flex justify-between">
                                                    <dt class="text-gray-400">Promotion:</dt>
                                                    <dd class="text-yellow-400">{{ $lead->promotion->label ?? $lead->promotion->quantity_range ?? 'N/A' }}</dd>
                                                </div>
                                                <div class="flex justify-between">
                                                    <dt class="text-gray-400">Prix promotion:</dt>
                                                    <dd class="text-yellow-400">{{ $lead->promotion->price ? number_format($lead->promotion->price, 2) . ' ' . $currencySymbol : 'N/A' }}</dd>
                                                </div>
                                                @endif
                                                @if($lead->variation)
                                                <div class="flex justify-between">
                                                    <dt class="text-gray-400">Variante:</dt>
                                                    <dd class="text-blue-400">
                                                        @php
                                                            $variantDisplay = 'Variante';
                                                            if (!empty($lead->variation->attributes) && is_array($lead->variation->attributes)) {
                                                                $parts = [];
                                                                foreach ($lead->variation->attributes as $key => $value) {
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
                                                    <dd class="text-blue-400">{{ $lead->variation->price ? number_format($lead->variation->price, 2) . ' ' . $currencySymbol : 'N/A' }}</dd>
                                                </div>
                                                @endif
                                            </dl>
                                        </div>
                                        
                                        <!-- Order Info -->
                                        <div class="bg-[#0f1c2e] rounded-lg p-4">
                                            <h4 class="text-sm font-semibold text-cyan-400 mb-3 flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                Informations Commande
                                            </h4>
                                            <dl class="space-y-2 text-sm">
                                                <div class="flex justify-between">
                                                    <dt class="text-gray-400">ID Lead:</dt>
                                                    <dd class="text-white font-mono">#{{ $lead->id }}</dd>
                                                </div>
                                                @if($lead->status)
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
                                                    $status = $lead->status;
                                                @endphp
                                                <div class="flex justify-between items-center">
                                                    <dt class="text-gray-400">Statut:</dt>
                                                    <dd>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$status] ?? 'bg-gray-500/20 text-gray-400' }}">
                                                            {{ $statusLabels[$status] ?? $status }}
                                                        </span>
                                                    </dd>
                                                </div>
                                                @endif
                                                <div class="flex justify-between">
                                                    <dt class="text-gray-400">Créé le:</dt>
                                                    <dd class="text-white">{{ $lead->created_at->format('d/m/Y à H:i') }}</dd>
                                                </div>
                                        @if($lead->ip_address)
                                        <div class="flex justify-between items-center">
                                            <dt class="text-gray-400">Adresse IP:</dt>
                                            <dd class="flex items-center gap-2 flex-wrap">
                                                <span class="text-white font-mono text-xs">{{ $lead->ip_address }}</span>
                                                @include('customer.partials.ip-access-actions', [
                                                    'ipAddress' => $lead->ip_address,
                                                    'blockedIpAddresses' => $blockedIpAddresses ?? [],
                                                ])
                                            </dd>
                                        </div>
                                        @endif
                                                @if($lead->user_agent)
                                                <div>
                                                    <dt class="text-gray-400 mb-1">Navigateur:</dt>
                                                    <dd class="text-white text-xs bg-[#1a2d42] p-2 rounded break-all">{{ Str::limit($lead->user_agent, 80) }}</dd>
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

            @if($leads->hasPages())
                <div class="px-6 py-4 bg-[#0a1628] border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <p class="text-sm text-gray-400">
                        Affichage de {{ $leads->firstItem() }} à {{ $leads->lastItem() }} sur {{ $leads->total() }} leads
                    </p>
                    <div class="leads-pagination">
                        {{ $leads->links() }}
                    </div>
                </div>
            @else
                <div class="px-6 py-3 bg-[#0a1628] border-t border-white/10">
                    <p class="text-sm text-gray-400">{{ $leads->total() }} lead(s)</p>
                </div>
            @endif

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
                            <h3 class="text-xl font-bold text-white">Supprimer le lead</h3>
                            <p class="text-sm text-gray-400">Cette action est irréversible</p>
                        </div>
                    </div>
                    <p class="text-gray-300 mb-6">Êtes-vous sûr de vouloir supprimer le lead <span id="deleteLeadLabel" class="font-semibold text-white"></span> ?</p>
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition">
                            Annuler
                        </button>
                        <form id="deleteForm" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="from" value="leads">
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
                            <h3 class="text-xl font-bold text-white">Supprimer les leads</h3>
                            <p class="text-sm text-gray-400">Cette action est irréversible</p>
                        </div>
                    </div>
                    <p class="text-gray-300 mb-6">Êtes-vous sûr de vouloir supprimer <span id="bulkDeleteCount" class="font-semibold text-white">0</span> lead(s) sélectionné(s) ?</p>
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
                .leads-pagination nav { display: flex; justify-content: center; }
                .leads-pagination nav > div:first-child { display: none; }
                .leads-pagination span[aria-current="page"] span {
                    background-color: #0891b2 !important;
                    border-color: #0891b2 !important;
                    color: #fff !important;
                }
                .leads-pagination a span {
                    background-color: #1a2d42 !important;
                    border-color: rgba(255,255,255,0.1) !important;
                    color: #e5e7eb !important;
                }
                .leads-pagination a:hover span {
                    background-color: #243b55 !important;
                    color: #fff !important;
                }
                .leads-pagination span[aria-disabled="true"] span {
                    background-color: #1a2d42 !important;
                    border-color: rgba(255,255,255,0.1) !important;
                    color: #9ca3af !important;
                }
            </style>
        @endif
    </div>
    
    <script>
        function toggleLeadDetails(leadId) {
            const detailsRow = document.getElementById('lead-details-' + leadId);
            if (detailsRow) {
                detailsRow.classList.toggle('hidden');
            }
        }

        function toggleSelectAll(master) {
            document.querySelectorAll('.lead-checkbox').forEach(cb => {
                cb.checked = master.checked;
            });
            updateBulkSelection();
        }

        function updateBulkSelection() {
            const checkboxes = document.querySelectorAll('.lead-checkbox');
            const checked = document.querySelectorAll('.lead-checkbox:checked');
            const bar = document.getElementById('bulkActionsBar');
            const countEl = document.getElementById('selectedCount');
            const selectAll = document.getElementById('selectAllLeads');

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

        function confirmDeleteLead(leadId) {
            const modal = document.getElementById('deleteModal');
            const form = document.getElementById('deleteForm');
            const label = document.getElementById('deleteLeadLabel');

            form.action = `/app/orders/${leadId}`;
            label.textContent = '#' + leadId;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function confirmBulkDelete() {
            const checked = document.querySelectorAll('.lead-checkbox:checked');
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
            const checked = document.querySelectorAll('.lead-checkbox:checked');
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
