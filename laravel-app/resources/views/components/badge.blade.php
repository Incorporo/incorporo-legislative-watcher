@props(['type' => 'default', 'size' => 'md'])

@php
    $typeClasses = [
        'default' => 'bg-apple-black-800 text-apple-black-200 ring-apple-black-700',
        'primary' => 'bg-white text-black ring-apple-black-300',
        'success' => 'bg-apple-black-700 text-white ring-apple-black-600',
        'warning' => 'bg-apple-black-700 text-apple-black-100 ring-apple-black-600',
        'danger' => 'bg-apple-black-800 text-white ring-apple-black-700',
        'info' => 'bg-apple-black-700 text-apple-black-100 ring-apple-black-600',
        'purple' => 'bg-apple-black-700 text-apple-black-100 ring-apple-black-600',
    ];

    $sizeClasses = [
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-1 text-xs',
        'lg' => 'px-3 py-1.5 text-sm',
    ];

    $classes = ($typeClasses[$type] ?? $typeClasses['default']) . ' ' . ($sizeClasses[$size] ?? $sizeClasses['md']);
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-xl font-semibold ring-1 ring-inset ' . $classes]) }}>
    {{ $slot }}
</span>
