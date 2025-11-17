@extends('layouts.app')

@section('title', 'Legislatori')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="gradient-bg rounded-2xl p-8 text-white shadow-xl mb-8">
        <h1 class="text-3xl font-bold mb-2">Legislatori</h1>
        <p class="text-lg opacity-90">Monitorizați activitatea și performanța legislatorilor români</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <x-stat-card
            title="Total Legislatori"
            :value="$stats['total_legislators']"
            color="indigo"
            :icon="'<svg class=\'h-6 w-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z\'/></svg>'"
        />
        <x-stat-card
            title="Deputați"
            :value="$stats['cdep_legislators']"
            subtitle="Camera Deputaților"
            color="blue"
            :icon="'<svg class=\'h-6 w-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4\'/></svg>'"
        />
        <x-stat-card
            title="Senatori"
            :value="$stats['senate_legislators']"
            subtitle="Senatul României"
            color="purple"
            :icon="'<svg class=\'h-6 w-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z\'/></svg>'"
        />
        <x-stat-card
            title="Partide Active"
            :value="$stats['total_parties']"
            color="green"
            :icon="'<svg class=\'h-6 w-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z\'/></svg>'"
        />
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
        <form method="GET" action="{{ route('legislators.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Căutare</label>
                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Nume legislator..."
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
                    </select>
                </div>

                <!-- Party -->
                <div>
                    <label for="party" class="block text-sm font-medium text-gray-700 mb-1">Partid</label>
                    <select
                        id="party"
                        name="party"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Toate</option>
                        @foreach($parties as $party)
                        <option value="{{ $party }}" {{ request('party') === $party ? 'selected' : '' }}>{{ $party }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Sort By -->
                <div>
                    <label for="sort" class="block text-sm font-medium text-gray-700 mb-1">Sortare</label>
                    <select
                        id="sort"
                        name="sort"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="name" {{ request('sort', 'name') === 'name' ? 'selected' : '' }}>Nume</option>
                        <option value="bills" {{ request('sort') === 'bills' ? 'selected' : '' }}>Proiecte inițiate</option>
                        <option value="recent" {{ request('sort') === 'recent' ? 'selected' : '' }}>Activitate recentă</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-2">
                <button
                    type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200"
                >
                    Aplică filtre
                </button>
                <a
                    href="{{ route('legislators.index') }}"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors duration-200"
                >
                    Resetează
                </a>
            </div>
        </form>
    </div>

    <!-- Party Distribution Chart -->
    @if(!request('chamber') && !request('party'))
    <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Distribuție pe partide</h2>
        <div class="h-64">
            <canvas id="partyChart"></canvas>
        </div>
    </div>
    @endif

    <!-- Legislators Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        @forelse($legislators as $legislator)
        <a href="{{ route('legislators.show', $legislator->id) }}" class="block">
            <div class="bg-white rounded-xl shadow-sm p-6 card-hover border border-gray-100 h-full">
                <div class="flex items-start gap-4">
                    <!-- Avatar -->
                    <div class="h-16 w-16 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xl font-bold flex-shrink-0">
                        {{ strtoupper(substr($legislator->first_name ?? '', 0, 1) . substr($legislator->last_name ?? '', 0, 1)) }}
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-bold text-gray-900 truncate">
                            {{ $legislator->first_name }} {{ $legislator->last_name }}
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">{{ $legislator->party ?? 'Independent' }}</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $legislator->chamber === 'cdep' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                {{ $legislator->chamber === 'cdep' ? 'Deputat' : 'Senator' }}
                            </span>
                            @if($legislator->active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Activ
                            </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-2xl font-bold text-indigo-600">{{ $legislator->initiated_bills_count ?? 0 }}</p>
                            <p class="text-xs text-gray-600">Proiecte inițiate</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-purple-600">{{ $legislator->cosponsored_bills_count ?? 0 }}</p>
                            <p class="text-xs text-gray-600">Co-semnături</p>
                        </div>
                    </div>
                </div>

                @if($legislator->constituency)
                <div class="mt-3 flex items-center text-sm text-gray-600">
                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    {{ $legislator->constituency }}
                </div>
                @endif
            </div>
        </a>
        @empty
        <div class="col-span-full">
            <div class="bg-gray-50 rounded-xl p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Niciun legislator găsit</h3>
                <p class="mt-1 text-sm text-gray-500">Încercați să modificați filtrele.</p>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($legislators->hasPages())
    <div class="bg-white rounded-xl shadow-sm p-4">
        {{ $legislators->links() }}
    </div>
    @endif
</div>

@if(!request('chamber') && !request('party'))
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const partyData = @json($partyDistribution);

    const ctx = document.getElementById('partyChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: partyData.map(p => p.party || 'Independent'),
            datasets: [{
                data: partyData.map(p => p.total),
                backgroundColor: [
                    'rgba(99, 102, 241, 0.8)',
                    'rgba(168, 85, 247, 0.8)',
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(107, 114, 128, 0.8)',
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        font: {
                            size: 12
                        },
                        padding: 15,
                        generateLabels: function(chart) {
                            const data = chart.data;
                            return data.labels.map((label, i) => ({
                                text: `${label} (${data.datasets[0].data[i]})`,
                                fillStyle: data.datasets[0].backgroundColor[i],
                                hidden: false,
                                index: i
                            }));
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
});
</script>
@endif
@endsection
