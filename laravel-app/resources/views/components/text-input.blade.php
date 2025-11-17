@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'bg-apple-black-800 border-apple-black-700 text-white focus:border-white focus:ring-white rounded-xl']) !!}>
