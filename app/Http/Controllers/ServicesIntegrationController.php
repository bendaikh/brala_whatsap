<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\WorkspaceServiceIntegration;
use App\Services\ServiceCompanyClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;

class ServicesIntegrationController extends Controller
{
    public function index()
    {
        $activeWorkspace = $this->activeWorkspace();

        $integrations = WorkspaceServiceIntegration::query()
            ->where('workspace_id', $activeWorkspace->id)
            ->where('user_id', auth()->id())
            ->with(['stores' => function ($query) {
                $query->select('id', 'name', 'service_integration_id', 'workspace_id');
            }])
            ->latest()
            ->get();

        $types = WorkspaceServiceIntegration::TYPES;
        $providers = WorkspaceServiceIntegration::PROVIDERS;
        $workspaceStores = \App\Models\Store::query()
            ->where('workspace_id', $activeWorkspace->id)
            ->where('user_id', auth()->id())
            ->orderBy('name')
            ->get(['id', 'name', 'service_integration_id']);

        return view('stores.services-integration', compact(
            'activeWorkspace',
            'integrations',
            'types',
            'providers',
            'workspaceStores'
        ));
    }

    public function store(Request $request)
    {
        $activeWorkspace = $this->activeWorkspace();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(WorkspaceServiceIntegration::TYPES))],
            'provider' => ['required', Rule::in(array_keys(WorkspaceServiceIntegration::PROVIDERS))],
            'api_url' => ['nullable', 'url', 'max:500'],
            'api_key' => ['nullable', 'string', 'max:2048'],
            'is_enabled' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'default_product_ref' => ['nullable', 'string', 'max:255'],
            'default_country' => ['nullable', 'string', 'max:10'],
        ]);

        $provider = $validated['provider'];

        $integration = new WorkspaceServiceIntegration([
            'workspace_id' => $activeWorkspace->id,
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'type' => $validated['type'],
            'provider' => $provider,
            'api_url' => $this->normalizeApiUrl($validated['api_url'] ?? null, $provider),
            'is_enabled' => $request->boolean('is_enabled'),
            'notes' => $validated['notes'] ?? null,
            'settings' => $this->buildSettings($validated, $provider),
        ]);

        if (!empty($validated['api_key'])) {
            $integration->api_key_encrypted = Crypt::encryptString(trim($validated['api_key']));
        }

        $integration->save();

        return redirect()
            ->route('stores.services-integration')
            ->with('success', 'Service company "' . $integration->name . '" added successfully.');
    }

    public function edit(WorkspaceServiceIntegration $integration)
    {
        $this->authorizeIntegration($integration);
        $activeWorkspace = $this->activeWorkspace();
        $types = WorkspaceServiceIntegration::TYPES;
        $providers = WorkspaceServiceIntegration::PROVIDERS;

        return view('stores.services-integration-edit', compact('activeWorkspace', 'integration', 'types', 'providers'));
    }

    public function update(Request $request, WorkspaceServiceIntegration $integration)
    {
        $this->authorizeIntegration($integration);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(WorkspaceServiceIntegration::TYPES))],
            'provider' => ['required', Rule::in(array_keys(WorkspaceServiceIntegration::PROVIDERS))],
            'api_url' => ['nullable', 'url', 'max:500'],
            'api_key' => ['nullable', 'string', 'max:2048'],
            'is_enabled' => ['nullable', 'boolean'],
            'clear_api_key' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'default_product_ref' => ['nullable', 'string', 'max:255'],
            'default_country' => ['nullable', 'string', 'max:10'],
        ]);

        $provider = $validated['provider'];

        $integration->name = $validated['name'];
        $integration->type = $validated['type'];
        $integration->provider = $provider;
        $integration->api_url = $this->normalizeApiUrl($validated['api_url'] ?? null, $provider);
        $integration->is_enabled = $request->boolean('is_enabled');
        $integration->notes = $validated['notes'] ?? null;
        $integration->settings = $this->buildSettings($validated, $provider, $integration->settings ?? []);

        if ($request->boolean('clear_api_key')) {
            $integration->api_key_encrypted = null;
        } elseif (!empty($validated['api_key'])) {
            $integration->api_key_encrypted = Crypt::encryptString(trim($validated['api_key']));
        }

        $integration->save();

        return redirect()
            ->route('stores.services-integration')
            ->with('success', 'Service company "' . $integration->name . '" updated successfully.');
    }

    public function destroy(WorkspaceServiceIntegration $integration)
    {
        $this->authorizeIntegration($integration);

        $name = $integration->name;
        $integration->delete();

        return redirect()
            ->route('stores.services-integration')
            ->with('success', 'Service company "' . $name . '" removed.');
    }

    public function test(WorkspaceServiceIntegration $integration)
    {
        $this->authorizeIntegration($integration);

        if (!$integration->api_url || !$integration->hasApiKey()) {
            return redirect()
                ->route('stores.services-integration')
                ->with('error', 'Add an API URL and API key before testing the connection.');
        }

        $wasEnabled = $integration->is_enabled;
        $integration->is_enabled = true;
        $result = ServiceCompanyClient::fromIntegration($integration)->testConnection();
        $integration->is_enabled = $wasEnabled;

        if ($result['success']) {
            return redirect()
                ->route('stores.services-integration')
                ->with('success', 'Connection to "' . $integration->name . '" successful.');
        }

        return redirect()
            ->route('stores.services-integration')
            ->with('error', 'Connection failed for "' . $integration->name . '": ' . $result['message']);
    }

    public function assignStore(Request $request, WorkspaceServiceIntegration $integration)
    {
        $this->authorizeIntegration($integration);
        $activeWorkspace = $this->activeWorkspace();

        $validated = $request->validate([
            'store_id' => [
                'required',
                'integer',
                Rule::exists('stores', 'id')
                    ->where('workspace_id', $activeWorkspace->id)
                    ->where('user_id', auth()->id()),
            ],
        ]);

        $store = \App\Models\Store::query()
            ->where('id', $validated['store_id'])
            ->where('workspace_id', $activeWorkspace->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $store->service_integration_id = $integration->id;
        $store->save();

        return redirect()
            ->route('stores.services-integration')
            ->with('success', '"' . $store->name . '" is now affected to ' . $integration->name . '.');
    }

    public function unassignStore(Request $request, WorkspaceServiceIntegration $integration)
    {
        $this->authorizeIntegration($integration);
        $activeWorkspace = $this->activeWorkspace();

        $validated = $request->validate([
            'store_id' => [
                'required',
                'integer',
                Rule::exists('stores', 'id')
                    ->where('workspace_id', $activeWorkspace->id)
                    ->where('user_id', auth()->id())
                    ->where('service_integration_id', $integration->id),
            ],
        ]);

        $store = \App\Models\Store::query()
            ->where('id', $validated['store_id'])
            ->where('workspace_id', $activeWorkspace->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $store->service_integration_id = null;
        $store->save();

        return redirect()
            ->route('stores.services-integration')
            ->with('success', '"' . $store->name . '" was unassigned from ' . $integration->name . '.');
    }

    protected function activeWorkspace(): Workspace
    {
        $workspace = Workspace::query()
            ->where('id', session('active_workspace_id'))
            ->where('user_id', auth()->id())
            ->first();

        if (!$workspace) {
            abort(403, 'No active workspace selected.');
        }

        return $workspace;
    }

    protected function authorizeIntegration(WorkspaceServiceIntegration $integration): void
    {
        $activeWorkspace = $this->activeWorkspace();

        if (
            (int) $integration->workspace_id !== (int) $activeWorkspace->id
            || (int) $integration->user_id !== (int) auth()->id()
        ) {
            abort(403);
        }
    }

    protected function normalizeApiUrl(?string $url, string $provider = 'custom'): ?string
    {
        if (empty($url)) {
            return null;
        }

        $url = rtrim($url, '/');

        if ($provider === WorkspaceServiceIntegration::PROVIDER_SURESHIP) {
            if (!preg_match('#/api/v1$#i', $url)) {
                $url = preg_replace('#/api/?$#i', '', $url);
                $url .= '/api/v1';
            }

            return $url;
        }

        return preg_replace('#/api/?$#i', '', $url);
    }

    protected function buildSettings(array $validated, string $provider, array $existing = []): array
    {
        $settings = $existing;

        if ($provider === WorkspaceServiceIntegration::PROVIDER_SURESHIP) {
            $settings['default_product_ref'] = $validated['default_product_ref'] ?? null;
            $settings['default_country'] = strtoupper($validated['default_country'] ?? 'CI');
        }

        return $settings;
    }
}
