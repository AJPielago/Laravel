<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lara Shop - Welcome</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .slider-container {
            overflow: hidden;
            width: 100%;
            max-width: 800px;
            margin: auto;
            position: relative;
        }

        .slider {
            display: flex;
            transition: transform 0.8s ease-in-out;
        }

        .slide {
            flex: 0 0 33.33%;
            /* Improved transition for smooth scaling and opacity */
            transition: all 0.6s cubic-bezier(0.22, 1, 0.36, 1);
            position: relative;
            z-index: 1;
        }

        .slide.active {
            transform: scale(1.1);
            z-index: 10;
        }
        
        /* No transition for the reset */
        .no-transition {
            transition: none !important;
        }
        
        /* Add perspective for a more dynamic effect */
        .card-perspective {
            perspective: 1000px;
        }
        
        .card-content {
            backface-visibility: hidden;
            transform-style: preserve-3d;
            transition: all 0.6s cubic-bezier(0.22, 1, 0.36, 1);
        }
        
        .active .card-content {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-900">
    <header class="bg-white shadow-md py-4">
        <div class="container mx-auto flex justify-between items-center px-6">
            <h1 class="text-2xl font-bold text-indigo-600">Lara Shop</h1>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="text-center py-16 bg-gradient-to-r from-indigo-500 to-purple-500 text-white">
        <h2 class="text-4xl font-bold mb-4">Welcome to Lara Shop</h2>
        <p class="text-lg mb-6">Discover amazing products and enjoy a seamless shopping experience.</p>
        <div class="flex justify-center space-x-4">
            @auth
                <a href="{{ route('products.index') }}" class="bg-white text-indigo-600 hover:bg-indigo-50 px-8 py-3 rounded-full font-semibold transition duration-300 shadow-lg hover:shadow-xl">
                    Browse Products
                </a>
            @else
                <a href="{{ route('login') }}" class="bg-white text-indigo-600 hover:bg-indigo-50 px-8 py-3 rounded-full font-semibold transition duration-300 shadow-lg hover:shadow-xl">
                    Login to Shop
                </a>
            @endauth
        </div>
    </section>

    <!-- Search Form -->
    <form action="{{ route('welcome') }}" method="GET" class="max-w-md mx-auto mb-8 px-4">
        <div class="relative flex items-center">
            <input 
                type="search" 
                name="search" 
                placeholder="Search products..." 
                value="{{ request('search') }}"
                class="w-full pl-4 pr-10 py-2 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-300"
            >
            <button 
                type="submit" 
                class="absolute right-0 mr-2 text-gray-600 hover:text-indigo-600 transition"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </button>
        </div>
    </form>

    <!-- Featured Products Section -->
    <section class="container mx-auto py-12 px-6">
        @if($products->total() > 0)
            <h3 class="text-2xl font-bold text-center mb-8">
                {{ request('search') ? 'Search Results' : 'Featured Products' }}
                @if(request('search'))
                    <span class="text-base text-gray-600 ml-2">({{ $products->total() }} result{{ $products->total() != 1 ? 's' : '' }})</span>
                @endif
            </h3>
        @else
            <div class="text-center py-12">
                <h3 class="text-2xl font-semibold text-gray-700 mb-4">No products found</h3>
                <p class="text-gray-500 mb-6">Try a different search term or browse our full product catalog.</p>
                <a href="{{ route('welcome') }}" class="bg-indigo-500 text-white px-4 py-2 rounded-full hover:bg-indigo-600 transition">
                    Clear Search
                </a>
            </div>
        @endif

        @if($products->total() > 0)
            <!-- Slider Container -->
            <div x-data="productSlider()" class="slider-container">
                <div 
                    x-ref="slider" 
                    class="slider flex space-x-4"
                    @mousemove.throttle.50ms="updateActiveSlide($event)"
                >
                    @foreach($products as $index => $product)
                        <div 
                            class="slide flex-shrink-0 w-1/3 p-4 transition-all duration-300 ease-in-out"
                            :class="{
                                'active': activeSlide === {{ $index }}, 
                                'opacity-50': activeSlide !== {{ $index }} && activeSlide !== null
                            }"
                            @mouseenter="activeSlide = {{ $index }}"
                            @mouseleave="activeSlide = null"
                        >
                            <div class="bg-white rounded-lg shadow-lg overflow-hidden transform transition-transform duration-300 hover:scale-105">
                                <img 
                                    src="{{ $product->first_photo ? asset('storage/' . $product->first_photo) : asset('images/default-product.jpg') }}" 
                                    alt="{{ $product->name }}" 
                                    class="w-full h-48 object-cover"
                                >
                                <div class="p-4">
                                    <h4 class="text-lg font-semibold text-gray-800 mb-2">{{ $product->name }}</h4>
                                    <p class="text-indigo-600 font-bold text-xl">${{ number_format($product->price, 2) }}</p>
                                    <div class="mt-4 flex justify-between items-center">
                                        <span class="text-sm text-gray-500">{{ $product->category }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-8 flex justify-center">
                {{ $products->links() }}
            </div>
        @endif
    </section>

    <!-- Footer -->
    <footer class="bg-lavender-50 text-gray-700 text-center py-6 mt-10 border-t border-cyan-100">
        <p>&copy; 2025 Lara Shop. All rights reserved.</p>
    </footer>

    @push('scripts')
    <script>
        function productSlider() {
            return {
                activeSlide: null,
                updateActiveSlide(event) {
                    const slider = this.$refs.slider;
                    const slides = slider.children;
                    const mouseX = event.clientX - slider.getBoundingClientRect().left;
                    const sliderWidth = slider.offsetWidth;
                    const slideWidth = slides[0].offsetWidth;
                    
                    // Calculate which slide should be active based on mouse position
                    const activeIndex = Math.floor(mouseX / slideWidth);
                    
                    if (activeIndex >= 0 && activeIndex < slides.length) {
                        this.activeSlide = activeIndex;
                    }
                }
            }
        }
    </script>
    @endpush
</body>
</html>