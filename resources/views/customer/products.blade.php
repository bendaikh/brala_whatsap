@extends('layouts.customer')

@section('content')
    @php
        $currencySymbol = isset($activeWorkspace) ? $activeWorkspace->getCurrencySymbol() : (config('workspace.currencies.MAD.symbol') ?? 'DHS');
    @endphp
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-white flex items-center gap-3">
                    <svg class="w-8 h-8 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    Catalogue Produits
                </h2>
                <p class="text-sm text-gray-400 mt-1">Gérez vos produits pour que l'IA puisse les utiliser dans les conversations</p>
            </div>
            <div class="flex items-center gap-3">
                <button class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Import en masse
                </button>
                <a href="{{ route('app.products.create') }}" class="px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-semibold rounded-lg transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Ajouter un produit
                </a>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        @if(session('success'))
            <div class="bg-emerald-500/20 border border-emerald-500/50 text-emerald-400 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <!-- Search Bar -->
        <div class="bg-[#0f1c2e] border border-white/10 rounded-lg px-4 py-3 flex items-center gap-3">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input 
                type="text" 
                placeholder="Rechercher un produit..." 
                class="flex-1 bg-transparent border-none text-white placeholder-gray-500 focus:outline-none"
            />
        </div>

        @if($products->count() > 0)
            <!-- Products Table -->
            <div class="bg-[#0f1c2e] border border-white/10 rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                <table class="w-full min-w-[1000px]">
                    <thead class="bg-[#0a1628] border-b border-white/10">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Stock</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">AI Images</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">AI Page</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @foreach($products as $product)
                            <tr class="hover:bg-white/5 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $product->first_image }}" alt="{{ $product->name }}" class="w-12 h-12 rounded-lg object-cover border border-white/10" />
                                        <div>
                                            <div class="text-sm font-medium text-white">{{ $product->name }}</div>
                                            <div class="text-xs text-gray-400">{{ $product->sku ?? 'No SKU' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-300">{{ $product->category->name ?? 'Uncategorized' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-white">{{ number_format($product->price, 2) }} {{ $currencySymbol }}</div>
                                    @if($product->compare_at_price)
                                        <div class="text-xs text-gray-400 line-through">{{ number_format($product->compare_at_price, 2) }} {{ $currencySymbol }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-300">{{ $product->stock }}</span>
                                </td>
                                <td class="px-6 py-4" data-product-id="{{ $product->id }}">
                                    @if($product->ai_images_status === 'completed' && !empty($product->ai_generated_images))
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-full bg-gradient-to-r from-emerald-600/20 to-green-600/20 text-emerald-400 border border-emerald-500/30">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                {{ count($product->ai_generated_images) }} Images
                                            </span>
                                            <button onclick="viewGeneratedImages({{ $product->id }})" class="text-cyan-400 hover:text-cyan-300 transition" title="View Generated Images">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    @elseif($product->ai_images_status === 'pending' || $product->ai_images_status === 'generating')
                                        <div class="space-y-1 min-w-[200px]">
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="text-xs font-semibold text-yellow-400">
                                                    {{ $product->ai_images_status === 'pending' ? 'Starting...' : 'Generating...' }}
                                                </span>
                                                <span class="text-xs text-gray-400 image-progress-text">
                                                    {{ $product->ai_images_generated ?? 0 }}/{{ $product->ai_images_total ?? 5 }}
                                                </span>
                                            </div>
                                            <div class="w-full bg-gray-700 rounded-full h-2">
                                                <div class="bg-gradient-to-r from-yellow-500 to-orange-500 h-2 rounded-full transition-all duration-500 image-progress-bar" 
                                                     style="width: {{ $product->ai_images_progress ?? 0 }}%">
                                                </div>
                                            </div>
                                        </div>
                                    @elseif($product->ai_images_status === 'failed')
                                        <button onclick="generateProductImages({{ $product->id }})" class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-full bg-red-600/20 text-red-400 hover:bg-orange-600/20 hover:text-orange-400 transition border border-red-500/30 hover:border-orange-500/30">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                            Retry
                                        </button>
                                    @else
                                        <button onclick="generateProductImages({{ $product->id }})" class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-full bg-gray-700/50 text-gray-400 hover:bg-orange-600/20 hover:text-orange-400 transition border border-gray-600 hover:border-orange-500/30">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            Generate
                                        </button>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($product->landing_page_status === 'completed' && ($product->landing_page_hero_description || $product->landing_page_ar))
                                        <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-full bg-gradient-to-r from-purple-600/20 to-blue-600/20 text-purple-400 border border-purple-500/30">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                            </svg>
                                            AI Generated
                                        </span>
                                    @elseif($product->landing_page_status === 'pending' || $product->landing_page_status === 'processing')
                                        <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-full bg-yellow-600/20 text-yellow-400 border border-yellow-500/30">
                                            <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                            {{ $product->landing_page_status === 'pending' ? 'Pending' : 'Generating...' }}
                                        </span>
                                    @elseif($product->landing_page_status === 'failed')
                                        <button onclick="generateLandingPage({{ $product->id }})" class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-full bg-red-600/20 text-red-400 hover:bg-purple-600/20 hover:text-purple-400 transition border border-red-500/30 hover:border-purple-500/30">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                            Retry
                                        </button>
                                    @else
                                        <button onclick="generateLandingPage({{ $product->id }})" class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-full bg-gray-700/50 text-gray-400 hover:bg-purple-600/20 hover:text-purple-400 transition border border-gray-600 hover:border-purple-500/30">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                            </svg>
                                            Generate
                                        </button>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($product->is_active)
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-emerald-500/20 text-emerald-400">Active</span>
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-500/20 text-gray-400">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($store)
                                        <a href="{{ \App\Support\StoreDomain::productUrl($store, $product->slug) }}" target="_blank" class="text-blue-400 hover:text-blue-300 transition" title="View Product Page">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        @endif
                                        <a href="{{ route('app.products.landing-builder', $product->id) }}" class="text-purple-400 hover:text-purple-300 transition" title="Edit Landing Page">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('app.products.edit', $product->id) }}" class="text-yellow-400 hover:text-yellow-300 transition" title="Edit Product">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <button onclick="deleteProduct({{ $product->id }}, '{{ addslashes($product->name) }}')" class="text-red-400 hover:text-red-300 transition" title="Delete Product">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-400">
                    Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} products
                </div>
                <div>
                    {{ $products->links() }}
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-[#0f1c2e] border border-white/10 rounded-xl p-16 text-center">
                <svg class="w-24 h-24 text-gray-600 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <h3 class="text-2xl font-bold text-white mb-3">Aucun produit</h3>
                <p class="text-gray-400 mb-8 max-w-md mx-auto">
                    Ajoutez vos produits pour que l'IA puisse les proposer aux clients
                </p>
                <div class="flex items-center justify-center gap-4">
                    <a href="{{ route('app.products.create') }}" class="px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-semibold rounded-lg transition inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Ajouter mon premier produit
                    </a>
                    <button class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        Import en masse
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black/80 z-50 hidden items-center justify-center p-4">
        <div class="bg-[#0f1c2e] border border-white/10 rounded-xl p-6 max-w-md w-full">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-red-500/20 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white">Delete Product</h3>
                    <p class="text-sm text-gray-400">This action cannot be undone</p>
                </div>
            </div>
            <p class="text-gray-300 mb-6">Are you sure you want to delete <span id="deleteProductName" class="font-semibold text-white"></span>? All associated images and data will be permanently removed.</p>
            <div class="flex justify-end gap-3">
                <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition">
                    Cancel
                </button>
                <form id="deleteForm" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                        Delete Product
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function deleteProduct(productId, productName) {
            const modal = document.getElementById('deleteModal');
            const form = document.getElementById('deleteForm');
            const nameSpan = document.getElementById('deleteProductName');
            
            form.action = `/app/products/${productId}`;
            nameSpan.textContent = productName;
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        
        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        
        // Close modal when clicking outside
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });

        function generateLandingPage(productId) {
            const button = event.target.closest('button');
            const originalContent = button.innerHTML;
            
            button.disabled = true;
            button.innerHTML = `
                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span>Starting...</span>
            `;
            
            fetch(`/app/products/${productId}/generate-landing-page`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    button.innerHTML = `
                        <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span>Generating...</span>
                    `;
                    button.className = 'inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-full bg-yellow-600/20 text-yellow-400 border border-yellow-500/30';
                    
                    // Show success notification
                    const notification = document.createElement('div');
                    notification.className = 'fixed top-4 right-4 bg-emerald-500/20 border border-emerald-500/50 text-emerald-400 px-6 py-4 rounded-lg shadow-lg z-50 flex items-center gap-3';
                    notification.innerHTML = `
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <div>
                            <p class="font-semibold">Generation Started</p>
                            <p class="text-sm">${data.message}</p>
                        </div>
                    `;
                    document.body.appendChild(notification);
                    
                    setTimeout(() => {
                        notification.remove();
                    }, 5000);
                    
                    // Refresh the page after 3 seconds to show the pending status
                    setTimeout(() => {
                        location.reload();
                    }, 3000);
                } else {
                    alert('Error: ' + data.message);
                    button.disabled = false;
                    button.innerHTML = originalContent;
                }
            })
            .catch(error => {
                alert('An error occurred while starting the landing page generation.');
                button.disabled = false;
                button.innerHTML = originalContent;
            });
        }

        // Auto-refresh page every 10 seconds if there are pending or processing jobs
        document.addEventListener('DOMContentLoaded', function() {
            const hasPendingJobs = document.querySelector('.bg-yellow-600\\/20');
            if (hasPendingJobs) {
                setTimeout(() => {
                    location.reload();
                }, 10000);
            }
            
            document.querySelectorAll('td[data-product-id]').forEach(cell => {
                const productId = cell.getAttribute('data-product-id');
                const progressBar = cell.querySelector('.image-progress-bar');
                
                if (progressBar) {
                    startProgressTracking(productId);
                }
            });
        });

        let progressIntervals = {};

        function generateProductImages(productId) {
            const button = event.target.closest('button');
            const cell = button.closest('td');
            
            button.disabled = true;
            
            cell.innerHTML = `
                <div class="space-y-1 min-w-[200px]">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-semibold text-yellow-400">Starting...</span>
                        <span class="text-xs text-gray-400 image-progress-text">0/5</span>
                    </div>
                    <div class="w-full bg-gray-700 rounded-full h-2">
                        <div class="bg-gradient-to-r from-yellow-500 to-orange-500 h-2 rounded-full transition-all duration-500 image-progress-bar" style="width: 0%"></div>
                    </div>
                </div>
            `;
            
            fetch(`/app/products/${productId}/generate-images`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    startProgressTracking(productId);
                } else {
                    alert('Error: ' + data.message);
                    location.reload();
                }
            })
            .catch(error => {
                alert('An error occurred while starting image generation.');
                location.reload();
            });
        }

        function startProgressTracking(productId) {
            if (progressIntervals[productId]) {
                clearInterval(progressIntervals[productId]);
            }
            
            progressIntervals[productId] = setInterval(() => {
                fetch(`/app/products/${productId}/image-progress`)
                    .then(response => response.json())
                    .then(data => {
                        const cell = document.querySelector(`td[data-product-id="${productId}"]`);
                        if (!cell) return;
                        
                        const progressBar = cell.querySelector('.image-progress-bar');
                        const progressText = cell.querySelector('.image-progress-text');
                        
                        if (progressBar && progressText) {
                            progressBar.style.width = data.progress + '%';
                            progressText.textContent = `${data.generated}/${data.total}`;
                        }
                        
                        if (data.status === 'completed') {
                            clearInterval(progressIntervals[productId]);
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        } else if (data.status === 'failed') {
                            clearInterval(progressIntervals[productId]);
                            alert('Image generation failed. Please try again.');
                            location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error checking progress:', error);
                    });
            }, 3000);
        }

        function viewGeneratedImages(productId) {
            fetch(`/app/products/${productId}/image-progress`)
                .then(response => response.json())
                .then(data => {
                    if (data.images && data.images.length > 0) {
                        const modal = document.createElement('div');
                        modal.className = 'fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4';
                        modal.onclick = (e) => {
                            if (e.target === modal) modal.remove();
                        };
                        
                        const imagesHtml = data.images.map(img => `
                            <div class="bg-white rounded-lg overflow-hidden">
                                <img src="${img}" alt="Generated product image" class="w-full h-64 object-contain" />
                            </div>
                        `).join('');
                        
                        modal.innerHTML = `
                            <div class="bg-[#0f1c2e] border border-white/10 rounded-xl p-6 max-w-6xl w-full max-h-[90vh] overflow-y-auto">
                                <div class="flex justify-between items-center mb-6">
                                    <h3 class="text-2xl font-bold text-white">AI Generated Images</h3>
                                    <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-white transition">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    ${imagesHtml}
                                </div>
                            </div>
                        `;
                        
                        document.body.appendChild(modal);
                    }
                })
                .catch(error => {
                    console.error('Error loading images:', error);
                });
        }
    </script>
@endsection
