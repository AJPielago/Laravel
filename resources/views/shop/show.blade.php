@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow-lg rounded-lg p-8">
        <!-- Product Details -->
        <div class="grid md:grid-cols-2 gap-8">
            <div class="space-y-4">
                @php
                    $photos = is_string($product->photos) ? json_decode($product->photos, true) : $product->photos;
                    $firstPhoto = !empty($photos) ? $photos[0] : null;
                    $otherPhotos = !empty($photos) ? array_slice($photos, 1) : [];
                @endphp
                
                @if($firstPhoto)
                    <div class="w-full h-[500px] bg-gray-100 rounded-lg overflow-hidden cursor-zoom-in" onclick="openImageModal('{{ Storage::url($firstPhoto) }}')">
                        <img src="{{ Storage::url($firstPhoto) }}" 
                             alt="{{ $product->name }}" 
                             class="w-full h-full object-contain">
                    </div>
                @endif
            </div>
            
            <div class="flex flex-col h-full">
                <div class="flex-grow">
                    <h1 class="text-3xl font-bold text-indigo-600 mb-4">{{ $product->name }}</h1>
                    <p class="text-gray-600 mb-6">{{ $product->description }}</p>
                    
                    <div class="flex items-center mb-6">
                        <span class="text-3xl font-bold text-indigo-600 mr-4">${{ number_format($product->price, 2) }}</span>
                        <span class="text-sm text-gray-500">
                            @if($product->stock > 0)
                                In Stock: {{ $product->stock }}
                            @else
                                Out of Stock
                            @endif
                        </span>
                    </div>

                    <!-- Add Rating Summary -->
                    <div class="mb-6">
                        @php
                            $averageRating = $product->reviews()->avg('rating');
                            $reviewCount = $product->reviews()->count();
                        @endphp
                        <div class="flex items-center gap-2">
                            <div class="flex items-center">
                                @for ($i = 1; $i <= 5; $i++)
                                    <span class="text-2xl {{ $i <= round($averageRating) ? 'text-yellow-400' : 'text-gray-300' }}">★</span>
                                @endfor
                            </div>
                            <span class="text-gray-600">({{ number_format($averageRating, 1) }}/5 from {{ $reviewCount }} {{ Str::plural('review', $reviewCount) }})</span>
                        </div>
                    </div>

                    @if(!empty($otherPhotos))
                        <div class="grid grid-cols-4 gap-4 mt-4 mb-6">
                            @foreach($otherPhotos as $photo)
                                <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden cursor-zoom-in" onclick="openImageModal('{{ Storage::url($photo) }}')">
                                    <img src="{{ Storage::url($photo) }}" 
                                         alt="{{ $product->name }} - Additional Photo" 
                                         class="w-full h-full object-contain">
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- View Reviews Button -->
                    <button type="button" 
                            onclick="toggleReviews()"
                            class="w-full mb-4 bg-indigo-100 text-indigo-600 px-6 py-3 rounded-full hover:bg-indigo-200 transition duration-300">
                        View Customer Reviews
                    </button>

                    @if($product->stock > 0)
                        <form action="{{ route('cart.add', $product) }}" method="POST" id="add-to-cart-form" class="mt-8">
                            @csrf
                            <div class="mb-4">
                                <label class="block text-gray-700 mb-2">Quantity</label>
                                <input type="number" 
                                       name="quantity" 
                                       min="1" 
                                       max="{{ $product->stock }}" 
                                       value="1" 
                                       class="w-full border rounded p-2">
                            </div>
                            <button type="submit" 
                                    class="w-full bg-indigo-600 text-white px-6 py-3 rounded-full hover:bg-indigo-700 transition duration-300">
                                Add to Cart
                            </button>
                        </form>

                        @if(auth()->check() && $deliveredOrder)
                            @php
                                $existingReview = $product->reviews()
                                    ->where('user_id', auth()->id())
                                    ->where('order_id', $deliveredOrder->id)
                                    ->first();

                                // Verify that the delivered order contains this product
                                $canReview = $deliveredOrder->items()
                                    ->where('product_id', $product->id)
                                    ->exists();
                            @endphp

                            @if($canReview)
                                <button type="button" 
                                        onclick="openReviewModal('{{ $deliveredOrder->id }}', {{ $existingReview ? $existingReview->rating : 0 }}, '{{ $existingReview ? $existingReview->comment : '' }}', {{ $existingReview ? $existingReview->id : 'null' }})"
                                        class="w-full mt-4 {{ $existingReview ? 'bg-indigo-600' : 'bg-green-600' }} text-white px-6 py-3 rounded-full hover:{{ $existingReview ? 'bg-indigo-700' : 'bg-green-700' }} transition duration-300">
                                    {{ $existingReview ? 'Update Your Review' : 'Write a Review' }}
                                </button>
                            @endif
                        @endif
                    @else
                        <button disabled 
                                class="w-full mt-8 bg-gray-400 text-white px-6 py-3 rounded-full cursor-not-allowed">
                            Out of Stock
                        </button>
                    @endif

                </div>
            </div>
        </div>

        <!-- Include Review Modal -->
        @include('shop.reviews.create')

        <!-- Reviews List Section -->
        <div id="reviewsSection" class="hidden mt-8 border-t pt-8">
            <h2 class="text-2xl font-bold text-indigo-600 mb-4">Customer Reviews</h2>
            <div id="reviewsList" class="space-y-4">
                @php
                    $reviews = $product->reviews()->with('user');
                    $userReview = null;
                    
                    if(auth()->check()) {
                        $userReview = $reviews->where('user_id', auth()->id())->first();
                    }
                    
                    $otherReviews = $reviews->where(function($query) {
                        if(auth()->check()) {
                            $query->where('user_id', '!=', auth()->id());
                        }
                    })->latest()->get();
                @endphp

                @if($userReview)
                    <div class="bg-indigo-50 rounded-lg p-4 border-2 border-indigo-100">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold">{{ $userReview->user->name }}</span>
                                    <span class="text-gray-500 text-sm">{{ $userReview->created_at->diffForHumans() }}</span>
                                    <span class="bg-indigo-100 text-indigo-800 text-xs px-2 py-1 rounded-full">Your Review</span>
                                </div>
                                <div class="flex text-yellow-400 my-1">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <span>{{ $i <= $userReview->rating ? '★' : '☆' }}</span>
                                    @endfor
                                </div>
                            </div>
                            <button onclick="openReviewModal('{{ $userReview->order_id }}', {{ $userReview->rating }}, '{{ $userReview->comment }}', {{ $userReview->id }})"
                                    class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                Edit Review
                            </button>
                        </div>
                        <p class="text-gray-600 mt-2">{{ $userReview->comment }}</p>
                    </div>
                @endif

                @foreach($otherReviews as $review)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold">{{ $review->user->name }}</span>
                                    <span class="text-gray-500 text-sm">{{ $review->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="flex text-yellow-400 my-1">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <span>{{ $i <= $review->rating ? '★' : '☆' }}</span>
                                    @endfor
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-600 mt-2">{{ $review->comment }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Image Modal -->
        <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-80 z-50 hidden flex items-center justify-center p-4" onclick="closeImageModal(event)">
            <div class="max-w-4xl max-h-[90vh] relative">
                <img id="modalImage" src="" alt="Zoomed Image" class="w-full h-full object-contain">
                <button class="absolute top-4 right-4 text-white text-3xl" onclick="closeImageModal(event)">&times;</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openImageModal(src) {
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    modalImage.src = src;
    modal.classList.remove('hidden');
}

function closeImageModal(event) {
    if (event.target.id === 'imageModal' || event.target.textContent === '×') {
        const modal = document.getElementById('imageModal');
        modal.classList.add('hidden');
    }
}

function openReviewModal(orderId, rating = 0, comment = '', reviewId = null) {
    const form = document.getElementById('reviewForm');
    const modalTitle = document.getElementById('reviewModalTitle');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    document.getElementById('order_id').value = orderId;
    document.getElementById('review_id').value = reviewId;
    document.getElementById('comment').value = comment || '';
    
    // Set the rating
    if (rating > 0) {
        setRating(rating); // Use the setRating function we defined
    } else {
        form.reset();
        const stars = document.querySelectorAll('#ratingStars span');
        stars.forEach(star => {
            star.classList.add('text-gray-300');
            star.classList.remove('text-yellow-400');
        });
    }
    
    // Always set mode based on existence of reviewId
    const isEdit = reviewId !== null;
    form.setAttribute('data-mode', isEdit ? 'edit' : 'create');
    
    modalTitle.textContent = isEdit ? 'Edit Your Review' : 'Write a Review';
    submitBtn.textContent = isEdit ? 'Update Review' : 'Submit Review';
    
    document.getElementById('reviewModal').classList.remove('hidden');
}

function closeReviewModal() {
    document.getElementById('reviewModal').classList.add('hidden');
}

function toggleReviews() {
    const reviewsSection = document.getElementById('reviewsSection');
    if (reviewsSection.classList.contains('hidden')) {
        reviewsSection.classList.remove('hidden');
        window.scrollTo({
            top: reviewsSection.offsetTop - 100,
            behavior: 'smooth'
        });
    } else {
        reviewsSection.classList.add('hidden');
    }
}

document.getElementById('reviewForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');
    const isEdit = this.getAttribute('data-mode') === 'edit';
    const reviewId = document.getElementById('review_id').value;
    submitButton.disabled = true;
    
    // Add _method field for PUT if editing
    if (isEdit) {
        formData.append('_method', 'PUT');
    }
    
    try {
        const url = isEdit 
            ? `/product/{{ $product->id }}/reviews/${reviewId}`
            : '{{ route('product.reviews.store', $product) }}';
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: isEdit ? 'Review updated successfully!' : 'Review submitted successfully!',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: 'linear-gradient(to right, #6366f1, #a855f7)',
                color: '#ffffff',
                iconColor: '#ffffff',
                customClass: {
                    popup: 'rounded-lg shadow-xl'
                }
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: result.message || 'Failed to process review',
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
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: error.message || 'Something went wrong. Please try again.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#4F46E5'
        });
    } finally {
        submitButton.disabled = false;
    }
});

document.getElementById('add-to-cart-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    submitButton.disabled = true;

    // Show loading state
    const toast = Swal.fire({
        title: 'Adding to Cart...',
        text: '{{ $product->name }} - ${{ number_format($product->price, 2) }}',
        icon: 'info',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        credentials: 'same-origin',
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw new Error(err.message || 'Network response was not ok'); });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Added to Cart!',
                text: data.message,
                icon: 'success',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: 'linear-gradient(to right, #6366f1, #a855f7)',
                color: '#ffffff',
                iconColor: '#ffffff',
                customClass: {
                    popup: 'rounded-lg shadow-xl'
                }
            });
            
            // Update cart count
            const cartCountEl = document.getElementById('cart-count');
            if (cartCountEl) {
                cartCountEl.textContent = data.cart_count || (parseInt(cartCountEl.textContent) + 1);
            }
        } else {
            throw new Error(data.message || 'Failed to add item to cart');
        }
    })
    .catch(error => {
        Swal.fire({
            title: 'Error',
            text: error.message || 'Failed to add item to cart. Please try again.',
            icon: 'error',
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
        console.error('Error:', error);
    })
    .finally(() => {
        submitButton.disabled = false;
    });
});
</script>
@endpush