<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-5 py-2.5 bg-white text-black border border-transparent rounded-xl font-semibold text-sm hover:bg-apple-black-100 active:bg-apple-black-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-black transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
