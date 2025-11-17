@props(['title', 'subtitle' => '', 'actions' => ''])

<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden hover:border-slate-300 transition-colors duration-300">
    <div class="px-6 py-5 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-slate-900">{{ $title }}</h3>
                @if($subtitle)
                    <p class="text-sm text-slate-600 mt-0.5">{{ $subtitle }}</p>
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
