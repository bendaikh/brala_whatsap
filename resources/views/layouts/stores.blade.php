<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-50">
            <div class="flex h-screen overflow-hidden">
                <!-- Sidebar -->
                <aside class="w-64 bg-white border-r border-gray-200 flex flex-col">
                    <!-- Logo -->
                    <div class="h-16 flex items-center px-6 border-b border-gray-200">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-emerald-500 rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold text-sm">C</span>
                            </div>
                            <span class="text-xl font-bold text-gray-900">ChatEasy</span>
                        </div>
                    </div>

                    <!-- Context Indicator -->
                    @php
                        $activeWorkspaceId = session('active_workspace_id');
                        $activeWorkspace = $activeWorkspaceId ? \App\Models\Workspace::find($activeWorkspaceId) : null;
                    @endphp

                    @if($activeWorkspace && request()->routeIs('stores.*'))
                        <div class="px-3 py-3 border-b border-gray-200">
                            <div class="flex items-center gap-2 p-2.5 rounded-lg bg-blue-50">
                                <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs text-blue-600 font-medium">Active Workspace</p>
                                    <p class="text-sm font-semibold text-blue-900 truncate">{{ $activeWorkspace->name }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Navigation -->
                    <nav class="flex-1 overflow-y-auto py-4 px-3">
                        <div class="space-y-1">
                            @if(request()->routeIs('workspaces.*'))
                                <!-- Workspace Management Navigation -->
                                <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Workspace Management</p>
                                
                                <a href="{{ route('workspaces.dashboard') }}" class="{{ request()->routeIs('workspaces.dashboard') && !request()->has('view') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }} flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                    </svg>
                                    Dashboard
                                </a>

                                <a href="{{ route('workspaces.dashboard', ['view' => 'list']) }}" class="{{ request()->has('view') && request()->get('view') === 'list' ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }} flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    My Workspaces
                                </a>

                                <a href="{{ route('workspaces.create') }}" class="{{ request()->routeIs('workspaces.create') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }} flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Create Workspace
                                </a>

                                <div class="border-t border-gray-200 my-4"></div>

                                <!-- AI API Integration Section (Workspace Level) -->
                                <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">AI Integration</p>
                                
                                <a href="{{ route('workspaces.ai-settings') }}" class="{{ request()->routeIs('workspaces.ai-settings') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }} flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                    </svg>
                                    AI API Settings
                                </a>

                            @elseif(request()->routeIs('stores.*'))
                                <!-- Store Management Navigation -->
                                <a href="{{ route('workspaces.dashboard') }}" class="text-gray-700 hover:bg-gray-50 flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                    </svg>
                                    Back to Workspaces
                                </a>

                                <div class="border-t border-gray-200 my-2"></div>

                                <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Store Management</p>
                                
                                <a href="{{ route('stores.dashboard') }}" class="{{ request()->routeIs('stores.dashboard') && !request()->has('view') ? 'bg-emerald-50 text-emerald-700' : 'text-gray-700 hover:bg-gray-50' }} flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                    </svg>
                                    Dashboard
                                </a>

                                <a href="{{ route('stores.dashboard', ['view' => 'list']) }}" class="{{ request()->has('view') && request()->get('view') === 'list' ? 'bg-emerald-50 text-emerald-700' : 'text-gray-700 hover:bg-gray-50' }} flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    My Stores
                                </a>

                                <a href="{{ route('stores.create') }}" class="{{ request()->routeIs('stores.create') ? 'bg-emerald-50 text-emerald-700' : 'text-gray-700 hover:bg-gray-50' }} flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Create Store
                                </a>

                                <div class="border-t border-gray-200 my-4"></div>

                                <!-- Services Integration (per active workspace) -->
                                <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Services Integration</p>

                                <a href="{{ route('stores.services-integration') }}" class="{{ request()->routeIs('stores.services-integration*') ? 'bg-emerald-50 text-emerald-700' : 'text-gray-700 hover:bg-gray-50' }} flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                    </svg>
                                    Service Companies
                                </a>

                                <a href="{{ route('stores.google-sheets') }}" class="{{ request()->routeIs('stores.google-sheets*') ? 'bg-emerald-50 text-emerald-700' : 'text-gray-700 hover:bg-gray-50' }} flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Google Sheets
                                </a>
                            @endif

                            <div class="border-t border-gray-200 my-4"></div>

                            <!-- Settings -->
                            <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Settings</p>

                            <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.edit') ? 'bg-emerald-50 text-emerald-700' : 'text-gray-700 hover:bg-gray-50' }} flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Profile
                            </a>
                        </div>
                    </nav>

                    <!-- User Section -->
                    <div class="border-t border-gray-200 p-4">
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-3 w-full p-2 rounded-lg hover:bg-gray-50 transition">
                                <div class="w-8 h-8 bg-emerald-500 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="text-white font-semibold text-sm">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                </div>
                                <div class="flex-1 text-left min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                                </div>
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div x-show="open" @click.away="open = false" x-cloak class="absolute bottom-full left-0 right-0 mb-2 bg-white rounded-lg shadow-lg py-1 border border-gray-200">
                                @if(auth()->user()->role === 'superadmin')
                                    <a href="{{ route('superadmin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                                        Super Admin Panel
                                    </a>
                                    <div class="border-t border-gray-200 my-1"></div>
                                @endif
                                <a href="{{ route('workspaces.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                                    My Workspaces
                                </a>
                                @if(session('active_workspace_id'))
                                    <a href="{{ route('stores.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                                        My Stores
                                    </a>
                                @endif
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                                    Profile Settings
                                </a>
                                <div class="border-t border-gray-200 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                                        Log Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </aside>

                <!-- Main Content -->
                <div class="flex-1 flex flex-col overflow-hidden">
                    <!-- Top Header -->
                    <header class="bg-white border-b border-gray-200 h-16">
                        <div class="h-full px-6 flex items-center justify-between">
                            <div>
                                @isset($header)
                                    {{ $header }}
                                @endisset
                            </div>
                            
                            <div class="flex items-center gap-4">
                                @if(auth()->user()->role === 'superadmin')
                                    <span class="px-3 py-1 bg-purple-100 text-purple-700 text-xs font-semibold rounded-full">
                                        Super Admin
                                    </span>
                                @endif
                            </div>
                        </div>
                    </header>

                    <!-- Page Content -->
                    <main class="flex-1 overflow-y-auto bg-gray-50">
                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>

        <!-- Alpine.js -->
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <style>
            [x-cloak] { display: none !important; }
        </style>
    </body>
</html>
