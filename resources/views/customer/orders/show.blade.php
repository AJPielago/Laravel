@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white shadow-xl rounded-lg overflow-hidden">
        <div class="p-6 sm:p-8">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-indigo-600">Order #{{ $order->id }}</h1>
                    <p class="text-gray-600 mt-2">Placed on {{ $order->created_at->format('M d, Y H:i') }}</p>
                </div>
                <span class="px-4 py-2 rounded-full text-sm font-semibold
                    @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                    @elseif($order->status === 'confirmed') bg-blue-100 text-blue-800
                    @elseif($order->status === 'shipped') bg-purple-100 text-purple-800
                    @elseif($order->status === 'delivered') bg-green-100 text-green-800
                    @else bg-gray-100 text-gray-800
                    @endif">
                    {{ ucfirst($order->status) }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Shipping Information -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Shipping Information</h2>
                    <div class="space-y-3">
                        <p><span class="font-medium">Address:</span> {{ $order->shipping_address }}</p>
                        <p><span class="font-medium">Phone:</span> {{ $order->phone }}</p>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Order Summary</h2>
                    <div class="space-y-3">
                        <p><span class="font-medium">Total Items:</span> {{ $order->items->count() }}</p>
                        <p><span class="font-medium">Total Amount:</span> ${{ number_format($order->total, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="mt-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Order Items</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Product</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Quantity</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Price</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($order->items as $item)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <img src="{{ asset('storage/' . $item->product->photos[0]) }}" 
                                             alt="{{ $item->product->name }}" 
                                             class="w-16 h-16 object-cover rounded-md mr-4">
                                        <span class="font-medium">{{ $item->product->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">{{ $item->quantity }}</td>
                                <td class="px-6 py-4">${{ number_format($item->price, 2) }}</td>
                                <td class="px-6 py-4">${{ number_format($item->price * $item->quantity, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-8">
                <a href="{{ route('orders.history') }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Order History
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
