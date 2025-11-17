@props(['level'])

@php
    $classes = [
        'critical' => 'bg-white text-black ring-apple-black-300',
        'high' => 'bg-apple-black-700 text-white ring-apple-black-600',
        'medium' => 'bg-apple-black-800 text-apple-black-200 ring-apple-black-700',
        'low' => 'bg-apple-black-900 text-apple-black-400 ring-apple-black-800',
    ];
    $labels = [
        'critical' => 'Critic',
        'high' => 'Ridicat',
        'medium' => 'Mediu',
        'low' => 'Scăzut',
    ];
    $icons = [
        'critical' => '●',
        'high' => '●',
        'medium' => '●',
        'low' => '●',
    ];
@endphp

<span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-xl ring-1 ring-inset {{ $classes[$level] ?? 'bg-apple-black-800 text-apple-black-300' }}">
    {{ $icons[$level] ?? '' }} {{ $labels[$level] ?? ucfirst($level) }}
</span>
