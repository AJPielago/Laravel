<x-app-layout>
    <div class="py-12">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 bg-white border-b border-gray-200">
                    <h2 class="text-xl font-bold mb-4 text-center">Add New Category</h2>

                    <form action="{{ route('categories.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="name" class="block text-xs font-medium text-gray-700 mb-1">Name</label>
                            <input type="text" name="name" id="name" 
                                   class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" 
                                   required>
                        </div>

                        <div>
                            <label for="description" class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" id="description" rows="2" 
                                      class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-medium py-2 px-4 rounded-md transition-colors">
                                Create Category
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Manually trigger notification if there are any form validation errors
            @if($errors->any())
                window.showNotification('{{ $errors->first() }}', 'error');
            @endif
        });
    </script>
    @endpush
</x-app-layout>
