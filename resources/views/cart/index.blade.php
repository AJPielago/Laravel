<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - Lara Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                <a href="{{ route('shop.index') }}" class="text-gray-800 hover:text-indigo-600 transition">Shop</a>
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
                @else
                    <a href="{{ route('login') }}" class="text-gray-800 hover:text-indigo-600 transition">Login</a>
                    <a href="{{ route('register') }}" class="text-gray-800 hover:text-indigo-600 transition">Register</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="min-h-[calc(100vh-8rem)] bg-gradient-to-r from-indigo-500 to-purple-500 py-12">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-lg p-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-indigo-600">Shopping Cart</h1>
                        <p class="mt-2 text-gray-600">Review your items below.</p>
                    </div>
                </div>

                @if(empty($cart))
                    <p class="text-gray-500">Your cart is empty</p>
                    <a href="{{ route('shop.index') }}" class="mt-4 inline-block bg-indigo-600 text-white px-6 py-3 rounded-full hover:bg-indigo-700 transition duration-300">
                        Continue Shopping
                    </a>
                @else
                    <div class="space-y-4">
                        @foreach($cart as $id => $details)
                            <div class="flex items-center justify-between border-b pb-4">
                                <div class="flex items-center space-x-4">
                                    @if($details['image'])
                                        <img src="{{ Storage::url($details['image']) }}" 
                                             alt="{{ $details['name'] }}" 
                                             class="w-16 h-16 object-cover rounded-lg shadow">
                                    @endif
                                    <div>
                                        <h3 class="font-semibold text-lg">{{ $details['name'] }}</h3>
                                        <p class="text-gray-600">${{ number_format($details['price'], 2) }} x {{ $details['quantity'] }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <form action="{{ route('cart.remove', $id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-500 hover:text-red-700 transition"
                                                onclick="event.preventDefault(); 
                                                         if(confirm('Are you sure you want to remove this item from your cart?')) {
                                                             this.closest('form').submit();
                                                         }">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                        
                        <div class="mt-6 flex justify-between items-center">
                            <p class="text-2xl font-bold text-indigo-600">Total: ${{ number_format($total, 2) }}</p>
                            <a href="{{ route('cart.checkout') }}" 
                               class="bg-indigo-600 text-white px-8 py-3 rounded-full hover:bg-indigo-700 transition duration-300 shadow-lg">
                                Proceed to Checkout
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </main>

    <footer class="bg-white text-gray-700 text-center py-6 border-t border-gray-200">
        <p>&copy; 2025 Lara Shop. All rights reserved.</p>
    </footer>
</body>
</html>
