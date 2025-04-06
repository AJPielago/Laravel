@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
    @if(session('checkout_success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            <strong class="font-bold">Order Placed Successfully!</strong>
            <p class="block sm:inline">
                Your order #{{ session('checkout_success')['order_id'] }} has been placed. 
                Total: ${{ number_format(session('checkout_success')['total'], 2) }} 
                ({{ session('checkout_success')['items'] }} items)
            </p>
        </div>
    @endif
    
    <div class="bg-white/95 backdrop-blur-sm shadow-2xl rounded-2xl p-8">
        <div class="flex items-center mb-8 pb-6 border-b border-gray-200">
            <div class="w-12 h-12 mr-4">
                @if(Auth::user()->photo)
                    <img src="{{ Storage::url(Auth::user()->photo) }}" 
                         alt="{{ Auth::user()->name }}" 
                         class="w-12 h-12 rounded-full object-cover">
                @else
                    <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                @endif
            </div>
            <div>
                <h2 class="text-3xl font-bold text-gray-800">Welcome back, {{ Auth::user()->name }}!</h2>
                <p class="text-gray-600">Here's what's happening with your account</p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 shadow-lg">
                <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <svg class="w-6 h-6 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Recent Orders
                </h3>
                @if($recentOrders->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentOrders as $order)
                            <div class="bg-white rounded-lg p-4 shadow-md hover:shadow-lg transition-shadow duration-300">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-600">Order #{{ $order->id }}</p>
                                        <p class="text-lg font-bold text-gray-800">${{ number_format($order->total, 2) }}</p>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-sm font-medium 
                                        {{ $order->status == 'completed' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <p class="text-gray-500 text-lg">No orders yet</p>
                        <p class="text-gray-400">Your order history will appear here</p>
                    </div>
                @endif
            </div>
            
            <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 shadow-lg">
                <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <svg class="w-6 h-6 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Quick Actions
                </h3>
                <div class="space-y-4">
                    <a href="{{ route('shop.index') }}" class="group block bg-gradient-to-r from-indigo-600 to-indigo-700 text-white p-4 rounded-xl hover:from-indigo-700 hover:to-indigo-800 transition duration-300 shadow-md hover:shadow-xl">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            <div>
                                <span class="block font-semibold">Browse Products</span>
                                <span class="text-indigo-200 text-sm">Explore our latest collection</span>
                            </div>
                        </div>
                    </a>
                    <a href="{{ route('cart.index') }}" class="group block bg-gradient-to-r from-purple-600 to-purple-700 text-white p-4 rounded-xl hover:from-purple-700 hover:to-purple-800 transition duration-300 shadow-md hover:shadow-xl">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <div>
                                <span class="block font-semibold">View Cart</span>
                                <span class="text-purple-200 text-sm">Check your shopping cart</span>
                            </div>
                        </div>
                    </a>
                    <a href="{{ route('orders.history') }}" class="group block bg-gradient-to-r from-blue-600 to-blue-700 text-white p-4 rounded-xl hover:from-blue-700 hover:to-blue-800 transition duration-300 shadow-md hover:shadow-xl">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <div>
                                <span class="block font-semibold">Order History</span>
                                <span class="text-blue-200 text-sm">View your past orders</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
