<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Lara Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50" x-data="{ showReceipt: false, orderDetails: null }">
    <div class="min-h-screen bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 py-12">
        <div class="container mx-auto px-4">
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
                            <form action="{{ route('orders.store') }}" method="POST" 
                                  @submit="orderDetails = {
                                      items: {{ json_encode($cart) }},
                                      total: {{ $total }},
                                      date: new Date().toLocaleDateString(),
                                      address: $event.target.shipping_address.value,
                                      phone: $event.target.phone.value
                                  }">
                                @csrf
                                <div class="space-y-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Shipping Address</label>
                                        <textarea name="shipping_address" required rows="3"
                                                  class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"></textarea>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                        <input type="text" name="phone" required
                                               class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                                    </div>

                                    <button type="submit" 
                                            @click="showReceipt = true"
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
    </div>

    <!-- Receipt Modal -->
    <div x-show="showReceipt" 
         x-transition
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4" 
         style="display: none;"
         @keyup.escape.window="showReceipt = false"
         x-init="$watch('showReceipt', value => {
            if (value) {
                // Wait 3 seconds after receipt is shown, then redirect
                setTimeout(() => {
                    window.location.href = '{{ route('customer.dashboard') }}';
                }, 3000);
            }
         })">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8" id="receipt">
            <div class="text-center mb-6">
                <h3 class="text-2xl font-bold text-gray-800">Order Confirmation</h3>
                <p class="text-green-600">Thank you for your purchase!</p>
                <p class="text-sm text-gray-500 mt-2">Redirecting to dashboard in 3 seconds...</p>
            </div>

            <div class="space-y-4">
                <div class="border-b border-gray-200 pb-4">
                    <p class="text-gray-600">Date: <span x-text="orderDetails?.date"></span></p>
                    <p class="text-gray-600">Shipping Address: <span x-text="orderDetails?.address"></span></p>
                    <p class="text-gray-600">Phone: <span x-text="orderDetails?.phone"></span></p>
                </div>

                <template x-for="(item, id) in orderDetails?.items" :key="id">
                    <div class="flex justify-between py-2">
                        <div>
                            <p x-text="item.name" class="font-medium"></p>
                            <p class="text-sm text-gray-500" x-text="'Qty: ' + item.quantity"></p>
                        </div>
                        <p class="font-medium" x-text="'$' + (item.price * item.quantity).toFixed(2)"></p>
                    </div>
                </template>

                <div class="border-t border-gray-200 pt-4 mt-4">
                    <div class="flex justify-between items-center font-bold">
                        <span>Total</span>
                        <span x-text="'$' + orderDetails?.total.toFixed(2)" class="text-indigo-600"></span>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end space-x-4">
                <button @click="window.print()" 
                        class="px-4 py-2 bg-gray-100 text-gray-800 rounded-lg hover:bg-gray-200 transition">
                    Print Receipt
                </button>
                <button @click="window.location.href = '{{ route('customer.dashboard') }}'" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    Go to Dashboard
                </button>
            </div>
        </div>
    </div>

    <style>
        @media print {
            body > * {
                display: none;
            }
            #receipt {
                display: block !important;
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            #receipt button {
                display: none;
            }
        }
    </style>
</body>
</html>
