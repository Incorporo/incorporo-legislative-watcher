@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-3 py-2 text-sm font-medium leading-5 text-white bg-apple-black-800 rounded-xl focus:outline-none transition duration-150 ease-in-out'
            : 'inline-flex items-center px-3 py-2 text-sm font-medium leading-5 text-apple-black-300 hover:text-white hover:bg-apple-black-800 rounded-xl focus:outline-none transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
