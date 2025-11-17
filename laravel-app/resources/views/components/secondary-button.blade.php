<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-5 py-2.5 bg-apple-black-800 border border-apple-black-700 rounded-xl font-semibold text-sm text-apple-black-200 hover:bg-apple-black-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-apple-black-500 focus:ring-offset-2 focus:ring-offset-black disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
