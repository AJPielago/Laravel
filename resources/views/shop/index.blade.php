@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col space-y-4 mb-8">
        <!-- Search Bar -->
        <div class="w-full">
            <form action="{{ route('shop.index') }}" method="GET" class="relative">
                <input type="text" 
                       name="search" 
                       placeholder="Search products..." 
                       value="{{ request('search') }}"
                       class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                <!-- Preserve other filter parameters -->
                @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                @if(request('min_price'))
                    <input type="hidden" name="min_price" value="{{ request('min_price') }}">
                @endif
                @if(request('max_price'))
                    <input type="hidden" name="max_price" value="{{ request('max_price') }}">
                @endif
                <span class="absolute left-3 top-2.5 text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </span>
            </form>
        </div>

        <!-- Existing Filters -->
        <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
            <!-- Filtering Form -->
            <form action="{{ route('shop.index') }}" method="GET" class="flex flex-wrap items-center space-x-4">
                <!-- Category Filter -->
                <div>
                    <select id="category-filter" name="category" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Categories</option>
                        @foreach($categories as $categoryName)
                            <option value="{{ $categoryName }}" {{ request('category') == $categoryName ? 'selected' : '' }}>
                                {{ $categoryName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Price Range Filter -->
                <div class="flex items-center space-x-2">
                    <input type="number" name="min_price" placeholder="Min Price" 
                           value="{{ request('min_price') }}"
                           class="w-24 px-2 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                    <span>-</span>
                    <input type="number" name="max_price" placeholder="Max Price" 
                           value="{{ request('max_price') }}"
                           class="w-24 px-2 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition">
                    Filter
                </button>

                <!-- Reset Button -->
                <a href="{{ route('shop.index') }}" 
                   class="px-4 py-2 bg-gray-200 text-gray-800 rounded-xl hover:bg-gray-300 transition">
                    Reset
                </a>
            </form>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @forelse($products as $product)
            <div class="bg-white rounded-xl shadow-lg overflow-hidden transform transition hover:scale-105 hover:shadow-xl flex flex-col h-[420px]">
                <!-- Product Image - Fixed Height -->
                <div class="w-full h-48 bg-gray-100">
                    @if($product->first_photo)
                        <img src="{{ Storage::url($product->first_photo) }}" 
                             alt="{{ $product->name }}" 
                             class="w-full h-full object-contain">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    @endif
                </div>
                
                <!-- Product Details - Fixed Layout -->
                <div class="p-4 flex-grow flex flex-col">
                    <h3 class="font-bold text-lg mb-2 line-clamp-2 min-h-[3.5rem]">{{ $product->name }}</h3>
                    
                    <div class="mt-auto space-y-4">
                        <!-- Category Badge -->
                        <div class="h-6">
                            @if($product->category)
                                <span class="inline-block bg-indigo-100 text-indigo-800 text-xs px-2 py-1 rounded-full">
                                    {{ $product->category->name }}
                                </span>
                            @endif
                        </div>

                        <!-- Price and Cart Button -->
                        <div class="flex items-center justify-between">
                            <span class="text-xl font-bold text-indigo-600">${{ number_format($product->price, 2) }}</span>
                            
                            @if($product->stock > 0)
                                <form id="addToCartForm-{{ $product->id }}" 
                                      action="{{ route('cart.add', $product) }}" 
                                      method="POST" 
                                      class="inline-block"
                                      onsubmit="event.preventDefault(); addToCart({{ $product->id }}, '{{ $product->name }}', {{ $product->price }});">
                                    @csrf
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" 
                                            class="bg-indigo-500 text-white px-3 py-1 rounded-lg hover:bg-green-600 transition text-sm">
                                        Add to Cart
                                    </button>
                                </form>
                            @else
                                <span class="text-red-500 text-sm">Out of Stock</span>
                            @endif
                        </div>

                        <!-- View Details Button -->
                        <a href="{{ route('shop.show', $product) }}" 
                           class="w-full block text-center bg-indigo-600 text-white py-2 rounded-xl hover:bg-indigo-700 transition">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-8">
                <p class="text-gray-500 text-xl">No products found.</p>
                <a href="{{ route('shop.index') }}" 
                   class="mt-4 inline-block px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition">
                    Reset Filters
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $products->appends(request()->input())->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
function addToCart(productId, productName, productPrice) {
    const form = document.getElementById('addToCartForm-' + productId);
    const formData = new FormData(form);
    
    // Show loading state
    const toast = Swal.fire({
        title: 'Adding to Cart...',
        text: productName + ' - $' + productPrice,
        icon: 'info',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Submit the form via fetch
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw new Error(err.message || 'Network response was not ok'); });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Added to Cart!',
                text: data.message,
                icon: 'success',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: 'linear-gradient(to right, #6366f1, #a855f7)',
                color: '#ffffff',
                iconColor: '#ffffff',
                customClass: {
                    popup: 'rounded-lg shadow-xl'
                }
            });
            
            // Update cart count
            const cartCountEl = document.getElementById('cart-count');
            if (cartCountEl) {
                cartCountEl.textContent = data.cart_count || (parseInt(cartCountEl.textContent) + 1);
            }
        } else {
            throw new Error(data.message || 'Failed to add item to cart');
        }
    })
    .catch(error => {
        Swal.fire({
            title: 'Error',
            text: error.message || 'Failed to add item to cart. Please try again.',
            icon: 'error',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            background: 'linear-gradient(to right, #ff6b6b, #ff4757)',
            color: '#ffffff',
            iconColor: '#ffffff',
            customClass: {
                popup: 'rounded-lg shadow-xl'
            }
        });
        console.error('Error:', error);
    });
}
</script>
@endpush