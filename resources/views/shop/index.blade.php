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
    <header class="bg-white shadow-md py-4">
        <div class="container mx-auto flex justify-between items-center px-6">
            <div class="flex items-center space-x-4">
                <a href="{{ route('welcome') }}" class="flex items-center">
                    <img src="{{ asset('logo.png') }}" alt="Lara Shop Logo" class="h-10 w-auto">
                </a>
                <h1 class="text-2xl font-bold text-indigo-600 hidden md:block">Lara Shop</h1>
            </div>
            <nav class="space-x-4 flex items-center">
                <a href="{{ route('dashboard') }}" class="text-gray-800 hover:text-indigo-600 transition">Dashboard</a>
                <a href="{{ route('shop.index') }}" class="text-gray-800 hover:text-indigo-600 transition">Products</a>
                <a href="{{ route('cart.index') }}" class="relative text-gray-800 hover:text-indigo-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span id="cart-count" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                        {{ count(session('cart', [])) }}
                    </span>
                </a>
                @auth
                    <a href="{{ route('orders.history') }}" class="text-gray-800 hover:text-indigo-600 transition">Orders</a>
                    <a href="{{ route('profile.edit') }}" class="flex items-center space-x-2">
                        @if(Auth::user()->photo)
                            <img src="{{ Storage::url(Auth::user()->photo) }}" 
                                 alt="{{ Auth::user()->name }}" 
                                 class="w-8 h-8 rounded-full object-cover border-2 border-indigo-200">
                        @else
                            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                        @endif
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-indigo-600 hover:text-indigo-800 font-semibold">Logout</button>
                    </form>
                @endauth
            </nav>
        </div>
    </header>

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
                            
                            <div class="flex justify-between items-center">
                                <span class="text-xl font-bold text-indigo-600">${{ number_format($product->price, 2) }}</span>
                                <span class="text-sm text-gray-500">Stock: {{ $product->stock }}</span>
                            </div>
                            
                            <div class="mt-4">
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