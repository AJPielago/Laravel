@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-r from-indigo-500 to-purple-500 py-12">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-xl rounded-lg p-8">
            <h1 class="text-3xl font-bold text-indigo-600 mb-6">My Order History</h1>
            
            @forelse($orders as $order)
                <div class="bg-gray-50 rounded-lg p-6 mb-4 border border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800">Order #{{ $order->id }}</h2>
                            <p class="text-gray-600">Placed on {{ $order->created_at->format('M d, Y') }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-sm font-medium 
                            @if($order->status === 'completed') bg-green-100 text-green-800
                            @elseif($order->status === 'pending') bg-yellow-100 text-yellow-800
                            @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        @foreach($order->items as $item)
                            <div class="flex items-center space-x-4 bg-white p-4 rounded-lg shadow-sm">
                                <img src="{{ asset('storage/' . $item->product->image) }}" 
                                     alt="{{ $item->product->name }}" 
                                     class="w-20 h-20 object-cover rounded-md">
                                <div>
                                    <h3 class="font-semibold text-gray-800">{{ $item->product->name }}</h3>
                                    <p class="text-gray-600">Quantity: {{ $item->quantity }}</p>
                                    <p class="text-indigo-600 font-bold">${{ number_format($item->price * $item->quantity, 2) }}</p>
                                    
                                    @if($order->status === 'completed')
                                        @php
                                            $existingReview = $item->product->reviews()
                                                ->where('user_id', auth()->id())
                                                ->where('order_id', $order->id)
                                                ->first();
                                        @endphp
                                        
                                        @if(!$existingReview)
                                            <button onclick="openReviewModal({{ $item->product->id }}, {{ $order->id }})" 
                                                    class="mt-2 px-3 py-1 bg-indigo-600 text-white rounded-full hover:bg-indigo-700 transition duration-300">
                                                Post Review
                                            </button>
                                        @else
                                            <div class="mt-2 text-sm text-green-600">
                                                You've already reviewed this product
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-center text-gray-600 py-8">
                    <p>You haven't placed any orders yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Review Modal -->
<div id="reviewModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-xl w-96 p-6">
        <h2 class="text-2xl font-bold text-indigo-600 mb-4">Write a Review</h2>
        <form id="reviewForm" method="POST">
            @csrf
            <input type="hidden" id="productId" name="product_id">
            <input type="hidden" id="orderId" name="order_id">
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Rating</label>
                <div class="flex space-x-1">
                    @for($i = 1; $i <= 5; $i++)
                        <button type="button" 
                                onclick="setRating({{ $i }})" 
                                class="star-btn text-gray-300 hover:text-yellow-400 focus:outline-none"
                                data-rating="{{ $i }}">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </button>
                    @endfor
                </div>
                <input type="hidden" id="rating" name="rating" required>
            </div>
            
            <div class="mb-4">
                <label for="comment" class="block text-gray-700 mb-2">Review</label>
                <textarea id="comment" name="comment" rows="4" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" 
                          placeholder="Tell us about your experience..." 
                          required></textarea>
            </div>
            
            <div class="flex justify-end space-x-2">
                <button type="button" 
                        onclick="closeReviewModal()" 
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Submit Review
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
function setRating(rating) {
    const stars = document.querySelectorAll('.star-btn');
    const ratingInput = document.getElementById('rating');
    
    stars.forEach(star => {
        const starRating = parseInt(star.getAttribute('data-rating'));
        if (starRating <= rating) {
            star.classList.remove('text-gray-300');
            star.classList.add('text-yellow-400');
        } else {
            star.classList.remove('text-yellow-400');
            star.classList.add('text-gray-300');
        }
    });
    
    ratingInput.value = rating;
}

function openReviewModal(productId, orderId) {
    const modal = document.getElementById('reviewModal');
    const productIdInput = document.getElementById('productId');
    const orderIdInput = document.getElementById('orderId');
    const reviewForm = document.getElementById('reviewForm');
    
    // Reset form
    reviewForm.reset();
    document.querySelectorAll('.star-btn').forEach(star => {
        star.classList.remove('text-yellow-400');
        star.classList.add('text-gray-300');
    });
    
    // Set hidden inputs
    productIdInput.value = productId;
    orderIdInput.value = orderId;
    
    // Show modal
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeReviewModal() {
    const modal = document.getElementById('reviewModal');
    modal.classList.remove('flex');
    modal.classList.add('hidden');
}

document.getElementById('reviewForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('{{ route("reviews.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            createToast({
                type: 'success',
                message: 'Review Submitted',
                details: 'Thank you for your feedback!'
            });
            closeReviewModal();
            // Optionally refresh the page or update the UI
        } else {
            createToast({
                type: 'error',
                message: 'Error',
                details: data.message || 'Failed to submit review'
            });
        }
    })
    .catch(error => {
        createToast({
            type: 'error',
            message: 'Error',
            details: 'An unexpected error occurred'
        });
    });
});
</script>
@endsection
