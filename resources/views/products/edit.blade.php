@extends('layouts.app')

@section('content')
@auth
    @if(auth()->user()->role === 'admin')
        <div class="bg-white shadow-lg rounded-lg p-8 max-w-4xl mx-auto">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <h1 class="text-2xl sm:text-3xl font-bold text-indigo-600">Edit Product</h1>
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

            <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Product Name</label>
                        <input type="text" name="name" id="name" 
                               value="{{ old('name', $product->name) }}" 
                               required
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-colors duration-200">
                    </div>

                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-2">Price</label>
                        <input type="number" name="price" id="price" 
                               step="0.01" 
                               value="{{ old('price', $product->price) }}" 
                               required
                               min="0"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-colors duration-200">
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="description" 
                              rows="4"
                              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-colors duration-200">{{ old('description', $product->description) }}</textarea>
                </div>

                <div>
                    <label for="stock" class="block text-sm font-medium text-gray-700 mb-2">Stock Quantity</label>
                    <input type="number" name="stock" id="stock" 
                           value="{{ old('stock', $product->stock) }}" 
                           required
                           min="0"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-colors duration-200">
                </div>

                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select name="category" id="category" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                        <option value="">Select Category</option>
                        <option value="Pencils" {{ $product->category == 'Pencils' ? 'selected' : '' }}>Pencils</option>
                        <option value="Papers" {{ $product->category == 'Papers' ? 'selected' : '' }}>Papers</option>
                        <option value="Accessories" {{ $product->category == 'Accessories' ? 'selected' : '' }}>Accessories</option>
                        <option value="Boards" {{ $product->category == 'Boards' ? 'selected' : '' }}>Boards</option>
                        <option value="Colors" {{ $product->category == 'Colors' ? 'selected' : '' }}>Colors</option>
                        <option value="Erasers" {{ $product->category == 'Erasers' ? 'selected' : '' }}>Erasers</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Product Photos</label>
                    
                    <!-- Existing Photos -->
                    <div id="existing-photos" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mb-4">
                        @php
                            $existingPhotos = $product->photos ?? [];
                        @endphp
                        @forelse($existingPhotos as $index => $photoUrl)
                            <div class="relative group border rounded-lg p-2" data-photo-url="{{ $photoUrl }}">
                                <img src="{{ asset('storage/' . $photoUrl) }}" 
                                     alt="Product Photo {{ $index + 1 }}" 
                                     class="w-full h-40 object-cover rounded-lg">
                                <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-lg">
                                    <button type="button" 
                                            onclick="removePhoto('{{ $photoUrl }}')" 
                                            class="bg-red-500 text-white p-2 rounded-full hover:bg-red-600 transition-colors duration-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center text-gray-500 py-4 border border-dashed border-gray-300 rounded-lg">
                                No photos uploaded for this product
                            </div>
                        @endforelse
                    </div>

                    <!-- Hidden Inputs for Photo Management -->
                    <input type="hidden" 
                           id="existing-photos-input" 
                           name="existing_photos" 
                           value="{{ json_encode($product->photos ?? []) }}">
                    <input type="hidden" 
                           id="deleted-photos-input" 
                           name="deleted_photos" 
                           value="[]">

                    <!-- Photo Upload -->
                    <div class="mb-4">
                        <label for="photo-upload" class="block text-gray-700 text-sm font-bold mb-2">
                            Upload Additional Photos
                        </label>
                        <input type="file" 
                               id="photo-upload" 
                               name="photos[]" 
                               multiple 
                               accept="image/jpeg,image/png,image/jpg,image/gif" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <!-- Photo Preview Section -->
                    <div id="photo-preview" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 mt-4 hidden"></div>
                </div>

                <div>
                    <button type="submit" 
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                        Update Product
                    </button>
                </div>
            </form>
        </div>
    @else
        <div class="bg-white shadow-lg rounded-lg p-8 max-w-2xl mx-auto">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Access Denied</strong>
                <span class="block sm:inline">You don't have permission to edit products.</span>
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
    // Parse existing photos from the hidden input
    let existingPhotosInput = document.getElementById('existing-photos-input');
    let deletedPhotosInput = document.getElementById('deleted-photos-input');
    
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

    // Photo deletion function
    function removePhoto(photoUrl) {
        if(confirm('Are you sure you want to delete this photo?')) {
            // Get existing photos
            let existingPhotos = JSON.parse(existingPhotosInput.value || '[]');
            
            // Filter out the deleted photo
            const newPhotos = existingPhotos.filter(url => url !== photoUrl);
            
            // Update the existing photos input
            existingPhotosInput.value = JSON.stringify(newPhotos);
            
            // Get deleted photos input
            let deletedPhotos = JSON.parse(deletedPhotosInput.value || '[]');
            
            // Add this photo to deleted photos if not already there
            if (!deletedPhotos.includes(photoUrl)) {
                deletedPhotos.push(photoUrl);
            }
            
            // Update the deleted photos input
            deletedPhotosInput.value = JSON.stringify(deletedPhotos);
            
            // Remove the photo from the DOM
            const photoElement = document.querySelector(`[data-photo-url="${CSS.escape(photoUrl)}"]`);
            if (photoElement) {
                photoElement.remove();
            }
            
            // If no photos left, show the empty message
            const existingPhotosContainer = document.getElementById('existing-photos');
            if (existingPhotosContainer.querySelectorAll('[data-photo-url]').length === 0) {
                existingPhotosContainer.innerHTML = `
                    <div class="col-span-full text-center text-gray-500 py-4 border border-dashed border-gray-300 rounded-lg">
                        No photos uploaded for this product
                    </div>
                `;
            }
        }
    }

    // Photo upload preview
    document.getElementById('photo-upload').addEventListener('change', function(e) {
        const previewContainer = document.getElementById('photo-preview');
        previewContainer.innerHTML = '';
        
        if(this.files.length > 0) {
            previewContainer.classList.remove('hidden');
            
            Array.from(this.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const div = document.createElement('div');
                    div.className = 'relative border rounded-lg p-2';
                    
                    const img = document.createElement('img');
                    img.src = event.target.result;
                    img.className = 'w-full h-32 object-cover rounded-lg';
                    
                    div.appendChild(img);
                    previewContainer.appendChild(div);
                }
                reader.readAsDataURL(file);
            });
        } else {
            previewContainer.classList.add('hidden');
        }
    });
</script>
@endpush