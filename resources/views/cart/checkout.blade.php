@extends('layouts.app')

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkoutForm = document.querySelector('form[action="{{ route('orders.store') }}"]');
        const receiptModal = document.getElementById('receipt-modal');
        const modalOverlay = document.getElementById('modal-overlay');

        checkoutForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Populate receipt modal
                    document.getElementById('order-number').textContent = data.order.id;
                    document.getElementById('order-date').textContent = new Date().toLocaleDateString();
                    document.getElementById('shipping-address').textContent = formData.get('shipping_address');
                    document.getElementById('shipping-phone').textContent = formData.get('phone');

                    // Populate order items
                    const itemsContainer = document.getElementById('order-items');
                    itemsContainer.innerHTML = ''; // Clear previous items
                    
                    @foreach($cart as $id => $details)
                    const item{{ $id }} = document.createElement('div');
                    item{{ $id }}.className = 'flex justify-between items-center py-2 border-b last:border-b-0';
                    item{{ $id }}.innerHTML = `
                        <div>
                            <p class="font-medium text-gray-800">{{ $details['name'] }}</p>
                            <p class="text-sm text-gray-500">Qty: {{ $details['quantity'] }}</p>
                        </div>
                        <p class="font-semibold text-indigo-600">${{ number_format($details['price'] * $details['quantity'], 2) }}</p>
                    `;
                    itemsContainer.appendChild(item{{ $id }});
                    @endforeach

                    // Set total
                    document.getElementById('order-total').textContent = '${{ number_format($total, 2) }}';

                    // Show modal
                    modalOverlay.classList.remove('hidden');
                    receiptModal.classList.remove('hidden');

                    // Auto-redirect
                    setTimeout(() => {
                        window.location.href = '{{ route('customer.dashboard') }}';
                    }, 5000);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Order Failed',
                        text: data.message || 'Something went wrong',
                        footer: '<a href="#" onclick="location.reload()">Try Again</a>'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Something went wrong',
                    footer: '<a href="#" onclick="location.reload()">Try Again</a>'
                });
            });
        });

        // Close modal functionality
        document.getElementById('close-modal')?.addEventListener('click', function() {
            modalOverlay.classList.add('hidden');
            receiptModal.classList.add('hidden');
        });
    });
</script>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div class="md:flex">
                <!-- Order Summary Section -->
                <div class="md:w-1/2 p-8 bg-gray-50">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Order Summary</h2>
                    <div class="space-y-4 mb-6">
                        @foreach($cart as $id => $details)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    @if(isset($details['image']))
                                        <img src="{{ Storage::url($details['image']) }}" 
                                             alt="{{ $details['name'] }}" 
                                             class="w-12 h-12 object-cover rounded-lg">
                                    @endif
                                    <div>
                                        <p class="font-medium text-gray-800">{{ $details['name'] }}</p>
                                        <p class="text-sm text-gray-500">Qty: {{ $details['quantity'] }}</p>
                                    </div>
                                </div>
                                <span class="font-medium text-gray-800">${{ number_format($details['price'] * $details['quantity'], 2) }}</span>
                            </div>
                        @endforeach
                        
                        <div class="border-t border-gray-200 pt-4 mt-6">
                            <div class="flex justify-between items-center font-bold text-xl">
                                <span class="text-gray-800">Total</span>
                                <span class="text-indigo-600">${{ number_format($total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Checkout Form Section -->
                <div class="md:w-1/2 p-8 bg-white">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Shipping Details</h2>
                    <form action="{{ route('orders.store') }}" method="POST">
                        @csrf
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Shipping Address</label>
                                <textarea name="shipping_address" required rows="3"
                                          class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                <input type="tel" 
                                       name="phone" 
                                       required
                                       pattern="[0-9]*"
                                       inputmode="numeric"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                            </div>

                            <button type="submit" 
                                    class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-3 px-4 rounded-xl hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-300 transform hover:scale-[1.02]">
                                Place Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div id="modal-overlay" 
     class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div id="receipt-modal" 
         class="bg-white shadow-2xl rounded-2xl max-w-md w-full overflow-hidden transform transition-all duration-300 ease-in-out hidden">
        
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white p-6">
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold">Order Confirmation</h2>
                <button id="close-modal" class="text-white hover:text-gray-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <p class="text-sm opacity-90 mt-2">Thank you for your purchase!</p>
        </div>

        <!-- Order Details -->
        <div class="p-6 space-y-4">
            <!-- Order Summary -->
            <div class="border-b pb-4">
                <div class="flex justify-between">
                    <span class="text-gray-600">Order Number</span>
                    <span id="order-number" class="font-semibold text-indigo-600"></span>
                </div>
                <div class="flex justify-between mt-2">
                    <span class="text-gray-600">Date</span>
                    <span id="order-date" class="font-medium"></span>
                </div>
            </div>

            <!-- Shipping Details -->
            <div class="border-b pb-4">
                <h3 class="text-lg font-semibold mb-2 text-gray-800">Shipping Information</h3>
                <div class="flex justify-between">
                    <span class="text-gray-600">Address</span>
                    <span id="shipping-address" class="text-right max-w-[200px] break-words"></span>
                </div>
                <div class="flex justify-between mt-2">
                    <span class="text-gray-600">Phone</span>
                    <span id="shipping-phone" class="font-medium"></span>
                </div>
            </div>

            <!-- Order Items -->
            <div>
                <h3 class="text-lg font-semibold mb-3 text-gray-800">Order Items</h3>
                <div id="order-items">
                    <!-- Items will be dynamically populated -->
                </div>
            </div>

            <!-- Total -->
            <div class="bg-gray-50 rounded-xl p-4 mt-4">
                <div class="flex justify-between items-center">
                    <span class="text-xl font-bold text-gray-800">Total</span>
                    <span id="order-total" class="text-2xl font-bold text-indigo-600"></span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-100 p-4 text-center">
            <p class="text-sm text-gray-600">
                Redirecting to dashboard in 5 seconds...
                <a href="{{ route('customer.dashboard') }}" class="text-indigo-600 ml-2 hover:underline">
                    Go Now
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
