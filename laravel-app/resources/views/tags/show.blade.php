<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-6 h-6 rounded" style="background-color: {{ $tag->color }}"></div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $tag->name }}
                </h2>
                <span class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded">
                    {{ $tag->bills->count() }} {{ Str::plural('bill', $tag->bills->count()) }}
                </span>
            </div>
            <a href="{{ route('tags.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                Back to Tags
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($tag->description)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <p class="text-gray-700">{{ $tag->description }}</p>
                    </div>
                </div>
            @endif

            @if($tag->bills->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center text-gray-500">
                        <p class="text-lg mb-2">No bills tagged yet</p>
                        <p>Start tagging bills with "{{ $tag->name }}" to organize them.</p>
                        <a href="{{ route('bills.index') }}" class="inline-block mt-4 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Browse Bills
                        </a>
                    </div>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($tag->bills as $bill)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold mb-2">
                                            <a href="{{ route('bills.show', $bill) }}" class="text-indigo-600 hover:text-indigo-800">
                                                {{ $bill->title }}
                                            </a>
                                        </h3>

                                        <div class="text-sm text-gray-600 mb-2">
                                            <span class="font-medium">{{ $bill->bill_number }}</span> &middot;
                                            <span>{{ ucfirst($bill->chamber) }}</span> &middot;
                                            <span>{{ ucfirst(str_replace('_', ' ', $bill->status)) }}</span>
                                        </div>

                                        @if($bill->summary)
                                            <p class="text-sm text-gray-700 mt-2">{{ Str::limit($bill->summary, 200) }}</p>
                                        @endif
                                    </div>

                                    <form method="POST" action="{{ route('tags.detach') }}" onsubmit="return confirm('Remove this tag from the bill?')">
                                        @csrf
                                        <input type="hidden" name="tag_id" value="{{ $tag->id }}">
                                        <input type="hidden" name="bill_id" value="{{ $bill->id }}">
                                        <button type="submit" class="ml-4 px-3 py-1 text-sm bg-red-100 text-red-700 rounded hover:bg-red-200">
                                            Remove Tag
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
