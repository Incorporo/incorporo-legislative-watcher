<!DOCTYPE html>
<html lang="ro" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Legislative Watcher') - Monitorizare Parlament România</title>

    <!-- Tailwind CSS CDN (for development - compile in production) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom Styles -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }

        [x-cloak] { display: none !important; }

        :root {
            --primary-50: #eef2ff;
            --primary-100: #e0e7ff;
            --primary-500: #6366f1;
            --primary-600: #4f46e5;
            --primary-700: #4338ca;
            --primary-900: #312e81;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }

        .gradient-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.3;
        }

        .glass {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(12px) saturate(180%);
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }

        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(0, 0, 0, 0.06);
        }

        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -4px rgba(99, 102, 241, 0.12), 0 8px 16px -4px rgba(0, 0, 0, 0.08);
            border-color: rgba(99, 102, 241, 0.2);
        }

        .badge-critical {
            @apply bg-red-50 text-red-700 ring-1 ring-red-600/20 font-semibold;
            text-shadow: 0 1px 0 rgba(255,255,255,0.5);
        }
        .badge-high {
            @apply bg-orange-50 text-orange-700 ring-1 ring-orange-600/20 font-semibold;
            text-shadow: 0 1px 0 rgba(255,255,255,0.5);
        }
        .badge-medium {
            @apply bg-amber-50 text-amber-700 ring-1 ring-amber-600/20 font-semibold;
            text-shadow: 0 1px 0 rgba(255,255,255,0.5);
        }
        .badge-low {
            @apply bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20 font-semibold;
            text-shadow: 0 1px 0 rgba(255,255,255,0.5);
        }

        .status-badge {
            @apply inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold ring-1 ring-inset;
            letter-spacing: 0.01em;
        }

        .nav-link {
            @apply relative rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200;
        }

        .nav-link:not(.active):hover {
            @apply bg-gray-50;
        }

        .nav-link.active {
            @apply bg-indigo-50 text-indigo-700;
            box-shadow: inset 0 1px 2px 0 rgba(99, 102, 241, 0.1);
        }

        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 70%;
            background: linear-gradient(180deg, #6366f1, #4f46e5);
            border-radius: 0 2px 2px 0;
        }

        .stat-card {
            @apply bg-white rounded-xl p-6 border;
            border-color: rgba(0, 0, 0, 0.06);
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px -4px rgba(0, 0, 0, 0.1);
            border-color: rgba(99, 102, 241, 0.15);
        }

        .btn-primary {
            @apply inline-flex items-center px-4 py-2.5 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            transition: all 0.2s;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px -4px rgba(99, 102, 241, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        /* Smooth page transitions */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        main { animation: fadeIn 0.4s ease-out; }

        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 10px; height: 10px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 5px; }
        ::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
    </style>

    @stack('styles')
</head>
<body class="h-full" x-data="{ mobileMenuOpen: false, searchOpen: false }">
    <div class="min-h-full">
        <!-- Navigation -->
        <nav class="glass sticky top-0 z-50 shadow-sm">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <a href="{{ route('dashboard') }}" class="flex items-center">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <span class="ml-3 text-xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                                Legislative Watcher
                            </span>
                        </a>
                    </div>

                    <!-- Desktop Navigation -->
                    <div class="hidden md:block">
                        <div class="ml-10 flex items-baseline space-x-1">
                            <a href="{{ route('dashboard') }}" class="nav-link @if(request()->routeIs('dashboard')) active @else text-gray-700 @endif">
                                <span class="flex items-center">
                                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                    </svg>
                                    Dashboard
                                </span>
                            </a>
                            <a href="{{ route('bills.index') }}" class="nav-link @if(request()->routeIs('bills.*')) active @else text-gray-700 @endif">
                                <span class="flex items-center">
                                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Proiecte
                                </span>
                            </a>
                            <a href="{{ route('risks.index') }}" class="nav-link @if(request()->routeIs('risks.*')) active @else text-gray-700 @endif">
                                <span class="flex items-center">
                                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    Riscuri
                                    @if(isset($criticalRisks) && $criticalRisks > 0)
                                        <span class="ml-1.5 inline-flex items-center justify-center rounded-full bg-red-500 px-1.5 py-0.5 text-[10px] font-bold text-white shadow-sm ring-2 ring-white">
                                            {{ $criticalRisks }}
                                        </span>
                                    @endif
                                </span>
                            </a>
                            <a href="#" class="nav-link text-gray-700">
                                <span class="flex items-center">
                                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Calendar
                                </span>
                            </a>
                        </div>
                    </div>

                    <!-- Search & Actions -->
                    <div class="hidden md:flex items-center space-x-4">
                        <!-- Search Button -->
                        <button @click="searchOpen = true" class="text-gray-500 hover:text-gray-700 transition-colors">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>

                        <!-- Notifications -->
                        <button class="text-gray-500 hover:text-gray-700 relative transition-colors">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span class="absolute -top-1 -right-1 h-4 w-4 rounded-full bg-red-500 text-xs text-white flex items-center justify-center">3</span>
                        </button>
                    </div>

                    <!-- Mobile menu button -->
                    <div class="md:hidden">
                        <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-700 hover:bg-gray-100 rounded-md p-2">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div x-show="mobileMenuOpen" x-cloak class="md:hidden border-t border-gray-200">
                <div class="space-y-1 px-2 pb-3 pt-2 sm:px-3">
                    <a href="{{ route('dashboard') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-700 hover:bg-gray-100">Dashboard</a>
                    <a href="{{ route('bills.index') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-700 hover:bg-gray-100">Proiecte de Lege</a>
                    <a href="{{ route('risks.index') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-700 hover:bg-gray-100">Riscuri</a>
                    <a href="#" class="block rounded-md px-3 py-2 text-base font-medium text-gray-700 hover:bg-gray-100">Calendar</a>
                </div>
            </div>
        </nav>

        <!-- Search Modal -->
        <div x-show="searchOpen" x-cloak class="relative z-50" x-transition>
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="searchOpen = false"></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-start justify-center p-4 text-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl" @click.away="searchOpen = false">
                        <div class="p-6">
                            <input type="text" placeholder="Caută proiecte de lege..." class="w-full rounded-lg border-gray-300 px-4 py-3 text-lg focus:border-indigo-500 focus:ring-indigo-500" autofocus>
                            <div class="mt-4 text-sm text-gray-500">
                                Tastează pentru a căuta...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Header (optional) -->
        @if(isset($header))
        <header class="bg-white shadow-sm">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
        @endif

        <!-- Main Content -->
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Flash Messages -->
            @if(session('success'))
            <div class="mb-6 rounded-lg bg-green-50 p-4 border border-green-200">
                <div class="flex">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <p class="ml-3 text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 rounded-lg bg-red-50 p-4 border border-red-200">
                <div class="flex">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <p class="ml-3 text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="mt-20 bg-gradient-to-br from-gray-50 to-white border-t border-gray-200">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div class="col-span-1 md:col-span-2">
                        <div class="flex items-center mb-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg shadow-indigo-500/30">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <span class="ml-3 text-lg font-bold text-gray-900">Legislative Watcher</span>
                        </div>
                        <p class="text-sm text-gray-600 max-w-md leading-relaxed">
                            Sistem modern de monitorizare automată a procesului legislativ din România. Transparență, analiză AI și alertare în timp real.
                        </p>
                        <div class="mt-4 inline-flex items-center px-3 py-1.5 rounded-lg bg-green-50 border border-green-200">
                            <span class="relative flex h-2 w-2 mr-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                            </span>
                            <span class="text-xs font-semibold text-green-700">Actualizat {{ now()->format('d.m.Y H:i') }}</span>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Navigare</h3>
                        <ul class="space-y-2 text-sm">
                            <li><a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-indigo-600 transition-colors">Dashboard</a></li>
                            <li><a href="{{ route('bills.index') }}" class="text-gray-600 hover:text-indigo-600 transition-colors">Proiecte de Lege</a></li>
                            <li><a href="{{ route('risks.index') }}" class="text-gray-600 hover:text-indigo-600 transition-colors">Riscuri</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors">API</a></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Informații</h3>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors">Despre</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors">Cum funcționează</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors">Contact</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors">GitHub</a></li>
                        </ul>
                    </div>
                </div>

                <div class="mt-8 border-t border-gray-200 pt-8 flex flex-col md:flex-row justify-between items-center">
                    <p class="text-sm text-gray-500">
                        © {{ date('Y') }} Legislative Watcher. Open Source Project.
                    </p>
                    <div class="flex space-x-6 mt-4 md:mt-0">
                        <a href="#" class="text-gray-400 hover:text-gray-500">
                            <span class="sr-only">GitHub</span>
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
