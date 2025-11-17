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
        [x-cloak] { display: none !important; }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .badge-critical { @apply bg-red-100 text-red-800 ring-1 ring-red-600/20; }
        .badge-high { @apply bg-orange-100 text-orange-800 ring-1 ring-orange-600/20; }
        .badge-medium { @apply bg-yellow-100 text-yellow-800 ring-1 ring-yellow-600/20; }
        .badge-low { @apply bg-green-100 text-green-800 ring-1 ring-green-600/20; }

        .status-badge { @apply inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset; }
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
                        <div class="ml-10 flex items-baseline space-x-4">
                            <a href="{{ route('dashboard') }}" class="@if(request()->routeIs('dashboard')) bg-indigo-50 text-indigo-700 @else text-gray-700 hover:bg-gray-100 @endif rounded-md px-3 py-2 text-sm font-medium transition-colors">
                                Dashboard
                            </a>
                            <a href="{{ route('bills.index') }}" class="@if(request()->routeIs('bills.*')) bg-indigo-50 text-indigo-700 @else text-gray-700 hover:bg-gray-100 @endif rounded-md px-3 py-2 text-sm font-medium transition-colors">
                                Proiecte de Lege
                            </a>
                            <a href="{{ route('risks.index') }}" class="@if(request()->routeIs('risks.*')) bg-indigo-50 text-indigo-700 @else text-gray-700 hover:bg-gray-100 @endif rounded-md px-3 py-2 text-sm font-medium transition-colors">
                                <span class="flex items-center">
                                    Riscuri
                                    @if(isset($criticalRisks) && $criticalRisks > 0)
                                        <span class="ml-1.5 inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">
                                            {{ $criticalRisks }}
                                        </span>
                                    @endif
                                </span>
                            </a>
                            <a href="#" class="text-gray-700 hover:bg-gray-100 rounded-md px-3 py-2 text-sm font-medium transition-colors">
                                Calendar
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
        <footer class="mt-16 bg-white border-t border-gray-200">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div class="col-span-1 md:col-span-2">
                        <div class="flex items-center mb-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <span class="ml-3 text-lg font-bold text-gray-900">Legislative Watcher</span>
                        </div>
                        <p class="text-sm text-gray-600 max-w-md">
                            Monitorizare automată a procesului legislativ din România. Transparență, analiza AI și alertare în timp real pentru Parlamentul României.
                        </p>
                        <p class="mt-4 text-xs text-gray-500">
                            Ultima actualizare: {{ now()->format('d.m.Y H:i') }}
                        </p>
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
