@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    @if(session('status_change'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            Order status changed from 
            <strong>{{ ucfirst(session('status_change')['old']) }}</strong> 
            to 
            <strong>{{ ucfirst(session('status_change')['new']) }}</strong>
        </div>
    @endif

    @if(session('review_prompt'))
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4 flex justify-between items-center" role="alert">
            <span>{{ session('review_prompt') }}</span>
            <a href="{{ route('reviews.create', ['order' => $order->id]) }}" 
               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded ml-4">
                Leave a Review
            </a>
        </div>
    @endif

    <div class="bg-white shadow-lg rounded-lg p-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-indigo-600">Order #{{ $order->id }}</h1>
            <div class="flex items-center mt-2">
                <span class="mr-2 px-3 py-1 rounded-full 
                    @if($order->status == 'pending') bg-yellow-100 text-yellow-800
                    @elseif($order->status == 'confirmed') bg-blue-100 text-blue-800
                    @elseif($order->status == 'shipped') bg-purple-100 text-purple-800
                    @elseif($order->status == 'delivered') bg-green-100 text-green-800
                    @endif
                    text-sm font-medium capitalize">
                    {{ $order->status }}
                </span>
                <p class="text-gray-600">Placed on {{ $order->created_at->format('M d, Y H:i') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Order Details -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Order Information</h2>
                <div class="space-y-3">
                    <p><span class="font-medium">Customer:</span> {{ $order->user->name }}</p>
                    <p><span class="font-medium">Email:</span> {{ $order->user->email }}</p>
                    <p><span class="font-medium">Phone:</span> {{ $order->phone }}</p>
                    <p><span class="font-medium">Shipping Address:</span> {{ $order->shipping_address }}</p>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Order Summary</h2>
                <div class="space-y-3">
                    <p><span class="font-medium">Total Items:</span> {{ $order->items->count() }}</p>
                    <p><span class="font-medium">Total Amount:</span> ${{ number_format($order->total, 2) }}</p>
                    <p><span class="font-medium">Payment Status:</span> 
                        <span class="text-green-600 font-medium">Paid</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="mt-8">
            <h2 class="text-2xl font-semibold mb-4">Order Items</h2>
            <div class="overflow-x-auto">
                <table class="w-full bg-white shadow-md rounded-lg overflow-hidden">
                    <thead class="bg-indigo-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($order->items as $item)
                            <tr>
                                <td class="px-4 py-4 whitespace-nowrap flex items-center">
                                    <img src="{{ $item->product->first_photo }}" 
                                         alt="{{ $item->product->name }}" 
                                         class="w-16 h-16 object-cover rounded-md mr-4">
                                    <span>{{ $item->product->name }}</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">{{ $item->quantity }}</td>
                                <td class="px-4 py-4 whitespace-nowrap">${{ number_format($item->price, 2) }}</td>
                                <td class="px-4 py-4 whitespace-nowrap">${{ number_format($item->price * $item->quantity, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-8 flex justify-between items-center">
            <a href="{{ route('orders.history') }}" 
               class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                Back to Order History
            </a>
            @if($order->status == 'pending')
                <form action="{{ route('orders.cancel', $order) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <button type="submit" 
                            onclick="return confirm('Are you sure you want to cancel this order?')"
                            class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                        Cancel Order
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
