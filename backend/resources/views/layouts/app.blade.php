<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pazarz') - Pazarz Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 flex flex-col">
            <div class="p-6 border-b border-gray-200">
                <a href="{{ route(auth()->user()->isAdmin() ? 'admin.dashboard' : 'seller.dashboard') }}" class="text-xl font-bold tracking-tight">
                    Pazarz
                </a>
                <p class="text-xs text-gray-500 mt-1">{{ auth()->user()->isAdmin() ? 'Admin Dashboard' : 'Seller Dashboard' }}</p>
            </div>

            <nav class="flex-1 p-4 space-y-1">
                @yield('sidebar')
            </nav>

            <div class="p-4 border-t border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center text-sm font-medium">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full text-left text-sm text-gray-600 hover:text-red-600 px-2 py-1 rounded">
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-auto">
            <!-- Top Bar -->
            <header class="bg-white border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-lg font-semibold">@yield('header', 'Dashboard')</h1>
                    @yield('header-actions')
                </div>
            </header>

            <!-- Flash Messages -->
            @if(session('success'))
                <div class="mx-6 mt-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mx-6 mt-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mx-6 mt-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Page Content -->
            <div class="p-6">
                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
