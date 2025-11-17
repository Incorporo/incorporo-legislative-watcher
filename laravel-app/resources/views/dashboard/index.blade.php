@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <!-- Hero Section -->
    <div class="bg-apple-black-900 border border-apple-black-800 rounded-3xl p-10 md:p-12 relative overflow-hidden">
        <div class="relative z-10">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center px-4 py-2 rounded-full bg-white text-black mb-5">
                        <span class="relative flex h-2 w-2 mr-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-black opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-black"></span>
                        </span>
                        <span class="text-xs font-bold uppercase tracking-wider">Sistem Operațional</span>
                    </div>
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-4 leading-tight tracking-tight text-white">
                        Monitorizare Legislativă
                    </h1>
                    <p class="text-xl md:text-2xl font-semibold text-apple-black-300 mb-3">
                        Parlamentul României
                    </p>
                    <p class="text-apple-black-400 text-base md:text-lg leading-relaxed max-w-xl">
                        Sistem profesional de monitorizare în timp real a procesului legislativ cu analiză automată și alertare inteligentă
                    </p>
                </div>
                <div class="mt-8 md:mt-0 grid grid-cols-2 gap-4">
                    <div class="text-center bg-apple-black-800 rounded-2xl px-6 py-4 border border-apple-black-700">
                        <div class="text-4xl font-bold mb-1 text-white">{{ $stats['total_bills'] }}</div>
                        <div class="text-xs text-apple-black-400 font-semibold uppercase tracking-wider">Proiecte</div>
                    </div>
                    <div class="text-center bg-apple-black-800 rounded-2xl px-6 py-4 border border-apple-black-700">
                        <div class="text-4xl font-bold mb-1 text-white">{{ $stats['active_bills'] }}</div>
                        <div class="text-xs text-apple-black-400 font-semibold uppercase tracking-wider">Active</div>
                    </div>
                    @if($lastScrapeJob)
                    <div class="col-span-2 text-center bg-apple-black-800 rounded-2xl px-6 py-3 border border-apple-black-700">
                        <div class="text-xs text-apple-black-400 font-semibold uppercase tracking-wider mb-1">Actualizat</div>
                        <div class="text-base font-bold font-mono text-white">{{ $lastScrapeJob->completed_at->format('H:i') }} • {{ $lastScrapeJob->completed_at->format('d.m.Y') }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-stat-card
            title="Total Proiecte"
            :value="$stats['total_bills']"
            subtitle="Toate camerele parlamentare"
            color="slate"
            :icon="'<svg class=\'h-7 w-7 text-white\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\' stroke-width=\'2.5\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z\' /></svg>'"
        />

        <x-stat-card
            title="În Desfășurare"
            :value="$stats['active_bills']"
            subtitle="Comisii și dezbateri parlamentare"
            color="blue"
            :icon="'<svg class=\'h-7 w-7 text-white\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\' stroke-width=\'2.5\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M13 10V3L4 14h7v7l9-11h-7z\' /></svg>'"
        />

        <x-stat-card
            title="Procedură Urgentă"
            :value="$stats['urgent_bills']"
            subtitle="Necesită atenție prioritară"
            color="amber"
            :icon="'<svg class=\'h-7 w-7 text-white\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\' stroke-width=\'2.5\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z\' /></svg>'"
        />

        <x-stat-card
            title="Risc Ridicat"
            :value="$stats['high_risk_bills']"
            subtitle="Identificate prin analiză AI"
            color="red"
            :icon="'<svg class=\'h-7 w-7 text-white\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\' stroke-width=\'2.5\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z\' /></svg>'"
        />
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-chart-card
            title="Proiecte pe Status"
            subtitle="Distribuția proiectelor după stadiul de procesare"
        >
            <canvas id="statusChart" height="250"></canvas>
        </x-chart-card>

        <x-chart-card
            title="Distribuție pe Cameră"
            subtitle="Camera Deputaților vs. Senat"
        >
            <canvas id="chamberChart" height="250"></canvas>
        </x-chart-card>
    </div>

    <!-- Activity Timeline -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- High Risk Bills -->
        <div class="lg:col-span-2 bg-apple-black-900 rounded-2xl border border-apple-black-800 overflow-hidden">
            <div class="border-b border-apple-black-800 px-6 py-4">
                <h3 class="text-lg font-semibold text-white flex items-center">
                    <svg class="h-5 w-5 text-white mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Proiecte cu Risc Ridicat
                </h3>
            </div>
            <div class="divide-y divide-apple-black-800">
                @forelse($highRiskBills as $bill)
                <a href="{{ route('bills.show', $bill->id) }}" class="block hover:bg-apple-black-800 transition-colors p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-2">
                                <span class="text-sm font-medium text-white">{{ $bill->bill_number }}/{{ $bill->year }}</span>
                                @if($bill->urgency_status)
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-xl bg-apple-black-700 text-white ring-1 ring-inset ring-apple-black-600">Urgență</span>
                                @endif
                                @foreach($bill->risks->take(1) as $risk)
                                <x-risk-badge :level="$risk->risk_level" />
                                @endforeach
                            </div>
                            <h4 class="text-sm font-medium text-white mb-1">{{ Str::limit($bill->title, 100) }}</h4>
                            @if($bill->risks->isNotEmpty())
                            <p class="text-xs text-apple-black-400">{{ Str::limit($bill->risks->first()->description, 150) }}</p>
                            @endif
                        </div>
                        <svg class="h-5 w-5 text-apple-black-500 flex-shrink-0 ml-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>
                @empty
                <div class="p-6 text-center text-apple-black-500">
                    <svg class="mx-auto h-12 w-12 text-apple-black-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="mt-2">Niciun proiect cu risc ridicat detectat</p>
                </div>
                @endforelse
            </div>
            @if($highRiskBills->isNotEmpty())
            <div class="bg-apple-black-800 px-6 py-3 text-center border-t border-apple-black-700">
                <a href="{{ route('risks.index', ['level' => 'high']) }}" class="text-sm font-medium text-white hover:text-apple-black-200">
                    Vezi toate proiectele cu risc ridicat →
                </a>
            </div>
            @endif
        </div>

        <!-- Recent Activity -->
        <div class="bg-apple-black-900 rounded-2xl border border-apple-black-800 overflow-hidden">
            <div class="border-b border-apple-black-800 px-6 py-4">
                <h3 class="text-lg font-semibold text-white">Activitate Recentă</h3>
            </div>
            <div class="divide-y divide-apple-black-800 max-h-96 overflow-y-auto">
                @forelse($recentBills->take(8) as $bill)
                <a href="{{ route('bills.show', $bill->id) }}" class="block hover:bg-apple-black-800 transition-colors p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="h-8 w-8 rounded-full bg-apple-black-800 border border-apple-black-700 flex items-center justify-center">
                                <span class="text-xs font-medium text-white">
                                    {{ strtoupper(substr($bill->chamber, 0, 1)) }}
                                </span>
                            </div>
                        </div>
                        <div class="ml-3 flex-1 min-w-0">
                            <p class="text-sm font-medium text-white truncate">{{ $bill->bill_number }}/{{ $bill->year }}</p>
                            <p class="text-xs text-apple-black-400 truncate">{{ Str::limit($bill->title, 60) }}</p>
                            <p class="text-xs text-apple-black-500 mt-1">{{ $bill->registration_date?->diffForHumans() }}</p>
                        </div>
                    </div>
                </a>
                @empty
                <div class="p-4 text-center text-apple-black-500 text-sm">
                    Nicio activitate recentă
                </div>
                @endforelse
            </div>
            <div class="bg-apple-black-800 px-6 py-3 text-center border-t border-apple-black-700">
                <a href="{{ route('bills.index') }}" class="text-sm font-medium text-white hover:text-apple-black-200">
                    Vezi toate proiectele →
                </a>
            </div>
        </div>
    </div>

    <!-- Bills per Month Chart -->
    <x-chart-card
        title="Evoluție Activitate Legislativă"
        subtitle="Proiecte înregistrate în ultimele 6 luni"
    >
        <canvas id="monthlyChart" height="80"></canvas>
    </x-chart-card>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monochromatic Apple-inspired palette
    const colors = {
        primary: 'rgb(255, 255, 255)',      // white
        primaryLight: 'rgba(255, 255, 255, 0.1)',
        secondary: 'rgb(200, 200, 200)',    // light gray
        slate: 'rgb(120, 120, 120)',        // mid gray
        amber: 'rgb(160, 160, 160)',        // gray
        emerald: 'rgb(180, 180, 180)',      // light-mid gray
        red: 'rgb(140, 140, 140)',          // darker gray
    };

    // Global Chart.js defaults
    Chart.defaults.font.family = "'Inter', -apple-system, BlinkMacSystemFont, sans-serif";
    Chart.defaults.color = '#a0a0a0';

    // Bills by Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'bar',
        data: {
            labels: {!! $billsByStatus->pluck('status')->map(fn($s) => ucfirst(str_replace('_', ' ', $s)))->toJson() !!},
            datasets: [{
                label: 'Proiecte',
                data: {!! $billsByStatus->pluck('total')->toJson() !!},
                backgroundColor: colors.primary,
                borderColor: colors.primary,
                borderWidth: 0,
                borderRadius: 12,
                barThickness: 40,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(26, 26, 26, 0.95)',
                    titleFont: { size: 14, weight: 'bold' },
                    bodyFont: { size: 13 },
                    padding: 12,
                    cornerRadius: 12,
                    borderColor: 'rgba(74, 74, 74, 0.5)',
                    borderWidth: 1,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(74, 74, 74, 0.3)', drawBorder: false },
                    ticks: { font: { size: 12, weight: '500' }, color: '#a0a0a0' }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 12, weight: '500' }, color: '#a0a0a0' }
                }
            }
        }
    });

    // Bills by Chamber Chart
    const chamberCtx = document.getElementById('chamberChart').getContext('2d');
    new Chart(chamberCtx, {
        type: 'doughnut',
        data: {
            labels: ['Camera Deputaților', 'Senat'],
            datasets: [{
                data: [{{ $billsByChamber['cdep'] ?? 0 }}, {{ $billsByChamber['senate'] ?? 0 }}],
                backgroundColor: [colors.primary, colors.secondary],
                borderWidth: 4,
                borderColor: '#000000',
                hoverOffset: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        font: { size: 13, weight: '600' },
                        usePointStyle: true,
                        pointStyle: 'circle',
                        color: '#a0a0a0',
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(26, 26, 26, 0.95)',
                    titleFont: { size: 14, weight: 'bold' },
                    bodyFont: { size: 13 },
                    padding: 12,
                    cornerRadius: 12,
                    borderColor: 'rgba(74, 74, 74, 0.5)',
                    borderWidth: 1,
                }
            },
            cutout: '65%',
        }
    });

    // Monthly Trend Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: {!! $billsPerMonth->pluck('month')->toJson() !!},
            datasets: [{
                label: 'Proiecte Înregistrate',
                data: {!! $billsPerMonth->pluck('total')->toJson() !!},
                borderColor: colors.primary,
                backgroundColor: colors.primaryLight,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: colors.primary,
                pointBorderColor: '#ffffff',
                pointBorderWidth: 3,
                pointRadius: 5,
                pointHoverRadius: 7,
                borderWidth: 3,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(26, 26, 26, 0.95)',
                    titleFont: { size: 14, weight: 'bold' },
                    bodyFont: { size: 13 },
                    padding: 12,
                    cornerRadius: 12,
                    borderColor: 'rgba(74, 74, 74, 0.5)',
                    borderWidth: 1,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(74, 74, 74, 0.3)', drawBorder: false },
                    ticks: { font: { size: 12, weight: '500' }, color: '#a0a0a0' }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 12, weight: '500' }, color: '#a0a0a0' }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index',
            }
        }
    });
});
</script>
@endpush
@endsection
