<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                My Tags
            </h2>
            <button onclick="openCreateModal()" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                Create New Tag
            </button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if($tags->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center text-gray-500">
                        <p class="text-lg mb-2">You haven't created any tags yet</p>
                        <p>Tags help you organize and categorize bills.</p>
                        <button onclick="openCreateModal()" class="inline-block mt-4 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Create Your First Tag
                        </button>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($tags as $tag)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-4 h-4 rounded" style="background-color: {{ $tag->color }}"></div>
                                        <h3 class="text-lg font-semibold">{{ $tag->name }}</h3>
                                    </div>
                                    <span class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded">
                                        {{ $tag->bills_count }} {{ Str::plural('bill', $tag->bills_count) }}
                                    </span>
                                </div>

                                @if($tag->description)
                                    <p class="text-sm text-gray-600 mb-3">{{ $tag->description }}</p>
                                @endif

                                <div class="flex gap-2 mt-4">
                                    <a href="{{ route('tags.show', $tag) }}" class="flex-1 text-center px-3 py-2 text-sm bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200">
                                        View Bills
                                    </a>
                                    <button onclick="editTag({{ $tag->id }}, '{{ $tag->name }}', '{{ $tag->color }}', '{{ $tag->description }}')" class="px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('tags.destroy', $tag) }}" onsubmit="return confirm('Delete this tag? It will be removed from all bills.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-2 text-sm bg-red-100 text-red-700 rounded hover:bg-red-200">
                                            Delete
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

    <!-- Create/Edit Tag Modal (placeholder) -->
    <script>
        function openCreateModal() {
            // TODO: Implement with Alpine.js modal
            const name = prompt('Tag name:');
            if (!name) return;

            const color = prompt('Color (hex):', '#3b82f6');
            const description = prompt('Description (optional):');

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("tags.store") }}';
            form.innerHTML = `
                @csrf
                <input type="hidden" name="name" value="${name}">
                <input type="hidden" name="color" value="${color}">
                <input type="hidden" name="description" value="${description}">
            `;
            document.body.appendChild(form);
            form.submit();
        }

        function editTag(id, name, color, description) {
            // TODO: Implement with Alpine.js modal
            alert('Edit functionality will be implemented with Alpine.js modal');
        }
    </script>
</x-app-layout>
