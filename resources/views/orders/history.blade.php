@extends('layouts.app')

@section('content')
    <div class="bg-white shadow-lg rounded-lg p-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-indigo-600">Order History</h1>
        </div>

        @if($orders->isEmpty())
            <div class="text-center py-8">
                <p class="text-gray-600 text-lg">You have no previous orders.</p>
            </div>
        @else
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
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
                    <tbody>
                        @foreach($orders as $order)
                            <tr class="border-b hover:bg-gray-50 transition">
                                <td class="px-6 py-4">#{{ $order->id }}</td>
                                <td class="px-6 py-4">{{ $order->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4">${{ number_format($order->total, 2) }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-sm rounded-full 
                                        @if($order->status == 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($order->status == 'completed') bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('orders.show', $order->id) }}" 
                                       class="text-indigo-600 hover:text-indigo-900">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
