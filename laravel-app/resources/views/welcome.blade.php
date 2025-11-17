<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Romanian Legislative Watcher') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="antialiased">
    <div class="relative min-h-screen bg-gradient-to-br from-gray-50 via-white to-blue-50">
        <!-- Navigation Bar -->
        <nav class="absolute top-0 w-full z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-gray-900">
                            🏛️ Legislative Watcher
                        </h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        @auth
                            <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-gray-900 px-3 py-2">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-gray-700 hover:text-gray-900 px-3 py-2">Log in</a>
                            <a href="{{ route('register') }}" class="ml-4 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Register</a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="relative isolate px-6 pt-14 lg:px-8">
            <div class="mx-auto max-w-3xl py-32 sm:py-48 lg:py-56">
                <div class="text-center">
                    <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-6xl">
                        Monitor Romanian Legislative Activity
                    </h1>
                    <p class="mt-6 text-lg leading-8 text-gray-600">
                        Track bills, analyze risks, and stay informed about legislative changes in the Romanian Parliament (Chamber of Deputies and Senate).
                    </p>
                    <div class="mt-10 flex items-center justify-center gap-x-6">
                        <a href="{{ route('bills.index') }}" class="rounded-md bg-indigo-600 px-6 py-3 text-base font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                            Browse Bills
                        </a>
                        <a href="{{ route('legislators.index') }}" class="text-base font-semibold leading-6 text-gray-900">
                            View Legislators <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Grid -->
        <div class="mx-auto max-w-7xl px-6 lg:px-8 pb-24">
            <div class="mx-auto max-w-2xl lg:text-center mb-16">
                <h2 class="text-base font-semibold leading-7 text-indigo-600">Transparency</h2>
                <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                    Everything you need to track legislation
                </p>
            </div>

            <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-none">
                <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-16 lg:max-w-none lg:grid-cols-3">
                    <!-- Feature 1 -->
                    <div class="flex flex-col bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <dt class="flex items-center gap-x-3 text-base font-semibold leading-7 text-gray-900">
                            <svg class="h-5 w-5 flex-none text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Track All Bills
                        </dt>
                        <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-gray-600">
                            <p class="flex-auto">Monitor legislative projects from both the Chamber of Deputies and Senate with comprehensive search and filtering.</p>
                        </dd>
                    </div>

                    <!-- Feature 2 -->
                    <div class="flex flex-col bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <dt class="flex items-center gap-x-3 text-base font-semibold leading-7 text-gray-900">
                            <svg class="h-5 w-5 flex-none text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            AI Risk Analysis
                        </dt>
                        <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-gray-600">
                            <p class="flex-auto">Automatically detect potential risks including privacy violations, business impacts, and constitutional concerns.</p>
                        </dd>
                    </div>

                    <!-- Feature 3 -->
                    <div class="flex flex-col bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <dt class="flex items-center gap-x-3 text-base font-semibold leading-7 text-gray-900">
                            <svg class="h-5 w-5 flex-none text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            Email Alerts
                        </dt>
                        <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-gray-600">
                            <p class="flex-auto">Get notified about important legislative changes, new bills, and deadlines with customizable email subscriptions.</p>
                        </dd>
                    </div>

                    <!-- Feature 4 -->
                    <div class="flex flex-col bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <dt class="flex items-center gap-x-3 text-base font-semibold leading-7 text-gray-900">
                            <svg class="h-5 w-5 flex-none text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Legislator Tracking
                        </dt>
                        <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-gray-600">
                            <p class="flex-auto">View profiles, voting records, and performance metrics for all MPs and Senators.</p>
                        </dd>
                    </div>

                    <!-- Feature 5 -->
                    <div class="flex flex-col bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <dt class="flex items-center gap-x-3 text-base font-semibold leading-7 text-gray-900">
                            <svg class="h-5 w-5 flex-none text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Legislative Calendar
                        </dt>
                        <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-gray-600">
                            <p class="flex-auto">Stay informed about upcoming sessions, deadlines, and important legislative events.</p>
                        </dd>
                    </div>

                    <!-- Feature 6 -->
                    <div class="flex flex-col bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <dt class="flex items-center gap-x-3 text-base font-semibold leading-7 text-gray-900">
                            <svg class="h-5 w-5 flex-none text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                            </svg>
                            Personal Tools
                        </dt>
                        <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-gray-600">
                            <p class="flex-auto">Create watchlists, add custom tags, write notes, and save frequent searches for quick access.</p>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="bg-indigo-700 py-16">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                        Start monitoring today
                    </h2>
                    <p class="mx-auto mt-6 max-w-xl text-lg leading-8 text-indigo-100">
                        Join thousands of engaged citizens, journalists, activists, and legal professionals tracking Romanian legislation.
                    </p>
                    <div class="mt-10 flex items-center justify-center gap-x-6">
                        @guest
                            <a href="{{ route('register') }}" class="rounded-md bg-white px-6 py-3 text-base font-semibold text-indigo-600 shadow-sm hover:bg-indigo-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                                Create Account
                            </a>
                        @endguest
                        <a href="{{ route('bills.index') }}" class="text-base font-semibold leading-6 text-white">
                            Browse without account <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-white mt-16">
            <div class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
                <div class="text-center text-sm text-gray-500">
                    <p>&copy; {{ date('Y') }} Romanian Legislative Watcher. Promoting transparency and civic engagement.</p>
                    <div class="mt-4 space-x-4">
                        <a href="{{ route('bills.index') }}" class="hover:text-gray-900">Bills</a>
                        <a href="{{ route('legislators.index') }}" class="hover:text-gray-900">Legislators</a>
                        <a href="{{ route('committees.index') }}" class="hover:text-gray-900">Committees</a>
                        <a href="{{ route('calendar.index') }}" class="hover:text-gray-900">Calendar</a>
                        <a href="{{ route('risks.index') }}" class="hover:text-gray-900">Risks</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
