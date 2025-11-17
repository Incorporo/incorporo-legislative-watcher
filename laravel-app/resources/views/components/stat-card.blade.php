@props([
    'title',
    'value',
    'subtitle' => null,
    'icon' => null,
    'color' => 'indigo',
    'trend' => null,
    'trendDirection' => 'up'
])

@php
    $colors = [
        'indigo' => 'bg-indigo-100 text-indigo-600',
        'blue' => 'bg-blue-100 text-blue-600',
        'green' => 'bg-green-100 text-green-600',
        'red' => 'bg-red-100 text-red-600',
        'orange' => 'bg-orange-100 text-orange-600',
        'purple' => 'bg-purple-100 text-purple-600',
        'yellow' => 'bg-yellow-100 text-yellow-600',
    ];
    $colorClass = $colors[$color] ?? $colors['indigo'];
@endphp

<div class="bg-white rounded-xl shadow-sm p-6 card-hover border border-gray-100">
    <div class="flex items-center justify-between">
        <div class="flex-1">
            <p class="text-sm font-medium text-gray-600">{{ $title }}</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $value }}</p>
            @if($subtitle)
            <p class="text-xs text-gray-500 mt-1">{{ $subtitle }}</p>
            @endif
            @if($trend)
            <div class="flex items-center mt-2">
                @if($trendDirection === 'up')
                <svg class="h-4 w-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                @else
                <svg class="h-4 w-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                </svg>
                @endif
                <span class="ml-1 text-sm {{ $trendDirection === 'up' ? 'text-green-600' : 'text-red-600' }}">
                    {{ $trend }}
                </span>
            </div>
            @endif
        </div>
        @if($icon)
        <div class="h-12 w-12 {{ $colorClass }} rounded-lg flex items-center justify-center flex-shrink-0 ml-4">
            {!! $icon !!}
        </div>
        @endif
    </div>
</div>
