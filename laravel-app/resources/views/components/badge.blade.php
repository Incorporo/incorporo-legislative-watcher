@props(['type' => 'default', 'size' => 'md'])

@php
    $typeClasses = [
        'default' => 'bg-slate-100 text-slate-700 ring-slate-600/20',
        'primary' => 'bg-blue-50 text-blue-700 ring-blue-600/30',
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/30',
        'warning' => 'bg-amber-50 text-amber-700 ring-amber-600/30',
        'danger' => 'bg-red-50 text-red-700 ring-red-600/30',
        'info' => 'bg-teal-50 text-teal-700 ring-teal-600/30',
        'purple' => 'bg-purple-50 text-purple-700 ring-purple-600/30',
    ];

    $sizeClasses = [
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-1 text-xs',
        'lg' => 'px-3 py-1.5 text-sm',
    ];

    $classes = ($typeClasses[$type] ?? $typeClasses['default']) . ' ' . ($sizeClasses[$size] ?? $sizeClasses['md']);
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-lg font-semibold ring-1 ring-inset ' . $classes]) }}>
    {{ $slot }}
</span>
