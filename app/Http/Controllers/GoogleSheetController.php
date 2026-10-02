<?php

namespace App\Http\Controllers;

use App\Models\GoogleSheetConnection;
use App\Models\Workspace;
use App\Services\GoogleSheetService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GoogleSheetController extends Controller
{
    public function index()
    {
        $activeWorkspace = $this->activeWorkspace();

        $connections = GoogleSheetConnection::query()
            ->where('workspace_id', $activeWorkspace->id)
            ->where('user_id', auth()->id())
            ->withCount('products')
            ->latest()
            ->get();

        return view('stores.google-sheets', compact('activeWorkspace', 'connections'));
    }

    public function store(Request $request)
    {
        $activeWorkspace = $this->activeWorkspace();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'spreadsheet_url' => ['nullable', 'string', 'max:1000'],
            'sheet_tab' => ['nullable', 'string', 'max:100'],
            'webhook_url' => ['required', 'url', 'max:1000'],
            'is_enabled' => ['nullable', 'boolean'],
        ]);

        $spreadsheetId = GoogleSheetConnection::extractSpreadsheetId($validated['spreadsheet_url'] ?? null);

        GoogleSheetConnection::create([
            'workspace_id' => $activeWorkspace->id,
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'spreadsheet_url' => $validated['spreadsheet_url'] ?? null,
            'spreadsheet_id' => $spreadsheetId,
            'sheet_tab' => $validated['sheet_tab'] ?: 'Sheet1',
            'webhook_url' => trim($validated['webhook_url']),
            'is_enabled' => $request->boolean('is_enabled', true),
        ]);

        return redirect()
            ->route('stores.google-sheets')
            ->with('success', 'Google Sheet connected successfully.');
    }

    public function edit(GoogleSheetConnection $connection)
    {
        $this->authorizeConnection($connection);
        $activeWorkspace = $this->activeWorkspace();

        return view('stores.google-sheets-edit', compact('activeWorkspace', 'connection'));
    }

    public function update(Request $request, GoogleSheetConnection $connection)
    {
        $this->authorizeConnection($connection);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'spreadsheet_url' => ['nullable', 'string', 'max:1000'],
            'sheet_tab' => ['nullable', 'string', 'max:100'],
            'webhook_url' => ['required', 'url', 'max:1000'],
            'is_enabled' => ['nullable', 'boolean'],
        ]);

        $connection->update([
            'name' => $validated['name'],
            'spreadsheet_url' => $validated['spreadsheet_url'] ?? null,
            'spreadsheet_id' => GoogleSheetConnection::extractSpreadsheetId($validated['spreadsheet_url'] ?? null),
            'sheet_tab' => $validated['sheet_tab'] ?: 'Sheet1',
            'webhook_url' => trim($validated['webhook_url']),
            'is_enabled' => $request->boolean('is_enabled'),
        ]);

        return redirect()
            ->route('stores.google-sheets')
            ->with('success', 'Google Sheet connection updated.');
    }

    public function destroy(GoogleSheetConnection $connection)
    {
        $this->authorizeConnection($connection);

        $name = $connection->name;
        $connection->products()->update(['google_sheet_connection_id' => null]);
        $connection->delete();

        return redirect()
            ->route('stores.google-sheets')
            ->with('success', 'Google Sheet "' . $name . '" disconnected.');
    }

    public function test(GoogleSheetConnection $connection, GoogleSheetService $service)
    {
        $this->authorizeConnection($connection);

        $wasEnabled = $connection->is_enabled;
        $connection->is_enabled = true;
        $result = $service->testConnection($connection);
        $connection->is_enabled = $wasEnabled;

        if ($result['success']) {
            return redirect()
                ->route('stores.google-sheets')
                ->with('success', 'Test row sent to "' . $connection->name . '" successfully. Check your sheet.');
        }

        return redirect()
            ->route('stores.google-sheets')
            ->with('error', 'Test failed for "' . $connection->name . '": ' . $result['message']);
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

    protected function authorizeConnection(GoogleSheetConnection $connection): void
    {
        $activeWorkspace = $this->activeWorkspace();

        if (
            (int) $connection->workspace_id !== (int) $activeWorkspace->id
            || (int) $connection->user_id !== (int) auth()->id()
        ) {
            abort(403);
        }
    }
}
