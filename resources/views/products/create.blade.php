@extends('layouts.app')

@section('content')
@auth
    @if(auth()->user()->role === 'admin')
        <div class="bg-white shadow-lg rounded-lg p-8 max-w-4xl mx-auto">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <h1 class="text-2xl sm:text-3xl font-bold text-indigo-600">Add Product</h1>
                <a href="{{ route('products.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200">
                    ← Back to Products
                </a>
            </div>

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Product Name</label>
                        <input type="text" name="name" id="name" 
                               value="{{ old('name') }}" 
                               required
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-colors duration-200">
                    </div>

                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-2">Price</label>
                        <input type="number" name="price" id="price" 
                               step="0.01" 
                               value="{{ old('price') }}" 
                               required
                               min="0"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-colors duration-200">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select name="category_id" id="category_id" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition {{ $errors->has('category_id') ? 'border-red-500' : '' }}">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="description" 
                              rows="4"
                              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-colors duration-200">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label for="stock" class="block text-sm font-medium text-gray-700 mb-2">Stock Quantity</label>
                    <input type="number" name="stock" id="stock" 
                           value="{{ old('stock') }}" 
                           required
                           min="0"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-colors duration-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Product Photos</label>
                    
                    <div class="flex items-center justify-center w-full">
                        <label for="photo-upload" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition duration-300">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-8 h-8 mb-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L7 8m3-2 3 2"/>
                                </svg>
                                <p class="mb-2 text-sm text-gray-500">
                                    <span class="font-semibold">Click to upload</span> or drag and drop
                                </p>
                                <p class="text-xs text-gray-500">PNG, JPG (MAX. 10 photos per upload)</p>
                            </div>
                            <input id="photo-upload" type="file" name="photos[]" multiple accept=".jpg,.jpeg,.png" class="hidden" />
                        </label>
                    </div>

                    <div id="photo-preview" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                        <!-- Preview images will appear here -->
                    </div>
                    
                    <!-- Hidden container to store the files between uploads -->
                    <div id="file-storage" class="hidden"></div>
                </div>

                <div class="flex justify-end space-x-4 mt-6">
                    <a href="{{ route('products.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                        Add Product
                    </button>
                </div>
            </form>
        </div>
    @else
        <div class="bg-white shadow-lg rounded-lg p-8 max-w-2xl mx-auto">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Access Denied</strong>
                <span class="block sm:inline">You don't have permission to add products.</span>
            </div>
        </div>
    @endif
@else
    <div class="bg-white shadow-lg rounded-lg p-8 max-w-2xl mx-auto">
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Authentication Required</strong>
            <span class="block sm:inline">Please <a href="{{ route('login') }}" class="text-red-800 hover:text-red-900 underline">log in</a> to access this page.</span>
        </div>
    </div>
@endauth
@endsection

@push('scripts')
<script>
    // Store all selected files
    let selectedFiles = [];
    
    document.getElementById('photo-upload').addEventListener('change', function(e) {
        const previewContainer = document.getElementById('photo-preview');
        const fileStorage = document.getElementById('file-storage');
        
        // Add new files to our storage
        Array.from(this.files).forEach(file => {
            // Check if file already exists
            const fileExists = selectedFiles.some(
                existingFile => existingFile.name === file.name && existingFile.size === file.size
            );
            
            if (!fileExists) {
                selectedFiles.push(file);
                
                // Create a preview element
                const reader = new FileReader();
                reader.onload = function(event) {
                    const div = document.createElement('div');
                    div.className = 'relative group';
                    
                    const img = document.createElement('img');
                    img.src = event.target.result;
                    img.className = 'w-full h-32 object-cover rounded-lg shadow-md';
                    
                    // Add remove button
                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200';
                    removeBtn.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    `;
                    removeBtn.addEventListener('click', () => removePhoto(file));
                    
                    div.appendChild(img);
                    div.appendChild(removeBtn);
                    previewContainer.appendChild(div);
                };
                reader.readAsDataURL(file);
                
                // Create a hidden input for the file (for form submission)
                const fileInput = document.createElement('input');
                fileInput.type = 'file';
                fileInput.name = 'photos[]';
                fileInput.multiple = true;
                
                // Create a DataTransfer object to store our files
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                fileInput.files = dataTransfer.files;
                
                fileStorage.appendChild(fileInput);
            }
        });
        
        // Reset the file input to allow selecting the same files again
        this.value = '';
    });
    
    function removePhoto(file) {
        if (confirm('Are you sure you want to remove this photo?')) {
            // Remove from selectedFiles array
            selectedFiles = selectedFiles.filter(
                existingFile => !(existingFile.name === file.name && existingFile.size === file.size)
            );
            
            // Remove from DOM
            const previewContainer = document.getElementById('photo-preview');
            Array.from(previewContainer.children).forEach(child => {
                if (child.querySelector('img')?.src.includes(file.name)) {
                    child.remove();
                }
            });
            
            // Remove from file storage
            const fileStorage = document.getElementById('file-storage');
            Array.from(fileStorage.children).forEach(input => {
                if (input.files[0]?.name === file.name) {
                    input.remove();
                }
            });
        }
    }

    // Price input custom handling
    const priceInput = document.getElementById('price');
    
    // Custom increment/decrement handling
    priceInput.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
            e.preventDefault();
            
            // Get current value, default to 0 if empty
            let currentValue = parseFloat(this.value) || 0;
            
            // Increment/decrement by 1
            if (e.key === 'ArrowUp') {
                currentValue += 1;
            } else {
                currentValue = Math.max(0, currentValue - 1);
            }
            
            // Set the new value, rounded to 2 decimal places
            this.value = currentValue.toFixed(2);
        }
    });

    // Prevent mousewheel from changing value
    priceInput.addEventListener('wheel', function(e) {
        e.preventDefault();
    });

    // Prevent invalid characters
    priceInput.addEventListener('keypress', function(e) {
        // Allow numbers, backspace, delete, tab, etc.
        if (e.key === 'e' || e.key === '+' || e.key === '-') {
            e.preventDefault();
        }
    });
</script>
@endpush