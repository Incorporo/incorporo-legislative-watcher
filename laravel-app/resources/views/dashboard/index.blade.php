@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-8">
    <!-- Hero Section -->
    <div class="gradient-bg rounded-2xl p-8 md:p-10 text-white shadow-2xl relative">
        <div class="relative z-10">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center px-3 py-1 rounded-full bg-white/20 backdrop-blur-sm border border-white/30 mb-4">
                        <span class="relative flex h-2 w-2 mr-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                        <span class="text-xs font-semibold text-white/90">Sistem Activ</span>
                    </div>
                    <h1 class="text-4xl md:text-5xl font-bold mb-3 leading-tight">
                        Monitorizare Legislativă
                        <span class="block text-indigo-200">România</span>
                    </h1>
                    <p class="text-indigo-100 text-lg font-medium leading-relaxed">
                        Transparență și analiză automată a procesului legislativ în timp real
                    </p>
                </div>
                <div class="mt-6 md:mt-0 flex items-center space-x-3">
                    <div class="text-center bg-white/15 rounded-xl px-5 py-3 backdrop-blur-md border border-white/20 shadow-lg">
                        <div class="text-3xl font-bold">{{ $stats['total_bills'] }}</div>
                        <div class="text-xs text-indigo-100 font-medium mt-1">Proiecte</div>
                    </div>
                    <div class="text-center bg-white/15 rounded-xl px-5 py-3 backdrop-blur-md border border-white/20 shadow-lg">
                        <div class="text-3xl font-bold">{{ $stats['active_bills'] }}</div>
                        <div class="text-xs text-indigo-100 font-medium mt-1">Active</div>
                    </div>
                    @if($lastScrapeJob)
                    <div class="hidden lg:block text-center bg-white/15 rounded-xl px-5 py-3 backdrop-blur-md border border-white/20 shadow-lg">
                        <div class="text-xs text-indigo-100 font-medium mb-1">Actualizat</div>
                        <div class="text-sm font-bold">{{ $lastScrapeJob->completed_at->diffForHumans() }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Total Bills -->
        <div class="stat-card group">
            <div class="flex items-center justify-between mb-4">
                <div class="h-12 w-12 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/30 group-hover:shadow-indigo-500/50 transition-shadow">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total</span>
            </div>
            <div>
                <p class="text-3xl font-bold text-gray-900 mb-1">{{ $stats['total_bills'] }}</p>
                <p class="text-sm font-medium text-gray-600">Proiecte de Lege</p>
                <p class="text-xs text-gray-500 mt-2">Toate camerele</p>
            </div>
        </div>

        <!-- Active Bills -->
        <div class="stat-card group">
            <div class="flex items-center justify-between mb-4">
                <div class="h-12 w-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/30 group-hover:shadow-blue-500/50 transition-shadow">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <span class="text-xs font-semibold text-blue-600 uppercase tracking-wider bg-blue-50 px-2 py-1 rounded">Activ</span>
            </div>
            <div>
                <p class="text-3xl font-bold text-blue-600 mb-1">{{ $stats['active_bills'] }}</p>
                <p class="text-sm font-medium text-gray-600">În Desfășurare</p>
                <p class="text-xs text-gray-500 mt-2">Comisii + Dezbateri</p>
            </div>
        </div>

        <!-- Urgent Bills -->
        <div class="stat-card group">
            <div class="flex items-center justify-between mb-4">
                <div class="h-12 w-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg shadow-orange-500/30 group-hover:shadow-orange-500/50 transition-shadow">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="text-xs font-semibold text-orange-600 uppercase tracking-wider bg-orange-50 px-2 py-1 rounded">Urgent</span>
            </div>
            <div>
                <p class="text-3xl font-bold text-orange-600 mb-1">{{ $stats['urgent_bills'] }}</p>
                <p class="text-sm font-medium text-gray-600">Procedură Urgentă</p>
                <p class="text-xs text-gray-500 mt-2">Necesită atenție</p>
            </div>
        </div>

        <!-- High Risk Bills -->
        <div class="stat-card group">
            <div class="flex items-center justify-between mb-4">
                <div class="h-12 w-12 bg-gradient-to-br from-red-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg shadow-red-500/30 group-hover:shadow-red-500/50 transition-shadow">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <span class="text-xs font-semibold text-red-600 uppercase tracking-wider bg-red-50 px-2 py-1 rounded">Risc</span>
            </div>
            <div>
                <p class="text-3xl font-bold text-red-600 mb-1">{{ $stats['high_risk_bills'] }}</p>
                <p class="text-sm font-medium text-gray-600">Risc Ridicat</p>
                <p class="text-xs text-gray-500 mt-2">Analiză AI</p>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Bills by Status -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Proiecte pe Status</h3>
            <canvas id="statusChart" height="250"></canvas>
        </div>

        <!-- Bills by Chamber -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribuție pe Cameră</h3>
            <canvas id="chamberChart" height="250"></canvas>
        </div>
    </div>

    <!-- Activity Timeline -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- High Risk Bills -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="h-5 w-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Proiecte cu Risc Ridicat
                </h3>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($highRiskBills as $bill)
                <a href="{{ route('bills.show', $bill->id) }}" class="block hover:bg-gray-50 transition-colors p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-2">
                                <span class="text-sm font-medium text-gray-900">{{ $bill->bill_number }}/{{ $bill->year }}</span>
                                @if($bill->urgency_status)
                                <span class="status-badge bg-orange-100 text-orange-800 ring-orange-600/20">Urgență</span>
                                @endif
                                @foreach($bill->risks->take(1) as $risk)
                                <span class="status-badge badge-{{ $risk->risk_level }}">{{ ucfirst($risk->risk_level) }}</span>
                                @endforeach
                            </div>
                            <h4 class="text-sm font-medium text-gray-900 mb-1">{{ Str::limit($bill->title, 100) }}</h4>
                            @if($bill->risks->isNotEmpty())
                            <p class="text-xs text-gray-600">{{ Str::limit($bill->risks->first()->description, 150) }}</p>
                            @endif
                        </div>
                        <svg class="h-5 w-5 text-gray-400 flex-shrink-0 ml-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>
                @empty
                <div class="p-6 text-center text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="mt-2">Niciun proiect cu risc ridicat detectat</p>
                </div>
                @endforelse
            </div>
            @if($highRiskBills->isNotEmpty())
            <div class="bg-gray-50 px-6 py-3 text-center">
                <a href="{{ route('risks.index', ['level' => 'high']) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    Vezi toate proiectele cu risc ridicat →
                </a>
            </div>
            @endif
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900">Activitate Recentă</h3>
            </div>
            <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                @forelse($recentBills->take(8) as $bill)
                <a href="{{ route('bills.show', $bill->id) }}" class="block hover:bg-gray-50 transition-colors p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="h-8 w-8 rounded-full bg-{{ $bill->chamber === 'cdep' ? 'blue' : 'purple' }}-100 flex items-center justify-center">
                                <span class="text-xs font-medium text-{{ $bill->chamber === 'cdep' ? 'blue' : 'purple' }}-600">
                                    {{ strtoupper(substr($bill->chamber, 0, 1)) }}
                                </span>
                            </div>
                        </div>
                        <div class="ml-3 flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $bill->bill_number }}/{{ $bill->year }}</p>
                            <p class="text-xs text-gray-600 truncate">{{ Str::limit($bill->title, 60) }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $bill->registration_date?->diffForHumans() }}</p>
                        </div>
                    </div>
                </a>
                @empty
                <div class="p-4 text-center text-gray-500 text-sm">
                    Nicio activitate recentă
                </div>
                @endforelse
            </div>
            <div class="bg-gray-50 px-6 py-3 text-center">
                <a href="{{ route('bills.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    Vezi toate proiectele →
                </a>
            </div>
        </div>
    </div>

    <!-- Bills per Month Chart -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Evoluție Ultimele 6 Luni</h3>
        <canvas id="monthlyChart" height="80"></canvas>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Bills by Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'bar',
        data: {
            labels: {!! $billsByStatus->pluck('status')->map(fn($s) => ucfirst(str_replace('_', ' ', $s)))->toJson() !!},
            datasets: [{
                label: 'Proiecte',
                data: {!! $billsByStatus->pluck('total')->toJson() !!},
                backgroundColor: 'rgba(99, 102, 241, 0.8)',
                borderColor: 'rgb(99, 102, 241)',
                borderWidth: 1,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0, 0, 0, 0.05)' } },
                x: { grid: { display: false } }
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
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(147, 51, 234, 0.8)'
                ],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
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
                borderColor: 'rgb(99, 102, 241)',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'rgb(99, 102, 241)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0, 0, 0, 0.05)' } },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>
@endpush
@endsection
