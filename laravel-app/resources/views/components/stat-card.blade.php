@props([
    'title',
    'value',
    'subtitle' => null,
    'icon' => null,
    'color' => 'blue',
    'trend' => null,
    'trendDirection' => 'up'
])

@php
    $colors = [
        'blue' => 'from-blue-600 to-blue-700 shadow-blue-500/20',
        'teal' => 'from-teal-600 to-teal-700 shadow-teal-500/20',
        'amber' => 'from-amber-600 to-amber-700 shadow-amber-500/20',
        'red' => 'from-red-600 to-red-700 shadow-red-500/20',
        'slate' => 'from-slate-600 to-slate-700 shadow-slate-500/20',
        'emerald' => 'from-emerald-600 to-emerald-700 shadow-emerald-500/20',
    ];
    $colorClass = $colors[$color] ?? $colors['blue'];
@endphp

<div class="stat-card group bg-white rounded-2xl p-6 border border-slate-200 hover:border-slate-300 transition-all duration-300">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-semibold text-slate-600 uppercase tracking-wide">{{ $title }}</p>
                @if($trend)
                    <span class="text-xs font-bold {{ $trendDirection === 'up' ? 'text-emerald-600' : 'text-red-600' }} flex items-center">
                        @if($trendDirection === 'up')
                            <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                            </svg>
                        @else
                            <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                            </svg>
                        @endif
                        {{ $trend }}
                    </span>
                @endif
            </div>
            <div class="mb-3">
                <p class="text-4xl font-bold text-slate-900 tracking-tight">{{ $value }}</p>
            </div>
            @if($subtitle)
                <p class="text-sm text-slate-500 font-medium">{{ $subtitle }}</p>
            @endif
        </div>
        @if($icon)
        <div class="ml-4 flex-shrink-0">
            <div class="w-14 h-14 rounded-xl bg-gradient-to-br {{ $colorClass }} flex items-center justify-center shadow-lg group-hover:shadow-xl transition-shadow duration-300">
                {!! $icon !!}
            </div>
        </div>
        @endif
    </div>
</div>
