<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            My Watchlist
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('watchlist.index') }}" class="flex gap-4">
                        <select name="priority" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All Priorities</option>
                            <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High Priority</option>
                            <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>Normal Priority</option>
                            <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low Priority</option>
                        </select>

                        <select name="sort" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="created_at" {{ request('sort') === 'created_at' ? 'selected' : '' }}>Recently Added</option>
                            <option value="priority" {{ request('sort') === 'priority' ? 'selected' : '' }}>Priority</option>
                            <option value="updated_at" {{ request('sort') === 'updated_at' ? 'selected' : '' }}>Recently Updated</option>
                        </select>

                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Apply Filters
                        </button>
                    </form>
                </div>
            </div>

            <!-- Watchlist Items -->
            @if($watchedBills->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center text-gray-500">
                        <p class="text-lg mb-2">Your watchlist is empty</p>
                        <p>Start adding bills to keep track of legislation that matters to you.</p>
                        <a href="{{ route('bills.index') }}" class="inline-block mt-4 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Browse Bills
                        </a>
                    </div>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($watchedBills as $watchlistItem)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <h3 class="text-lg font-semibold">
                                                <a href="{{ route('bills.show', $watchlistItem->bill) }}" class="text-indigo-600 hover:text-indigo-800">
                                                    {{ $watchlistItem->bill->title }}
                                                </a>
                                            </h3>

                                            <!-- Priority Badge -->
                                            @if($watchlistItem->priority === 'high')
                                                <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded">HIGH</span>
                                            @elseif($watchlistItem->priority === 'low')
                                                <span class="px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-800 rounded">LOW</span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded">NORMAL</span>
                                            @endif
                                        </div>

                                        <div class="text-sm text-gray-600 mb-2">
                                            <span class="font-medium">{{ $watchlistItem->bill->bill_number }}</span> &middot;
                                            <span>{{ ucfirst($watchlistItem->bill->chamber) }}</span> &middot;
                                            <span>{{ ucfirst(str_replace('_', ' ', $watchlistItem->bill->status)) }}</span>
                                        </div>

                                        @if($watchlistItem->personal_note)
                                            <div class="mt-2 p-3 bg-gray-50 rounded text-sm">
                                                <strong>Note:</strong> {{ $watchlistItem->personal_note }}
                                            </div>
                                        @endif

                                        <div class="mt-3 flex items-center gap-4 text-sm text-gray-500">
                                            <span>
                                                Notifications:
                                                <span class="font-medium {{ $watchlistItem->notifications_enabled ? 'text-green-600' : 'text-gray-600' }}">
                                                    {{ $watchlistItem->notifications_enabled ? 'Enabled' : 'Disabled' }}
                                                </span>
                                            </span>
                                            <span>Added {{ $watchlistItem->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>

                                    <div class="ml-4 flex flex-col gap-2">
                                        <button
                                            onclick="editWatchlist({{ $watchlistItem->id }})"
                                            class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
                                            Edit
                                        </button>
                                        <form method="POST" action="{{ route('watchlist.destroy', $watchlistItem) }}" onsubmit="return confirm('Remove this bill from your watchlist?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full px-3 py-1 text-sm bg-red-100 text-red-700 rounded hover:bg-red-200">
                                                Remove
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $watchedBills->links() }}
                </div>
            @endif
        </div>
    </div>

    <script>
        function editWatchlist(id) {
            // TODO: Implement edit modal
            alert('Edit functionality will be implemented with Alpine.js modal');
        }
    </script>
</x-app-layout>
