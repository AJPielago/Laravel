@extends('layouts.app')

@section('content')
@include('layouts.header')

<!-- Remove any padding from the app layout that might be constraining the background -->
<div class="bg-gradient-to-r from-indigo-500 to-purple-500 w-full min-h-screen -mt-16"> <!-- Negative margin to counteract any header spacing -->
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-16"> <!-- Added top padding to account for the header -->
        <div class="max-w-full bg-white shadow-lg rounded-lg p-8 my-12">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-indigo-600">Order History</h1>
                <p class="mt-2 text-gray-600">View your previous orders and their status.</p>
            </div>

            @if($orders->isEmpty())
                <div class="text-center py-8">
                    <p class="text-gray-600 text-lg">You have no previous orders.</p>
                </div>
            @else
                <div class="overflow-x-auto bg-white shadow-md rounded-lg">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b">
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Order ID</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Total</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($orders as $order)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4 text-sm text-gray-700">#{{ $order->id }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $order->created_at->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">${{ number_format($order->total, 2) }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 text-sm font-semibold rounded-full
                                            @if($order->status == 'pending') bg-yellow-100 text-yellow-700
                                            @elseif($order->status == 'confirmed') bg-blue-100 text-blue-700
                                            @elseif($order->status == 'shipped') bg-red-100 text-red-700
                                            @elseif($order->status == 'delivered' || $order->status == 'completed') bg-green-100 text-green-700
                                            @else bg-gray-100 text-gray-700
                                            @endif">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <a href="{{ route('orders.show', $order->id) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">View Details</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
/* Add this to your CSS to ensure the gradient fills the entire viewport */
html, body {
    margin: 0;
    padding: 0;
    height: 100%;
    min-height: 100vh;
    width: 100%;
}

.bg-gradient-to-r {
    position: relative;
    width: 100vw !important;
    left: 50%;
    right: 50%;
    margin-left: -50vw;
    margin-right: -50vw;
}
</style>
@endsection