<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                My Tags
            </h2>
            <button @click="$dispatch('show-create-tag-modal')" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
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

    <!-- Create Tag Modal -->
    <div x-data="{ showModal: false, tag: { name: '', color: '#3b82f6', description: '' } }"
         @show-create-tag-modal.window="showModal = true"
         x-cloak>
        <!-- Trigger handled by button click setting showModal = true -->

        <!-- Modal Overlay -->
        <div x-show="showModal"
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-title"
             role="dialog"
             aria-modal="true"
             @keydown.escape.window="showModal = false">

            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div x-show="showModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                     @click="showModal = false"></div>

                <!-- Center modal -->
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal panel -->
                <div x-show="showModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">

                    <form method="POST" action="{{ route('tags.store') }}">
                        @csrf
                        <div>
                            <div class="mt-3 text-center sm:mt-0 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Create New Tag
                                </h3>
                                <div class="mt-6 space-y-4">
                                    <!-- Tag Name -->
                                    <div>
                                        <label for="tag-name" class="block text-sm font-medium text-gray-700">
                                            Tag Name <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text"
                                               name="name"
                                               id="tag-name"
                                               x-model="tag.name"
                                               required
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                               placeholder="e.g., Priority Bills">
                                    </div>

                                    <!-- Color Picker -->
                                    <div>
                                        <label for="tag-color" class="block text-sm font-medium text-gray-700">
                                            Color
                                        </label>
                                        <div class="mt-1 flex items-center space-x-2">
                                            <input type="color"
                                                   name="color"
                                                   id="tag-color"
                                                   x-model="tag.color"
                                                   class="h-10 w-20 border-gray-300 rounded">
                                            <input type="text"
                                                   x-model="tag.color"
                                                   class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                   placeholder="#3b82f6">
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    <div>
                                        <label for="tag-description" class="block text-sm font-medium text-gray-700">
                                            Description
                                        </label>
                                        <textarea name="description"
                                                  id="tag-description"
                                                  x-model="tag.description"
                                                  rows="3"
                                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                  placeholder="Optional description for this tag"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Create Tag
                            </button>
                            <button type="button"
                                    @click="showModal = false; tag = { name: '', color: '#3b82f6', description: '' }"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <script>
        // Alpine.js component for tag manager
        document.addEventListener('alpine:init', () => {
            Alpine.data('tagManager', () => ({
                init() {
                    // Initialization logic if needed
                }
            }));
        });

        function editTag(id, name, color, description) {
            // For now, use a simple approach - could be enhanced with a separate edit modal
            if (confirm('Edit tag: ' + name + '?')) {
                const newName = prompt('Tag name:', name);
                if (!newName) return;

                const newColor = prompt('Color (hex):', color);
                const newDescription = prompt('Description:', description || '');

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/tags/${id}`;
                form.innerHTML = `
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="name" value="${newName}">
                    <input type="hidden" name="color" value="${newColor}">
                    <input type="hidden" name="description" value="${newDescription}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</x-app-layout>
