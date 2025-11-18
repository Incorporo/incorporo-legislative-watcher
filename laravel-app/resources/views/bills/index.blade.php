@extends('layouts.app')

@section('title', 'Proiecte de Lege')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div>
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">Proiecte de Lege</h1>
            <p class="text-base text-apple-black-400 font-medium">Monitorizare completă a procesului legislativ român</p>
        </div>
        <div class="mt-6 md:mt-0 flex items-center space-x-3">
            <a href="{{ route('bills.export.csv', request()->all()) }}" class="inline-flex items-center px-4 py-2.5 bg-white text-black rounded-xl text-sm font-semibold hover:bg-apple-black-100 transition-all">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export CSV
            </a>
            <a href="{{ route('bills.index') }}" class="inline-flex items-center px-4 py-2.5 border border-apple-black-700 rounded-xl text-sm font-semibold text-apple-black-300 bg-apple-black-800 hover:bg-apple-black-700 hover:text-white transition-all">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Resetează Filtre
            </a>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('bills.index') }}" class="bg-apple-black-900 rounded-2xl p-6 border border-apple-black-800 mb-6">
        <div class="flex items-center mb-5 pb-4 border-b border-apple-black-800">
            <div class="h-9 w-9 rounded-xl bg-white flex items-center justify-center mr-3">
                <svg class="h-5 w-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-white">Filtre Avansate</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label for="search" class="block text-sm font-medium text-apple-black-300 mb-1">Caută</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-apple-black-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           class="block w-full pl-10 pr-3 py-2 bg-apple-black-800 border border-apple-black-700 text-white rounded-xl focus:ring-white focus:border-white placeholder-apple-black-500"
                           placeholder="Titlu, număr, descriere...">
                </div>
            </div>

            <!-- Chamber -->
            <div>
                <label for="chamber" class="block text-sm font-medium text-apple-black-300 mb-1">Cameră</label>
                <select name="chamber" id="chamber" class="block w-full bg-apple-black-800 border-apple-black-700 text-white rounded-xl focus:ring-white focus:border-white">
                    <option value="">Toate</option>
                    <option value="cdep" {{ request('chamber') == 'cdep' ? 'selected' : '' }}>Camera Deputaților</option>
                    <option value="senate" {{ request('chamber') == 'senate' ? 'selected' : '' }}>Senat</option>
                </select>
            </div>

            <!-- Status -->
            <div>
                <label for="status" class="block text-sm font-medium text-apple-black-300 mb-1">Status</label>
                <select name="status" id="status" class="block w-full bg-apple-black-800 border-apple-black-700 text-white rounded-xl focus:ring-white focus:border-white">
                    <option value="">Toate</option>
                    @foreach($statuses as $status)
                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Year -->
            <div>
                <label for="year" class="block text-sm font-medium text-apple-black-300 mb-1">An</label>
                <select name="year" id="year" class="block w-full bg-apple-black-800 border-apple-black-700 text-white rounded-xl focus:ring-white focus:border-white">
                    <option value="">Toate</option>
                    @foreach($years as $year)
                    <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-5 flex flex-wrap items-center gap-3 pt-4 border-t border-apple-black-800">
            <!-- Quick Filters -->
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-white text-black rounded-xl text-sm font-semibold hover:bg-apple-black-100 transition-all">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Aplică Filtre
            </button>

            <label class="inline-flex items-center">
                <input type="checkbox" name="urgent" value="1" {{ request('urgent') ? 'checked' : '' }}
                       class="rounded border-apple-black-700 bg-apple-black-800 text-white focus:ring-white focus:ring-offset-black">
                <span class="ml-2 text-sm text-apple-black-300">Doar urgențe</span>
            </label>

            <div>
                <label for="risk" class="sr-only">Nivel risc</label>
                <select name="risk" id="risk" class="bg-apple-black-800 border-apple-black-700 text-white rounded-xl text-sm focus:ring-white focus:border-white">
                    <option value="">Toate riscurile</option>
                    <option value="critical" {{ request('risk') == 'critical' ? 'selected' : '' }}>🔴 Critic</option>
                    <option value="high" {{ request('risk') == 'high' ? 'selected' : '' }}>🟠 Ridicat</option>
                    <option value="medium" {{ request('risk') == 'medium' ? 'selected' : '' }}>🟡 Mediu</option>
                    <option value="low" {{ request('risk') == 'low' ? 'selected' : '' }}>🟢 Scăzut</option>
                </select>
            </div>
        </div>
    </form>

    <!-- Results Count -->
    <div class="flex items-center justify-between bg-apple-black-900 px-6 py-4 rounded-2xl border border-apple-black-800">
        <div class="flex items-center space-x-2 text-sm text-apple-black-300 font-medium">
            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <span>
                Afișare <span class="font-bold text-white">{{ $bills->firstItem() ?? 0 }}</span> -
                <span class="font-bold text-white">{{ $bills->lastItem() ?? 0 }}</span> din
                <span class="font-bold text-white">{{ $bills->total() }}</span> proiecte
            </span>
        </div>
        <div class="flex items-center space-x-3">
            <label for="sort-select" class="text-sm font-semibold text-apple-black-300">Sortează:</label>
            <select id="sort-select" onchange="window.location.href = this.value" class="bg-apple-black-800 border-apple-black-700 text-white rounded-xl text-sm font-semibold focus:ring-2 focus:ring-white focus:border-white">
                <option value="{{ route('bills.index', array_merge(request()->except('sort', 'order'), ['sort' => 'registration_date', 'order' => 'desc'])) }}" {{ request('sort') == 'registration_date' && request('order') == 'desc' ? 'selected' : '' }}>
                    📅 Cele mai recente
                </option>
                <option value="{{ route('bills.index', array_merge(request()->except('sort', 'order'), ['sort' => 'registration_date', 'order' => 'asc'])) }}" {{ request('sort') == 'registration_date' && request('order') == 'asc' ? 'selected' : '' }}>
                    📅 Cele mai vechi
                </option>
                <option value="{{ route('bills.index', array_merge(request()->except('sort', 'order'), ['sort' => 'title', 'order' => 'asc'])) }}" {{ request('sort') == 'title' ? 'selected' : '' }}>
                    🔤 Alfabetic
                </option>
            </select>
        </div>
    </div>

    <!-- Bills List -->
    <div class="space-y-4">
        @forelse($bills as $bill)
        <div class="bg-apple-black-900 rounded-2xl border border-apple-black-800 p-6 hover:border-apple-black-700 transition-all duration-300 group">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <!-- Header -->
                    <div class="flex items-center flex-wrap gap-2 mb-3">
                        <a href="{{ route('bills.show', $bill->id) }}" class="text-lg font-bold text-white hover:text-apple-black-200 transition-colors">
                            {{ $bill->bill_number }}/{{ $bill->year }}
                        </a>

                        <!-- Chamber Badge -->
                        <x-badge type="{{ $bill->chamber === 'cdep' ? 'primary' : 'default' }}">
                            {{ $bill->chamber === 'cdep' ? 'CDEP' : 'Senat' }}
                        </x-badge>

                        <!-- Urgency Badge -->
                        @if($bill->urgency_status)
                        <x-badge type="warning">
                            ⚡ Urgență
                        </x-badge>
                        @endif

                        <!-- Risk Badge -->
                        @php
                            $riskLevel = $bill->getHighestRiskLevel();
                        @endphp
                        @if($riskLevel)
                        <x-risk-badge :level="$riskLevel" />
                        @endif
                    </div>

                    <!-- Title -->
                    <h3 class="text-base font-semibold text-white mb-2 leading-relaxed">
                        <a href="{{ route('bills.show', $bill->id) }}" class="hover:text-apple-black-200 transition-colors">
                            {{ $bill->title }}
                        </a>
                    </h3>

                    <!-- Description -->
                    @if($bill->description)
                    <p class="text-sm text-apple-black-300 mb-3">{{ Str::limit($bill->description, 200) }}</p>
                    @endif

                    <!-- Meta Info -->
                    <div class="flex flex-wrap items-center gap-4 text-sm text-apple-black-400">
                        <span class="flex items-center">
                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ $bill->registration_date?->format('d.m.Y') ?? 'N/A' }}
                        </span>

                        @if($bill->status)
                        <span class="flex items-center">
                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            {{ ucfirst(str_replace('_', ' ', $bill->status)) }}
                        </span>
                        @endif

                        @if($bill->initiators->isNotEmpty())
                        <span class="flex items-center">
                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            {{ $bill->initiators->first()->name }}
                            @if($bill->initiators->count() > 1)
                            <span class="ml-1">+{{ $bill->initiators->count() - 1 }} altele</span>
                            @endif
                        </span>
                        @endif
                    </div>
                </div>

                <!-- Action Arrow -->
                <a href="{{ route('bills.show', $bill->id) }}" class="flex-shrink-0 ml-4 transition-transform group-hover:translate-x-1">
                    <div class="h-10 w-10 rounded-xl bg-apple-black-800 group-hover:bg-apple-black-700 flex items-center justify-center transition-colors">
                        <svg class="h-5 w-5 text-apple-black-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>
            </div>
        </div>
        @empty
        <div class="bg-apple-black-900 rounded-2xl border border-apple-black-800 p-16 text-center">
            <div class="max-w-md mx-auto">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-apple-black-800 rounded-full mb-6">
                    <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Niciun proiect găsit</h3>
                <p class="text-base text-apple-black-300 mb-8">Nu am găsit proiecte care să corespundă criteriilor tale de filtrare. Încearcă să ajustezi filtrele.</p>
                <a href="{{ route('bills.index') }}" class="inline-flex items-center px-4 py-2 bg-white text-black rounded-xl text-sm font-semibold hover:bg-apple-black-100 transition-all">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Resetează toate filtrele
                </a>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($bills->hasPages())
    <div class="flex items-center justify-between border-t border-apple-black-800 bg-apple-black-900 px-4 py-3 sm:px-6 rounded-2xl">
        <div class="flex flex-1 justify-between sm:hidden">
            @if($bills->onFirstPage())
            <span class="relative inline-flex items-center rounded-xl border border-apple-black-700 bg-apple-black-800 px-4 py-2 text-sm font-medium text-apple-black-500">Anterior</span>
            @else
            <a href="{{ $bills->previousPageUrl() }}" class="relative inline-flex items-center rounded-xl border border-apple-black-700 bg-apple-black-800 px-4 py-2 text-sm font-medium text-white hover:bg-apple-black-700">Anterior</a>
            @endif

            @if($bills->hasMorePages())
            <a href="{{ $bills->nextPageUrl() }}" class="relative ml-3 inline-flex items-center rounded-xl border border-apple-black-700 bg-apple-black-800 px-4 py-2 text-sm font-medium text-white hover:bg-apple-black-700">Următor</a>
            @else
            <span class="relative ml-3 inline-flex items-center rounded-xl border border-apple-black-700 bg-apple-black-800 px-4 py-2 text-sm font-medium text-apple-black-500">Următor</span>
            @endif
        </div>
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-apple-black-300">
                    Afișare <span class="font-medium text-white">{{ $bills->firstItem() }}</span> până la <span class="font-medium text-white">{{ $bills->lastItem() }}</span> din <span class="font-medium text-white">{{ $bills->total() }}</span> rezultate
                </p>
            </div>
            <div>
                {{ $bills->links() }}
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
