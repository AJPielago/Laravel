<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Order - Lara Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-900">
@include('layouts.header')

    <main class="min-h-[calc(100vh-8rem)] bg-gradient-to-r from-indigo-500 to-purple-500 py-12">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-lg p-8">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-indigo-600">Manage Order #{{ $order->id }}</h1>
                    <p class="mt-2 text-gray-600">Update order status and details</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Order Details -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h2 class="text-xl font-semibold mb-4">Order Details</h2>
                        <div class="space-y-3">
                            <p><span class="font-medium">Customer:</span> {{ $order->user->name }}</p>
                            <p><span class="font-medium">Email:</span> {{ $order->user->email }}</p>
                            <p><span class="font-medium">Phone:</span> {{ $order->phone }}</p>
                            <p><span class="font-medium">Address:</span> {{ $order->shipping_address }}</p>
                            <p><span class="font-medium">Total:</span> ${{ number_format($order->total, 2) }}</p>
                            <p><span class="font-medium">Date:</span> {{ $order->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>

                    <!-- Update Status -->
                    <div>
                        <h2 class="text-xl font-semibold mb-4">Update Status</h2>
                        <form action="{{ route('orders.update-status', $order) }}" method="POST" class="space-y-4">
                            @csrf
                            @method('PUT')
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Order Status</label>
                                <select name="status" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                                    <option value="pending" {{ strtolower($order->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="confirmed" {{ strtolower($order->status) === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                    <option value="shipped" {{ strtolower($order->status) === 'shipped' ? 'selected' : '' }}>Shipped</option>

                                </select>
                            </div>

                            <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition duration-300">
                                Update Status
                            </button>
                        </form>

                        @if(session('status_change'))
                            <div class="mt-4 bg-indigo-50 border-l-4 border-indigo-600 p-4 rounded" role="alert">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-indigo-700">
                                            Order status updated from 
                                            <span class="font-semibold">{{ ucfirst(session('status_change.old')) }}</span> 
                                            to 
                                            <span class="font-semibold">{{ ucfirst(session('status_change.new')) }}</span>
                                        </p>
                                        @if(session('status_change.email_sent'))
                                            <p class="mt-2 text-sm text-indigo-600">
                                                <svg class="inline-block h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                                </svg>
                                                Order confirmation email has been sent to the customer
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="mt-4 bg-red-50 border-l-4 border-red-600 p-4 rounded" role="alert">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-red-700">{{ session('error') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Order Items -->
                <div class="mt-8">
                    <h2 class="text-xl font-semibold mb-4">Order Items</h2>
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
                                        <td class="px-6 py-4">{{ $item->product->name }}</td>
                                        <td class="px-6 py-4">{{ $item->quantity }}</td>
                                        <td class="px-6 py-4">${{ number_format($item->price, 2) }}</td>
                                        <td class="px-6 py-4">${{ number_format($item->price * $item->quantity, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-white text-gray-700 text-center py-6 border-t border-gray-200">
        <p>&copy; 2025 Lara Shop. All rights reserved.</p>
    </footer>
</body>
</html>
