@extends('layouts.app')

@section('title', $legislator->first_name . ' ' . $legislator->last_name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('legislators.index') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-700">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Înapoi la legislatori
        </a>
    </div>

    <!-- Header Card -->
    <div class="bg-white rounded-xl shadow-sm p-8 mb-8">
        <div class="flex items-start gap-6">
            <!-- Avatar -->
            <div class="h-24 w-24 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-3xl font-bold flex-shrink-0">
                {{ strtoupper(substr($legislator->first_name ?? '', 0, 1) . substr($legislator->last_name ?? '', 0, 1)) }}
            </div>

            <!-- Info -->
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-gray-900">
                    {{ $legislator->first_name }} {{ $legislator->last_name }}
                </h1>
                <p class="text-xl text-gray-600 mt-1">{{ $legislator->party ?? 'Independent' }}</p>

                <div class="flex flex-wrap items-center gap-2 mt-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $legislator->chamber === 'cdep' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                        {{ $legislator->chamber === 'cdep' ? 'Camera Deputaților' : 'Senat' }}
                    </span>
                    @if($legislator->active)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Activ
                    </span>
                    @endif
                </div>

                @if($legislator->constituency)
                <div class="mt-3 flex items-center text-gray-600">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Circumscripția: <strong>{{ $legislator->constituency }}</strong></span>
                </div>
                @endif

                @if($legislator->email)
                <div class="mt-2 flex items-center text-gray-600">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <a href="mailto:{{ $legislator->email }}" class="text-indigo-600 hover:underline">{{ $legislator->email }}</a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <x-stat-card
            title="Proiecte inițiate"
            :value="$stats['total_bills']"
            color="indigo"
            :icon="'<svg class=\'h-6 w-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z\'/></svg>'"
        />
        <x-stat-card
            title="Co-semnături"
            :value="$stats['cosponsored_bills']"
            color="blue"
            :icon="'<svg class=\'h-6 w-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z\'/></svg>'"
        />
        <x-stat-card
            title="Rată de succes"
            :value="$successRate . '%'"
            color="green"
            :icon="'<svg class=\'h-6 w-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z\'/></svg>'"
        />
        <x-stat-card
            title="Comisii"
            :value="$stats['committees']"
            color="purple"
            :icon="'<svg class=\'h-6 w-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z\'/></svg>'"
        />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Activity Timeline Chart -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Activitate legislativă (ultimele 6 luni)</h2>
                <div class="h-64">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>

            <!-- Initiated Bills -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900">Proiecte de lege inițiate</h2>
                    <span class="text-sm text-gray-600">{{ $initiatedBills->count() }} proiecte</span>
                </div>

                @forelse($initiatedBills as $bill)
                <div class="border-l-4 {{ $bill->urgency_status ? 'border-red-500' : 'border-gray-300' }} pl-4 mb-4 last:mb-0">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <a href="{{ route('bills.show', $bill->id) }}" class="text-indigo-600 hover:text-indigo-700 font-medium">
                                {{ $bill->bill_number }}/{{ $bill->year }}
                            </a>
                            <h3 class="text-sm text-gray-900 mt-1">{{ Str::limit($bill->title, 100) }}</h3>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ ucfirst(str_replace('_', ' ', $bill->status)) }}
                                </span>
                                @if($bill->urgency_status)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                    Urgență
                                </span>
                                @endif
                                <span class="text-xs text-gray-500">{{ $bill->registration_date?->format('d M Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-8">Nu există proiecte inițiate</p>
                @endforelse

                @if($initiatedBills->count() > 5)
                <div class="mt-4 text-center">
                    <a href="{{ route('bills.index', ['initiator' => $legislator->id]) }}" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                        Vezi toate proiectele →
                    </a>
                </div>
                @endif
            </div>

            <!-- Co-sponsored Bills -->
            @if($cosponsoredBills->count() > 0)
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900">Proiecte co-semnate</h2>
                    <span class="text-sm text-gray-600">{{ $cosponsoredBills->count() }} proiecte</span>
                </div>

                @foreach($cosponsoredBills->take(5) as $bill)
                <div class="border-l-4 border-blue-300 pl-4 mb-4 last:mb-0">
                    <a href="{{ route('bills.show', $bill->id) }}" class="text-indigo-600 hover:text-indigo-700 font-medium">
                        {{ $bill->bill_number }}/{{ $bill->year }}
                    </a>
                    <h3 class="text-sm text-gray-900 mt-1">{{ Str::limit($bill->title, 100) }}</h3>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                            {{ ucfirst(str_replace('_', ' ', $bill->status)) }}
                        </span>
                        <span class="text-xs text-gray-500">{{ $bill->registration_date?->format('d M Y') }}</span>
                    </div>
                </div>
                @endforeach

                @if($cosponsoredBills->count() > 5)
                <div class="mt-4 text-center">
                    <a href="{{ route('bills.index', ['cosponsor' => $legislator->id]) }}" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                        Vezi toate co-semnăturile →
                    </a>
                </div>
                @endif
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-8">
            <!-- Committee Memberships -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Comisii</h2>
                @forelse($committees as $committee)
                <div class="mb-4 last:mb-0">
                    <a href="{{ route('committees.show', $committee->id) }}" class="block p-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <h3 class="font-medium text-gray-900">{{ $committee->name_short ?? $committee->name }}</h3>
                        <p class="text-sm text-gray-600 mt-1">
                            @if($committee->pivot->is_chair)
                            <span class="text-indigo-600 font-medium">Președinte</span>
                            @else
                            Membru
                            @endif
                        </p>
                    </a>
                </div>
                @empty
                <p class="text-gray-500 text-sm">Nu face parte din comisii</p>
                @endforelse
            </div>

            <!-- Activity Stats -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Statistici detaliate</h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Proiecte adoptate</span>
                        <span class="font-bold text-green-600">{{ $stats['passed_bills'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">În dezbatere</span>
                        <span class="font-bold text-blue-600">{{ $stats['active_bills'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Respinse</span>
                        <span class="font-bold text-red-600">{{ $stats['rejected_bills'] }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-3 border-t border-gray-200">
                        <span class="text-sm text-gray-600">Activitate lunară medie</span>
                        <span class="font-bold text-indigo-600">{{ number_format($stats['avg_monthly_activity'], 1) }}</span>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Activitate recentă</h2>
                <div class="space-y-3">
                    @forelse($recentActivity->take(5) as $activity)
                    <div class="text-sm">
                        <div class="flex items-center gap-2">
                            <div class="h-2 w-2 rounded-full bg-indigo-500 flex-shrink-0"></div>
                            <span class="text-gray-600">{{ $activity->event_date?->format('d M Y') }}</span>
                        </div>
                        <p class="text-gray-900 mt-1 ml-4">{{ Str::limit($activity->description, 80) }}</p>
                    </div>
                    @empty
                    <p class="text-gray-500 text-sm">Nicio activitate recentă</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Activity Timeline Chart
    const activityData = @json($activityTimeline);

    const ctx = document.getElementById('activityChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: activityData.map(d => {
                const [year, month] = d.month.split('-');
                const date = new Date(year, month - 1);
                return date.toLocaleDateString('ro-RO', { month: 'short', year: 'numeric' });
            }),
            datasets: [{
                label: 'Proiecte inițiate',
                data: activityData.map(d => d.total),
                borderColor: 'rgb(99, 102, 241)',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
});
</script>
@endsection
