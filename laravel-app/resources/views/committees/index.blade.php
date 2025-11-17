@extends('layouts.app')

@section('title', 'Comisii Parlamentare')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="gradient-bg rounded-2xl p-8 text-white shadow-xl mb-8">
        <h1 class="text-3xl font-bold mb-2">Comisii Parlamentare</h1>
        <p class="text-lg opacity-90">Monitorizați activitatea comisiilor parlamentare și proiectele alocate</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <x-stat-card
            title="Total Comisii"
            :value="$stats['total_committees']"
            color="indigo"
            :icon="'<svg class=\'h-6 w-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4\'/></svg>'"
        />
        <x-stat-card
            title="Camera Deputaților"
            :value="$stats['cdep_committees']"
            color="blue"
            :icon="'<svg class=\'h-6 w-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z\'/></svg>'"
        />
        <x-stat-card
            title="Senat"
            :value="$stats['senate_committees']"
            color="purple"
            :icon="'<svg class=\'h-6 w-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z\'/></svg>'"
        />
        <x-stat-card
            title="Comisii Comune"
            :value="$stats['joint_committees']"
            color="green"
            :icon="'<svg class=\'h-6 w-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z\'/></svg>'"
        />
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
        <form method="GET" action="{{ route('committees.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Căutare</label>
                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Nume comisie..."
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>

                <!-- Chamber -->
                <div>
                    <label for="chamber" class="block text-sm font-medium text-gray-700 mb-1">Cameră</label>
                    <select
                        id="chamber"
                        name="chamber"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Toate</option>
                        <option value="cdep" {{ request('chamber') === 'cdep' ? 'selected' : '' }}>Camera Deputaților</option>
                        <option value="senate" {{ request('chamber') === 'senate' ? 'selected' : '' }}>Senat</option>
                        <option value="joint" {{ request('chamber') === 'joint' ? 'selected' : '' }}>Comisii Comune</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-end gap-2">
                    <button
                        type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200"
                    >
                        Aplică filtre
                    </button>
                    <a
                        href="{{ route('committees.index') }}"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors duration-200"
                    >
                        Resetează
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Committees Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        @forelse($committees as $committee)
        <a href="{{ route('committees.show', $committee->id) }}" class="block">
            <div class="bg-white rounded-xl shadow-sm p-6 card-hover border border-gray-100 h-full">
                <!-- Header -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">
                            {{ $committee->name_short ?? $committee->name }}
                        </h3>
                        @if($committee->name_short && $committee->name !== $committee->name_short)
                        <p class="text-sm text-gray-600">{{ Str::limit($committee->name, 80) }}</p>
                        @endif
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium flex-shrink-0 ml-2 {{ $committee->chamber === 'cdep' ? 'bg-blue-100 text-blue-800' : ($committee->chamber === 'senate' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800') }}">
                        @if($committee->chamber === 'cdep')
                            Camera Deputaților
                        @elseif($committee->chamber === 'senate')
                            Senat
                        @else
                            Comisie Comună
                        @endif
                    </span>
                </div>

                <!-- Chair -->
                @if($committee->chair)
                <div class="mb-4 pb-4 border-b border-gray-100">
                    <p class="text-xs text-gray-500 mb-1">Președinte</p>
                    <div class="flex items-center gap-2">
                        <div class="h-8 w-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold">
                            {{ strtoupper(substr($committee->chair->first_name ?? '', 0, 1) . substr($committee->chair->last_name ?? '', 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                {{ $committee->chair->first_name }} {{ $committee->chair->last_name }}
                            </p>
                            <p class="text-xs text-gray-600">{{ $committee->chair->party ?? 'Independent' }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Stats -->
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-indigo-600">{{ $committee->members_count ?? $committee->members->count() }}</p>
                        <p class="text-xs text-gray-600">Membri</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-blue-600">{{ $committee->bills_count ?? 0 }}</p>
                        <p class="text-xs text-gray-600">Proiecte</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-green-600">
                            @php
                                $pending = App\Models\CommitteeAssignment::where('committee_id', $committee->id)
                                    ->whereIn('status', ['assigned', 'under_review'])
                                    ->count();
                            @endphp
                            {{ $pending }}
                        </p>
                        <p class="text-xs text-gray-600">În lucru</p>
                    </div>
                </div>

                <!-- Description -->
                @if($committee->description)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-sm text-gray-600">{{ Str::limit($committee->description, 120) }}</p>
                </div>
                @endif
            </div>
        </a>
        @empty
        <div class="col-span-full">
            <div class="bg-gray-50 rounded-xl p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nicio comisie găsită</h3>
                <p class="mt-1 text-sm text-gray-500">Încercați să modificați filtrele.</p>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($committees->hasPages())
    <div class="bg-white rounded-xl shadow-sm p-4">
        {{ $committees->links() }}
    </div>
    @endif
</div>
@endsection
