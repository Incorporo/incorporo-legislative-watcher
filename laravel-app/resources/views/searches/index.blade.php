<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Saved Searches
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Sort Options -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('searches.index') }}" class="flex gap-4">
                        <select name="sort" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="recently_used" {{ request('sort') === 'recently_used' ? 'selected' : '' }}>Recently Used</option>
                            <option value="most_used" {{ request('sort') === 'most_used' ? 'selected' : '' }}>Most Used</option>
                            <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Name (A-Z)</option>
                        </select>

                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Apply Sort
                        </button>
                    </form>
                </div>
            </div>

            @if($savedSearches->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center text-gray-500">
                        <p class="text-lg mb-2">You haven't saved any searches yet</p>
                        <p>Save your frequently used search filters for quick access.</p>
                        <a href="{{ route('bills.index') }}" class="inline-block mt-4 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Browse Bills
                        </a>
                    </div>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($savedSearches as $search)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <h3 class="text-lg font-semibold">{{ $search->name }}</h3>
                                            @if($search->is_default)
                                                <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded font-semibold">DEFAULT</span>
                                            @endif
                                        </div>

                                        <div class="text-sm text-gray-600 mb-3">
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($search->filters as $key => $value)
                                                    @if($value)
                                                        <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded">
                                                            <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ is_array($value) ? implode(', ', $value) : $value }}
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-4 text-sm text-gray-500">
                                            <span>Used {{ $search->use_count }} {{ Str::plural('time', $search->use_count) }}</span>
                                            @if($search->last_used_at)
                                                <span>Last used {{ $search->last_used_at->diffForHumans() }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="ml-4 flex flex-col gap-2">
                                        <a href="{{ route('searches.apply', $search) }}" class="px-3 py-2 text-sm bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200 text-center">
                                            Apply Search
                                        </a>
                                        @if(!$search->is_default)
                                            <form method="POST" action="{{ route('searches.setDefault', $search) }}">
                                                @csrf
                                                <button type="submit" class="w-full px-3 py-2 text-sm bg-green-100 text-green-700 rounded hover:bg-green-200">
                                                    Set as Default
                                                </button>
                                            </form>
                                        @endif
                                        <button
                                            onclick="editSearch({{ $search->id }})"
                                            class="px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
                                            Edit
                                        </button>
                                        <form method="POST" action="{{ route('searches.destroy', $search) }}" onsubmit="return confirm('Delete this saved search?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full px-3 py-2 text-sm bg-red-100 text-red-700 rounded hover:bg-red-200">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <script>
        function editSearch(id) {
            // TODO: Implement edit modal
            alert('Edit functionality will be implemented with Alpine.js modal');
        }
    </script>
</x-app-layout>
