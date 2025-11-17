<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            My Notes
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
                    <form method="GET" action="{{ route('notes.index') }}" class="flex gap-4">
                        <select name="privacy" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All Notes</option>
                            <option value="private" {{ request('privacy') === 'private' ? 'selected' : '' }}>Private Only</option>
                            <option value="public" {{ request('privacy') === 'public' ? 'selected' : '' }}>Public Only</option>
                        </select>

                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Apply Filter
                        </button>
                    </form>
                </div>
            </div>

            @if($notes->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center text-gray-500">
                        <p class="text-lg mb-2">You haven't created any notes yet</p>
                        <p>Add notes to bills to keep track of your thoughts and analysis.</p>
                        <a href="{{ route('bills.index') }}" class="inline-block mt-4 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Browse Bills
                        </a>
                    </div>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($notes as $note)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <h3 class="text-lg font-semibold">
                                                <a href="{{ route('bills.show', $note->bill) }}" class="text-indigo-600 hover:text-indigo-800">
                                                    {{ $note->bill->title }}
                                                </a>
                                            </h3>
                                            @if($note->is_private)
                                                <span class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded">Private</span>
                                            @else
                                                <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded">Public</span>
                                            @endif
                                        </div>

                                        <div class="text-sm text-gray-600 mb-3">
                                            <span class="font-medium">{{ $note->bill->bill_number }}</span> &middot;
                                            <span>{{ $note->created_at->format('M d, Y') }}</span>
                                        </div>

                                        <div class="p-4 bg-gray-50 rounded">
                                            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $note->content }}</p>
                                        </div>
                                    </div>

                                    <div class="ml-4 flex flex-col gap-2">
                                        <button
                                            onclick="editNote({{ $note->id }})"
                                            class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
                                            Edit
                                        </button>
                                        <form method="POST" action="{{ route('notes.destroy', $note) }}" onsubmit="return confirm('Delete this note?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full px-3 py-1 text-sm bg-red-100 text-red-700 rounded hover:bg-red-200">
                                                Delete
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
                    {{ $notes->links() }}
                </div>
            @endif
        </div>
    </div>

    <script>
        function editNote(id) {
            // NOTE: Edit modal can be implemented using Alpine.js pattern from tags/index.blade.php
            // For now, navigate to note detail or bills page to edit
            alert('Click on a bill to view and edit notes');
        }
    </script>
</x-app-layout>
