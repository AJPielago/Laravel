@extends('layouts.app')

@section('content')
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
                                            class="mt-2 px-3 py-1 bg-indigo-500 text-white rounded hover:bg-indigo-600 transition">
                                        Write a Review
                                    </button>
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
@endsection

@push('scripts')
<!-- JavaScript remains unchanged -->
<script>
function openReviewModal(productId, orderId) {
    // Create a modal for writing a review
    const modal = document.createElement('div');
    modal.innerHTML = `
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
            <div class="bg-white rounded-lg p-8 max-w-md w-full">
                <h2 class="text-2xl font-bold mb-4 text-indigo-600">Write a Review</h2>
                <form id="reviewForm">
                    <input type="hidden" name="product_id" value="${productId}">
                    <input type="hidden" name="order_id" value="${orderId}">
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Rating</label>
                        <div class="flex space-x-1">
                            ${[1,2,3,4,5].map(star => `
                                <button type="button" onclick="setRating(${star})" 
                                        class="star-btn text-gray-300 hover:text-yellow-400" 
                                        data-rating="${star}">
                                    ★
                                </button>
                            `).join('')}
                        </div>
                        <input type="hidden" name="rating" id="ratingInput" value="0">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Review</label>
                        <textarea name="comment" rows="4" 
                                  class="w-full border rounded p-2 focus:ring-2 focus:ring-indigo-300" 
                                  placeholder="Share your experience..."></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-4">
                        <button type="button" onclick="closeReviewModal()" 
                                class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                            Submit Review
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal.firstChild);
}

function setRating(rating) {
    document.getElementById('ratingInput').value = rating;
    const starButtons = document.querySelectorAll('.star-btn');
    starButtons.forEach(btn => {
        const btnRating = parseInt(btn.getAttribute('data-rating'));
        btn.classList.toggle('text-yellow-400', btnRating <= rating);
        btn.classList.toggle('text-gray-300', btnRating > rating);
    });
}

function closeReviewModal() {
    const modal = document.querySelector('.fixed.inset-0.bg-black');
    if (modal) {
        modal.remove();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const reviewForm = document.getElementById('reviewForm');
    if (reviewForm) {
        reviewForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(reviewForm);
            
            try {
                const response = await fetch(`{{ route('reviews.store', '') }}/${formData.get('product_id')}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Review Submitted',
                        text: 'Thank you for your feedback!'
                    });
                    closeReviewModal();
                    // Optionally refresh the page or update the UI
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.message || 'Failed to submit review'
                    });
                }
            } catch (error) {
                console.error('Review submission error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An unexpected error occurred'
                });
            }
        });
    }
});
</script>
@endpush