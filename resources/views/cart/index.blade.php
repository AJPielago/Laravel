@extends('layouts.app')

@section('content')
<div class="min-h-[calc(100vh-8rem)] bg-gradient-to-r from-indigo-500 to-purple-500 py-12">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-lg rounded-lg p-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-indigo-600">Shopping Cart</h1>
                    <p class="mt-2 text-gray-600">Review your items below.</p>
                </div>
            </div>

            @if(empty($cart))
                <p class="text-gray-500">Your cart is empty</p>
                <a href="{{ route('shop.index') }}" class="mt-4 inline-block bg-indigo-600 text-white px-6 py-3 rounded-full hover:bg-indigo-700 transition duration-300">
                    Continue Shopping
                </a>
            @else
                <div class="space-y-4">
                    @foreach($cart as $id => $details)
                        <div class="flex items-center justify-between border-b pb-4">
                            <div class="flex items-center space-x-4">
                                @if($details['image'])
                                    <img src="{{ Storage::url($details['image']) }}" 
                                         alt="{{ $details['name'] }}" 
                                         class="w-16 h-16 object-cover rounded-lg shadow">
                                @endif
                                <div>
                                    <h3 class="font-semibold text-lg">{{ $details['name'] }}</h3>
                                    <p class="text-gray-600">${{ number_format($details['price'], 2) }}</p>
                                    <div class="flex items-center mt-2">
                                        <button type="button" 
                                                onclick="updateQuantity({{ $id }}, -1)"
                                                class="w-8 h-8 flex items-center justify-center bg-gray-100 rounded-full hover:bg-gray-200 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                            </svg>
                                        </button>
                                        <span class="mx-4 font-medium" id="qty-{{ $id }}">{{ $details['quantity'] }}</span>
                                        <button type="button"
                                                onclick="updateQuantity({{ $id }}, 1)"
                                                class="w-8 h-8 flex items-center justify-center bg-gray-100 rounded-full hover:bg-gray-200 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <form action="{{ route('cart.remove', $id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-500 hover:text-red-700 transition"
                                            onclick="event.preventDefault(); 
                                                     if(confirm('Are you sure you want to remove this item from your cart?')) {
                                                         this.closest('form').submit();
                                                     }">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                    
                    <div class="mt-6 flex justify-between items-center">
                        <p class="text-2xl font-bold text-indigo-600" id="cart-total">Total: ${{ number_format($total, 2) }}</p>
                        <a href="{{ route('cart.checkout') }}" 
                           class="bg-indigo-600 text-white px-8 py-3 rounded-full hover:bg-indigo-700 transition duration-300 shadow-lg">
                            Proceed to Checkout
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateQuantity(productId, change) {
    const qtyElement = document.getElementById(`qty-${productId}`);
    let newQty = parseInt(qtyElement.textContent) + change;
    
    // Ensure quantity doesn't go below 1
    if (newQty < 1) return;
    
    fetch(`{{ url('/cart/update') }}/${productId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            quantity: newQty
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update quantity display
            qtyElement.textContent = newQty;
            
            // Update total price display using the specific cart total element
            const cartTotalEl = document.getElementById('cart-total');
            if (cartTotalEl && data.cart_total) {
                cartTotalEl.textContent = `Total: $${parseFloat(data.cart_total).toFixed(2)}`;
            }
        } else {
            throw new Error(data.message || 'Failed to update quantity');
        }
    })
    .catch(error => {
        Swal.fire({
            title: 'Error',
            text: error.message || 'Failed to update quantity',
            icon: 'error',
            toast: true,
            position: 'top-end',
            timer: 3000
        });
    });
}
</script>
@endpush
