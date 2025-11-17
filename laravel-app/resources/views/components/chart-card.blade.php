@props(['title', 'subtitle' => '', 'actions' => ''])

<div class="bg-apple-black-900 rounded-2xl border border-apple-black-800 overflow-hidden hover:border-apple-black-700 transition-colors duration-300">
    <div class="px-6 py-5 border-b border-apple-black-800">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-white">{{ $title }}</h3>
                @if($subtitle)
                    <p class="text-sm text-apple-black-400 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            @if($actions)
                <div>{{ $actions }}</div>
            @endif
        </div>
    </div>
    <div class="p-6">
        {{ $slot }}
    </div>
</div>
