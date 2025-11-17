@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-apple-black-300']) }}>
    {{ $value ?? $slot }}
</label>
