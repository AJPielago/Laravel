<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $product->name }} - Lara Shop</title>
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
                            <form action="{{ route('cart.add', $product) }}" method="POST" id="add-to-cart-form">
                                @csrf
                                <button type="button" 
                                        class="w-full bg-indigo-600 text-white px-6 py-3 rounded-full hover:bg-indigo-700 transition duration-300"
                                        onclick="addToCart()">
                                    Add to Cart
                                </button>
                            </form>
                        </div>
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
                </script>

                <!-- Notification Element -->
                <div id="cart-notification" class="fixed top-4 right-4 z-50 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-lg shadow-xl p-4 transform transition-all duration-300 opacity-0 translate-y-[-20px] pointer-events-none">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <p class="font-semibold" id="notification-message"></p>
                            <p class="text-sm text-white/80" id="notification-details"></p>
                        </div>
                    </div>
                </div>

                <!-- Reviews Section -->
                <div class="border-t pt-8">
                    <h2 class="text-2xl font-bold text-indigo-600 mb-6">Customer Reviews</h2>
                    
                    @if(auth()->check() && auth()->user()->hasOrderedProduct($product->id))
                        @php
                            $existingReview = $product->reviews()->where('user_id', auth()->id())->first();
                            $order = auth()->user()->getProductOrder($product->id);
                        @endphp

                        @if($order && $order->canBeReviewed())
                            <form action="{{ $existingReview ? route('reviews.update', $existingReview) : route('reviews.store', $product) }}" 
                                  method="POST" 
                                  class="mb-8">
                                @csrf
                                @if($existingReview)
                                    @method('PUT')
                                @endif
                                <input type="hidden" name="order_id" value="{{ $order->id }}">
                                
                                <div class="mb-4">
                                    <label class="block text-gray-700 mb-2">Rating</label>
                                    <div class="flex space-x-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            <input type="radio" 
                                                   name="rating" 
                                                   value="{{ $i }}" 
                                                   {{ $existingReview && $existingReview->rating == $i ? 'checked' : '' }}
                                                   required>
                                            <label class="mr-4">{{ $i }}</label>
                                        @endfor
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-gray-700 mb-2">Your Review</label>
                                    <textarea name="comment" 
                                              rows="4" 
                                              class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200"
                                              required>{{ $existingReview ? $existingReview->comment : '' }}</textarea>
                                </div>

                                <button type="submit"
                                        class="bg-indigo-600 text-white px-6 py-2 rounded-full hover:bg-indigo-700 transition duration-300">
                                    {{ $existingReview ? 'Update Review' : 'Submit Review' }}
                                </button>
                            </form>
                        @else
                            <p class="text-yellow-600">You can only review products from completed orders.</p>
                        @endif
                    @endif

                    <div class="space-y-6">
                        @php
                            $reviews = $product->reviews ?? collect();
                        @endphp
                        @if($reviews->count() > 0)
                            @foreach($reviews as $review)
                                <div class="bg-gray-50 rounded-lg p-6">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <p class="font-semibold">{{ $review->user->name }}</p>
                                            <div class="flex items-center">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg class="w-5 h-5 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" 
                                                         fill="currentColor" 
                                                         viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                @endfor
                                            </div>
                                        </div>
                                        <span class="text-gray-500 text-sm">{{ $review->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-gray-600">{{ $review->comment }}</p>
                                </div>
                            @endforeach
                        @else
                            <div class="bg-gray-50 rounded-lg p-6 text-center text-gray-600">
                                <p>No reviews yet for this product.</p>
                                <p class="mt-2 text-sm text-gray-500">Be the first to leave a review!</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
function addToCart() {
    const form = document.getElementById('add-to-cart-form');
    const formData = new FormData(form);
    const productName = '{{ $product->name }}';
    const productPrice = '{{ number_format($product->price, 2) }}';
    
    // Show loading state
    const toast = Swal.fire({
        title: 'Adding to Cart...',
        text: `${productName} - $${productPrice}`,
        icon: 'info',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Submit the form via fetch
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        credentials: 'same-origin'
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
    });
}
</script>
<!-- Soft Cyan Border -->
<footer class="bg-purple-50 text-gray-700 text-center py-6 border-t border-cyan-100">
    <p>&copy; 2025 Lara Shop. All rights reserved.</p>
</footer>
</body>
</html>
