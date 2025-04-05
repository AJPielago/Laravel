<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details #{{ $order->id }} - Lara Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-900">
@include('layouts.header')

    <main class="min-h-[calc(100vh-8rem)] bg-gradient-to-r from-indigo-500 to-purple-500 py-12">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
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
    </main>
</body>
</html>
