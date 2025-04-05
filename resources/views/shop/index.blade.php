<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - Lara Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 text-gray-900">
@include('layouts.header')

    <main class="min-h-[calc(100vh-8rem)] bg-gradient-to-r from-indigo-500 to-purple-500 py-12">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4 mb-8">
                <!-- Filtering Form -->
                <form action="{{ route('shop.index') }}" method="GET" class="flex flex-wrap items-center space-x-4">
                    <!-- Category Filter -->
                    <div>
                        <select name="category" class="px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                            <option value="">All Categories</option>
                            <option value="Pencils" {{ request('category') == 'Pencils' ? 'selected' : '' }}>Pencils</option>
                            <option value="Papers" {{ request('category') == 'Papers' ? 'selected' : '' }}>Papers</option>
                            <option value="Accessories" {{ request('category') == 'Accessories' ? 'selected' : '' }}>Accessories</option>
                            <option value="Boards" {{ request('category') == 'Boards' ? 'selected' : '' }}>Boards</option>
                            <option value="Colors" {{ request('category') == 'Colors' ? 'selected' : '' }}>Colors</option>
                            <option value="Erasers" {{ request('category') == 'Erasers' ? 'selected' : '' }}>Erasers</option>
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

            <!-- Products Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @forelse($products as $product)
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden transform transition hover:scale-105 hover:shadow-xl">
                        <!-- Product Card Content -->
                        @if($product->first_photo)
                            <img src="{{ Storage::url($product->first_photo) }}" 
                                 alt="{{ $product->name }}" 
                                 class="w-full h-48 object-cover">
                        @endif
                        
                        <div class="p-4">
                            <h3 class="font-bold text-lg mb-2">{{ $product->name }}</h3>
                            
                            @if($product->category)
                                <span class="inline-block bg-indigo-100 text-indigo-800 text-xs px-2 py-1 rounded-full mb-2">
                                    {{ $product->category }}
                                </span>
                            @endif
                            
                            <div class="flex items-center justify-between mt-2">
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
                        </div>
                        
                        <div class="mt-4">
                            <a href="{{ route('shop.show', $product) }}" 
                               class="w-full block text-center bg-indigo-600 text-white py-2 rounded-xl hover:bg-indigo-700 transition">
                                View Details
                            </a>
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
    </main>

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

    <footer class="bg-purple-50 text-gray-700 text-center py-6 border-t border-cyan-100">
        <p>&copy; 2025 Lara Shop. All rights reserved.</p>
    </footer>
</body>
</html>