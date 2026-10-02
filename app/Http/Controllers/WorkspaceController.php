<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class WorkspaceController extends Controller
{
    use AuthorizesRequests;

    public function dashboard(Request $request)
    {
        $view = $request->get('view', 'overview');
        
        $workspaces = auth()->user()->workspaces()
            ->withCount(['stores', 'stores as products_count' => function ($query) {
                $query->join('products', 'stores.id', '=', 'products.store_id');
            }])
            ->latest()
            ->get();
        
        // Global statistics across ALL workspaces
        $totalStores = auth()->user()->stores()->count();
        $totalProducts = \App\Models\Product::whereIn('store_id', auth()->user()->stores()->pluck('id'))->count();
        $totalOrders = \App\Models\Order::whereIn('store_id', auth()->user()->stores()->pluck('id'))->count();
        $newOrders = \App\Models\Order::whereIn('store_id', auth()->user()->stores()->pluck('id'))
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
        $totalRevenue = \App\Models\Order::whereIn('store_id', auth()->user()->stores()->pluck('id'))
            ->where('status', 'completed')
            ->sum('total');
        $pendingOrders = \App\Models\Order::whereIn('store_id', auth()->user()->stores()->pluck('id'))
            ->where('status', 'pending')
            ->count();
            
        $stats = [
            'total_workspaces' => $workspaces->count(),
            'active_workspaces' => $workspaces->where('is_active', true)->count(),
            'total_stores' => $totalStores,
            'total_products' => $totalProducts,
            'total_orders' => $totalOrders,
            'new_orders' => $newOrders,
            'total_revenue' => $totalRevenue,
            'pending_orders' => $pendingOrders,
        ];
        
        $currentWorkspaceId = session('active_workspace_id');
        
        if ($view === 'list') {
            return view('workspaces.list', compact('workspaces', 'stats', 'currentWorkspaceId'));
        }
        
        return view('workspaces.overview', compact('workspaces', 'stats', 'currentWorkspaceId'));
    }
    
    public function create()
    {
        return view('workspaces.create');
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'language' => 'required|string|in:' . implode(',', array_keys(config('workspace.languages', ['ar' => []]))),
            'currency' => 'required|string|in:' . implode(',', array_keys(config('workspace.currencies', ['MAD' => []]))),
            'is_active' => 'boolean',
        ]);
        
        $validated['user_id'] = auth()->id();
        
        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }
        
        $workspace = Workspace::create($validated);
        
        session(['active_workspace_id' => $workspace->id]);
        session()->forget('active_store_id');
        
        return redirect()->route('workspaces.dashboard')->with('success', 'Workspace created successfully!');
    }
    
    public function edit(Workspace $workspace)
    {
        $this->authorize('update', $workspace);
        
        return view('workspaces.edit', compact('workspace'));
    }
    
    public function update(Request $request, Workspace $workspace)
    {
        $this->authorize('update', $workspace);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'language' => 'required|string|in:' . implode(',', array_keys(config('workspace.languages', ['ar' => []]))),
            'currency' => 'required|string|in:' . implode(',', array_keys(config('workspace.currencies', ['MAD' => []]))),
            'is_active' => 'boolean',
        ]);
        
        $workspace->update($validated);
        
        return redirect()->route('workspaces.dashboard')->with('success', 'Workspace updated successfully!');
    }
    
    public function destroy(Workspace $workspace)
    {
        $this->authorize('delete', $workspace);
        
        $storesCount = $workspace->stores()->count();
        
        if ($storesCount > 0) {
            return redirect()->route('workspaces.dashboard')
                ->with('error', 'Cannot delete workspace with existing stores. Please delete or reassign the stores first.');
        }
        
        if (session('active_workspace_id') == $workspace->id) {
            session()->forget('active_workspace_id');
            session()->forget('active_store_id');
        }
        
        $workspace->delete();
        
        return redirect()->route('workspaces.dashboard')->with('success', 'Workspace deleted successfully!');
    }
    
    public function switch(Workspace $workspace)
    {
        $this->authorize('view', $workspace);
        
        session(['active_workspace_id' => $workspace->id]);
        session()->forget('active_store_id');
        
        return redirect()->route('stores.dashboard')->with('success', 'Switched to workspace: ' . $workspace->name);
    }
}
