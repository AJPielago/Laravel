@php
    $currentRoute = Route::currentRouteName();
    $navLinks = [
        'dashboard' => 'dashboard',
        'products.index' => 'products.index', 
        'orders.index' => 'orders.index',
        'users.index' => 'users.index'
    ];
@endphp

<header class="bg-white shadow-md">
    <div class="container mx-auto px-4 py-4 flex justify-between items-center">
        <!-- Logo and Brand -->
        <div class="flex items-center space-x-4">
            <a href="{{ route('welcome') }}" class="flex items-center">
                <img src="{{ asset('logo.png') }}" alt="Lara Shop Logo" class="h-10 w-auto mr-2">
                <span class="text-2xl font-bold text-indigo-600 hover:text-indigo-700">
                    Lara Shop
                </span>
            </a>
        </div>

        <!-- Navigation Links -->
        <nav class="flex space-x-6">
    @auth
        @if(Auth::user()->hasRole('admin'))
            <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-indigo-600 
                {{ request()->routeIs('dashboard') ? 'text-indigo-600 font-semibold' : '' }}">
                Dashboard
            </a>
            <a href="{{ route('users.index') }}" class="text-gray-600 hover:text-indigo-600 
                {{ request()->routeIs('users.index') ? 'text-indigo-600 font-semibold' : '' }}">
                Users
            </a>
            <a href="{{ route('products.index') }}" class="text-gray-600 hover:text-indigo-600 
                {{ request()->routeIs('products.index') ? 'text-indigo-600 font-semibold' : '' }}">
                Products
            </a>
            <a href="{{ route('orders.index') }}" class="text-gray-600 hover:text-indigo-600 
                {{ request()->routeIs('orders.index') ? 'text-indigo-600 font-semibold' : '' }}">
                Orders
            </a>
            <a href="{{ route('shop.reviews') }}" class="text-gray-600 hover:text-indigo-600 
                {{ request()->routeIs('shop.reviews') ? 'text-indigo-600 font-semibold' : '' }}">
                Reviews
            </a>
        @else
            <a href="{{ route('customer.dashboard') }}" class="text-gray-600 hover:text-indigo-600 
                {{ request()->routeIs('customer.dashboard') ? 'text-indigo-600 font-semibold' : '' }}">
                Dashboard
            </a>
            <a href="{{ route('shop.index') }}" class="text-gray-600 hover:text-indigo-600 
                {{ request()->routeIs('shop.index') ? 'text-indigo-600 font-semibold' : '' }}">
                Products
            </a>
            <a href="{{ route('orders.history') }}" class="text-gray-600 hover:text-indigo-600 
                {{ request()->routeIs('customer.order-history') ? 'text-indigo-600 font-semibold' : '' }}">
                Orders
            </a>
            <a href="{{ route('cart.index') }}" class="relative text-gray-800 hover:text-indigo-600 transition">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
    </svg>
    <span id="cart-count" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
        {{ count(session('cart', [])) }}
    </span>
</a>
        @endif
    @endauth
</nav>

        <!-- User Menu -->
        <div class="flex items-center space-x-4">
            @auth
                <div class="relative group">
                    <button class="flex items-center space-x-2 focus:outline-none">
                        <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-indigo-200">
                            <img 
                                src="{{ auth()->user()->photo ? asset('storage/' . auth()->user()->photo) : asset('default-avatar.png') }}" 
                                alt="Profile" 
                                class="h-8 w-8 rounded-full object-cover"
                            >
                        </div>
                        <span class="text-gray-700 font-medium">
                            {{ Auth::user()->name }}
                        </span>
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg z-20 hidden group-focus-within:block">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50">
                            Edit Profile
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-indigo-50">
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="flex space-x-4">
                    <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-700">
                        Log in
                    </a>
                    <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                        Register
                    </a>
                </div>
            @endauth
        </div>
    </div>
</header>
