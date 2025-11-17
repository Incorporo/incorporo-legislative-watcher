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
        'blue' => 'bg-apple-black-800',
        'teal' => 'bg-apple-black-700',
        'amber' => 'bg-apple-black-700',
        'red' => 'bg-apple-black-700',
        'slate' => 'bg-apple-black-800',
        'emerald' => 'bg-apple-black-700',
    ];
    $colorClass = $colors[$color] ?? $colors['blue'];
@endphp

<div class="stat-card group bg-apple-black-900 rounded-2xl p-6 border border-apple-black-800 hover:border-apple-black-700 transition-all duration-300">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-semibold text-apple-black-400 uppercase tracking-wide">{{ $title }}</p>
                @if($trend)
                    <span class="text-xs font-bold {{ $trendDirection === 'up' ? 'text-apple-black-300' : 'text-apple-black-500' }} flex items-center">
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
                <p class="text-4xl font-bold text-white tracking-tight">{{ $value }}</p>
            </div>
            @if($subtitle)
                <p class="text-sm text-apple-black-400 font-medium">{{ $subtitle }}</p>
            @endif
        </div>
        @if($icon)
        <div class="ml-4 flex-shrink-0">
            <div class="w-14 h-14 rounded-2xl {{ $colorClass }} flex items-center justify-center group-hover:bg-apple-black-700 transition-all duration-300">
                {!! $icon !!}
            </div>
        </div>
        @endif
    </div>
</div>
