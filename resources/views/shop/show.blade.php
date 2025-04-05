<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $product->name }} - Lara Shop</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 text-gray-900">
    <header class="bg-white shadow-md py-4">
        <div class="container mx-auto flex justify-between items-center px-6">
            <div class="flex items-center space-x-4">
                <a href="{{ route('welcome') }}" class="flex items-center">
                    <img src="{{ asset('logo.png') }}" alt="Lara Shop Logo" class="h-10 w-auto">
                </a>
                <h1 class="text-2xl font-bold text-indigo-600 hidden md:block">Lara Shop</h1>
            </div>
            <nav class="space-x-4 flex items-center">
                <a href="{{ route('dashboard') }}" class="text-gray-800 hover:text-indigo-600 transition">Dashboard</a>
                <a href="{{ route('shop.index') }}" class="text-gray-800 hover:text-indigo-600 transition">Products</a>
                <a href="{{ route('cart.index') }}" class="relative text-gray-800 hover:text-indigo-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span id="cart-count" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                        {{ count(session('cart', [])) }}
                    </span>
                </a>
            </nav>
        </div>
    </header>

    <main class="min-h-[calc(100vh-8rem)] bg-gradient-to-r from-indigo-500 to-purple-500 py-12">
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

                            <!-- View Reviews Button -->
                            <button type="button" 
                                    onclick="toggleReviews()"
                                    class="w-full mb-4 bg-indigo-100 text-indigo-600 px-6 py-3 rounded-full hover:bg-indigo-200 transition duration-300">
                                View Reviews
                            </button>

                            @if(!empty($otherPhotos))
                                <div class="grid grid-cols-4 gap-4 mt-4">
                                    @foreach($otherPhotos as $photo)
                                        <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden cursor-zoom-in" onclick="openImageModal('{{ Storage::url($photo) }}')">
                                            <img src="{{ Storage::url($photo) }}" 
                                                 alt="{{ $product->name }} - Additional Photo" 
                                                 class="w-full h-full object-contain">
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        
                        <div class="mt-auto">
                            <form action="{{ route('cart.add', $product) }}" method="POST" id="add-to-cart-form" class="mt-8">
                                @csrf
                                <button type="button" 
                                        class="w-full bg-indigo-600 text-white px-6 py-3 rounded-full hover:bg-indigo-700 transition duration-300"
                                        onclick="addToCart()">
                                    Add to Cart
                                </button>
                            </form>
                            
                            @php
                                $deliveredOrders = auth()->user()->orders()
                                    ->where('status', 'delivered')
                                    ->whereHas('items', function($query) use ($product) {
                                        $query->where('product_id', $product->id);
                                    })
                                    ->get();
                            @endphp

                            @if($deliveredOrders->isNotEmpty())
                                <button type="button" 
                                        onclick="openReviewModal('{{ $deliveredOrders->first()->id }}')"
                                        class="w-full mt-2 bg-green-600 text-white px-6 py-3 rounded-full hover:bg-green-700 transition duration-300">
                                    Write a Review
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                @include('shop.reviews.create')

                <!-- Add Reviews List Section -->
                <div id="reviewsSection" class="hidden mt-8 border-t pt-8">
                    <h2 class="text-2xl font-bold text-indigo-600 mb-4">Customer Reviews</h2>
                    <div id="reviewsList" class="space-y-4">
                        @foreach($product->reviews()->with('user')->latest()->get() as $review)
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

                function openReviewModal(orderId) {
                    document.getElementById('order_id').value = orderId;
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

                // Add AJAX form submission
                document.getElementById('reviewForm').addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const submitButton = this.querySelector('button[type="submit"]');
                    submitButton.disabled = true;
                    
                    try {
                        const response = await fetch('{{ route('reviews.store') }}', {
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
                                text: 'Your review has been submitted successfully.'
                            });
                            closeReviewModal();
                            // Optionally reload the page to show the new review
                            location.reload();
                        } else {
                            throw new Error(result.message || 'Failed to submit review');
                        }
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: error.message || 'Something went wrong. Please try again.'
                        });
                    } finally {
                        submitButton.disabled = false;
                    }
                });
                </script>

</body>
</html>