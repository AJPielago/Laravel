@extends('layouts.app')

@section('content')
    @if(session('toast'))
        <x-toast 
            :message="session('toast')['message']"
            :type="session('toast')['type']"
            :details="session('toast')['details']"
        />
    @endif

    <div id="dynamic-toast"></div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-white shadow-lg rounded-lg p-8">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-indigo-600">Orders</h1>
                <p class="mt-2 text-gray-600">Manage customer orders and update their status.</p>
            </div>

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table id="orders-table" class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Order ID</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Customer</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Total</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($orders as $order)
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="px-6 py-4 text-sm text-gray-700">#{{ $order->id }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $order->user->name }}</td>
                            <td class="px-6 py-4">
                                <select onchange="updateOrderStatus({{ $order->id }}, this.value)" 
                                        class="status-select px-3 py-1 text-sm font-semibold rounded border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50
                                        @if($order->status === 'confirmed') bg-blue-100 text-blue-700 
                                        @elseif($order->status === 'pending') bg-yellow-100 text-yellow-700 
                                        @elseif($order->status === 'delivered') bg-green-100 text-green-700 
                                        @else bg-red-100 text-red-700 
                                        @endif">
                                    <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="confirmed" {{ $order->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                    <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                    <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">${{ number_format($order->total, 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $order->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4 text-sm space-x-3">
                                <a href="{{ route('orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">View</a>
                                <a href="{{ route('orders.manage', $order) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">Manage</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">No orders found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    function updateOrderStatus(orderId, newStatus) {
        $.ajax({
            url: `/orders/${orderId}/status`,
            method: 'PUT',
            data: {
                status: newStatus,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    createToast(response.toast);
                    
                    // Update the select element's styling based on the new status
                    const select = document.querySelector(`tr:has(td:contains('#${orderId}')) .status-select`);
                    
                    // Remove all color classes
                    select.classList.remove('bg-yellow-100', 'text-yellow-700', 'bg-blue-100', 'text-blue-700', 'bg-green-100', 'text-green-700', 'bg-red-100', 'text-red-700');
                    
                    // Add appropriate color class based on new status
                    if (newStatus === 'pending') {
                        select.classList.add('bg-yellow-100', 'text-yellow-700');
                    } else if (newStatus === 'confirmed') {
                        select.classList.add('bg-blue-100', 'text-blue-700');
                    } else if (newStatus === 'delivered') {
                        select.classList.add('bg-green-100', 'text-green-700');
                    } else if (newStatus === 'cancelled') {
                        select.classList.add('bg-red-100', 'text-red-700');
                    }
                }
            },
            error: function(xhr) {
                createToast({
                    type: 'error',
                    message: 'Failed to update order status',
                    details: xhr.responseJSON?.message || 'An error occurred while updating the order status.'
                });
            }
        });
    }
    </script>
@endsection
