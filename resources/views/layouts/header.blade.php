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
        <!-- Logo -->
        <a href="{{ route('welcome') }}" class="text-2xl font-bold text-indigo-600 hover:text-indigo-700">
            Lara Shop
        </a>

        <!-- Navigation Links -->
        <nav class="flex space-x-6">
            @auth
                @if(Auth::user()->hasRole('admin'))
                    <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-indigo-600 
                        {{ request()->routeIs('dashboard') ? 'text-indigo-600 font-semibold' : '' }}">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('customer.dashboard') }}" class="text-gray-600 hover:text-indigo-600 
                        {{ request()->routeIs('customer.dashboard') ? 'text-indigo-600 font-semibold' : '' }}">
                        Dashboard
                    </a>
                @endif
                <a href="{{ route('products.index') }}" class="text-gray-600 hover:text-indigo-600 
                    {{ request()->routeIs('products.index') ? 'text-indigo-600 font-semibold' : '' }}">
                    Products
                </a>
                <a href="{{ route('orders.history') }}" class="text-gray-600 hover:text-indigo-600 
                    {{ request()->routeIs('orders.history') ? 'text-indigo-600 font-semibold' : '' }}">
                    Orders
                </a>
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
