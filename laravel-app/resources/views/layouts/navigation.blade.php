<nav x-data="{ open: false }" class="bg-apple-black-900 border-b border-apple-black-800 backdrop-blur-xl bg-opacity-90">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ Auth::check() ? route('dashboard') : route('bills.index') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-white" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-2 sm:-my-px sm:ms-10 sm:flex">
                    @auth
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard*')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                    @endauth

                    <x-nav-link :href="route('bills.index')" :active="request()->routeIs('bills.*')">
                        {{ __('Bills') }}
                    </x-nav-link>

                    <x-nav-link :href="route('legislators.index')" :active="request()->routeIs('legislators.*')">
                        {{ __('Legislators') }}
                    </x-nav-link>

                    <x-nav-link :href="route('committees.index')" :active="request()->routeIs('committees.*')">
                        {{ __('Committees') }}
                    </x-nav-link>

                    <x-nav-link :href="route('calendar.index')" :active="request()->routeIs('calendar.*')">
                        {{ __('Calendar') }}
                    </x-nav-link>

                    <x-nav-link :href="route('risks.index')" :active="request()->routeIs('risks.*')">
                        {{ __('Risks') }}
                    </x-nav-link>

                    @auth
                        <!-- More dropdown for authenticated users -->
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-xl text-apple-black-300 bg-apple-black-800 hover:text-white hover:bg-apple-black-700 focus:outline-none transition ease-in-out duration-150">
                                    <div>{{ __('My Tools') }}</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('watchlist.index')">
                                    {{ __('My Watchlist') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('tags.index')">
                                    {{ __('Tags') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('notes.index')">
                                    {{ __('Notes') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('searches.index')">
                                    {{ __('Saved Searches') }}
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    @endauth
                </div>
            </div>

            <!-- Right Side Navigation -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                    <!-- Settings Dropdown -->
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-xl text-apple-black-300 bg-apple-black-800 hover:text-white hover:bg-apple-black-700 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('dashboard.customize')">
                                {{ __('Customize Dashboard') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <!-- Guest Links -->
                    <a href="{{ route('login') }}" class="text-sm text-apple-black-300 hover:text-white px-3 py-2 rounded-xl hover:bg-apple-black-800 transition">{{ __('Log in') }}</a>
                    <a href="{{ route('register') }}" class="ml-4 text-sm text-white bg-apple-black-700 hover:bg-apple-black-600 px-4 py-2 rounded-xl transition font-medium">{{ __('Register') }}</a>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-xl text-apple-black-400 hover:text-white hover:bg-apple-black-800 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @auth
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard*')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
            @endauth

            <x-responsive-nav-link :href="route('bills.index')" :active="request()->routeIs('bills.*')">
                {{ __('Bills') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('legislators.index')" :active="request()->routeIs('legislators.*')">
                {{ __('Legislators') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('committees.index')" :active="request()->routeIs('committees.*')">
                {{ __('Committees') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('calendar.index')" :active="request()->routeIs('calendar.*')">
                {{ __('Calendar') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('risks.index')" :active="request()->routeIs('risks.*')">
                {{ __('Risks') }}
            </x-responsive-nav-link>

            @auth
                <!-- Personal Tools Section -->
                <div class="pt-4 pb-1 border-t border-apple-black-800">
                    <div class="px-4 py-2 text-xs font-semibold text-apple-black-500 uppercase tracking-wider">
                        {{ __('My Tools') }}
                    </div>
                    <x-responsive-nav-link :href="route('watchlist.index')" :active="request()->routeIs('watchlist.*')">
                        {{ __('My Watchlist') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('tags.index')" :active="request()->routeIs('tags.*')">
                        {{ __('Tags') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('notes.index')" :active="request()->routeIs('notes.*')">
                        {{ __('Notes') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('searches.index')" :active="request()->routeIs('searches.*')">
                        {{ __('Saved Searches') }}
                    </x-responsive-nav-link>
                </div>
            @endauth
        </div>

        <!-- Responsive Settings Options -->
        @auth
            <div class="pt-4 pb-1 border-t border-apple-black-800">
                <div class="px-4">
                    <div class="font-medium text-base text-white">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-apple-black-400">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('dashboard.customize')">
                        {{ __('Customize Dashboard') }}
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @else
            <!-- Guest Options -->
            <div class="pt-4 pb-1 border-t border-apple-black-800">
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('login')">
                        {{ __('Log in') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('register')">
                        {{ __('Register') }}
                    </x-responsive-nav-link>
                </div>
            </div>
        @endauth
    </div>
</nav>
