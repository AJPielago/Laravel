@extends('layouts.app')

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkoutForm = document.querySelector('form[action="{{ route('orders.store') }}"]');

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
                    // Build receipt HTML
                    let receiptHtml = `
                        <div class="space-y-4 text-left text-white">
                            <div class="border-b border-white/20 pb-4">
                                <div class="flex justify-between">
                                    <span class="text-white/80">Order #</span>
                                    <span class="font-semibold">${data.order.id}</span>
                                </div>
                                <div class="flex justify-between mt-2">
                                    <span class="text-white/80">Date</span>
                                    <span>${new Date().toLocaleDateString()}</span>
                                </div>
                            </div>
                            <div class="border-b border-white/20 pb-4">
                                <h3 class="font-semibold mb-2">Shipping Information</h3>
                                <div class="text-sm">
                                    <p><span class="text-white/80">Address:</span> ${formData.get('shipping_address')}</p>
                                    <p><span class="text-white/80">Phone:</span> ${formData.get('phone')}</p>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-semibold mb-2">Order Items</h3>
                                <div class="space-y-2">
                                    @foreach($cart as $id => $details)
                                    <div class="flex justify-between text-sm">
                                        <div>
                                            <p class="font-medium">{{ $details['name'] }}</p>
                                            <p class="text-white/70">Qty: {{ $details['quantity'] }}</p>
                                        </div>
                                        <p class="font-semibold">${{ number_format($details['price'] * $details['quantity'], 2) }}</p>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="border-t border-white/20 pt-4 mt-4">
                                <div class="flex justify-between">
                                    <span class="text-lg font-bold">Total</span>
                                    <span class="text-lg font-bold">${{ number_format($total, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    `;

                    Swal.fire({
                        title: 'Order Confirmation',
                        html: receiptHtml,
                        icon: 'success',
                        width: 600,
                        confirmButtonText: 'Continue Shopping',
                        confirmButtonColor: '#ffffff',
                        buttonsStyling: false, // Disable default styling
                        background: 'linear-gradient(to right, #4f46e5, #7c3aed)', // Match app's indigo to purple gradient
                        color: '#ffffff', // Make modal title text white
                        customClass: {
                            container: 'receipt-modal',
                            popup: 'rounded-xl shadow-2xl p-4',
                            htmlContainer: 'p-0',
                            title: 'text-2xl mb-4',
                            closeButton: 'text-white hover:text-gray-200',
                            confirmButton: 'px-6 py-3 bg-white !text-indigo-700 hover:bg-gray-50 font-semibold rounded-lg transition-colors duration-200'
                        }
                    }).then((result) => {
                        window.location.href = '{{ route('shop.index') }}';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Order Failed',
                        text: data.message || 'Something went wrong',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        background: 'linear-gradient(to right, #ff6b6b, #ff4757)',
                        color: '#ffffff',
                        iconColor: '#ffffff',
                        customClass: {
                            popup: 'rounded-lg shadow-xl'
                        }
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

                <!-- Shipping Details Section -->
                <div class="md:w-1/2 p-8 bg-white">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Shipping Details</h2>
                    <form action="{{ route('orders.store') }}" method="POST">
                        @csrf
                        <div class="space-y-6">
                            <div>
                                <span class="text-sm font-medium text-gray-700">Shipping Address</span>
                                <p class="mt-2 text-gray-800 p-4 bg-gray-50 rounded-lg">
                                    {{ auth()->user()->shipping_address }}
                                </p>
                            </div>
                            
                            <div>
                                <span class="text-sm font-medium text-gray-700">Phone Number</span>
                                <p class="mt-2 text-gray-800 p-4 bg-gray-50 rounded-lg">
                                    {{ auth()->user()->phone }}
                                </p>
                            </div>

                            <div class="border-t pt-6">
                                <a href="{{ route('profile.edit') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                                    Edit Shipping Details
                                </a>
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
@endsection
