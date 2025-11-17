@props(['level'])

@php
    $classes = [
        'critical' => 'badge-critical',
        'high' => 'badge-high',
        'medium' => 'badge-medium',
        'low' => 'badge-low',
    ];
    $labels = [
        'critical' => 'Critic',
        'high' => 'Ridicat',
        'medium' => 'Mediu',
        'low' => 'Scăzut',
    ];
    $icons = [
        'critical' => '🔴',
        'high' => '🟠',
        'medium' => '🟡',
        'low' => '🟢',
    ];
@endphp

<span class="status-badge {{ $classes[$level] ?? 'bg-gray-100 text-gray-800' }}">
    {{ $icons[$level] ?? '' }} {{ $labels[$level] ?? ucfirst($level) }}
</span>
