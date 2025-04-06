@extends('layouts.app')

@push('styles')
<style>
    .slider-container {
        overflow: visible;
        width: 100%;
        max-width: 1400px;
        margin: auto;
        padding: 1rem;
    }

    .slider {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 1.5rem;
    }

    .slide {
        position: relative;
        z-index: 1;
        transition: all 0.6s cubic-bezier(0.22, 1, 0.36, 1);
    }

    .slide.active {
        transform: scale(1.1);
        z-index: 10;
    }
    
    .no-transition {
        transition: none !important;
    }
    
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
@endpush

@section('hideHeaderAuth', true)
@section('hideHeaderLogin', true)

@section('content')
    <!-- Hero Section -->
    <section class="text-center py-16 bg-gradient-to-r from-indigo-500 to-purple-500">
        <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-4xl font-bold mb-4 text-indigo-600">Welcome to Lara Shop</h2>
            <p class="text-lg mb-6 text-gray-600">Discover amazing products and enjoy a seamless shopping experience.</p>
            <div class="flex justify-center space-x-4">
                @auth
                    <a href="{{ auth()->user()->role === 'admin' ? route('dashboard') : route('customer.dashboard') }}" 
                       class="bg-indigo-600 text-white hover:bg-indigo-700 px-8 py-3 rounded-full font-semibold transition duration-300 shadow-lg hover:shadow-xl">
                        Browse Products
                    </a>
                @else
                    <a href="{{ route('login') }}" 
                       class="relative inline-flex items-center justify-center px-8 py-3 overflow-hidden font-medium text-white bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full group hover:scale-105 transition-all duration-300 ease-out hover:shadow-xl">
                        <span class="absolute inset-0 flex items-center justify-center w-full h-full text-white duration-300 -translate-x-full bg-gradient-to-r from-purple-600 to-indigo-500 group-hover:translate-x-0 ease">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                        </span>
                        <span class="absolute flex items-center justify-center w-full h-full text-white transition-all duration-300 transform group-hover:translate-x-full ease">Login to Shop</span>
                        <span class="relative invisible">Login to Shop</span>
                    </a>
                @endauth
            </div>
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
            <div class="slider-container">
                <div x-data="productSlider()" 
                     x-ref="slider" 
                     class="slider"
                     @mousemove.throttle.50ms="updateActiveSlide($event)"
                >
                    @foreach($products as $index => $product)
                        <div class="slide flex-shrink-0"
                             :class="{
                                 'active': activeSlide === {{ $index }}, 
                                 'opacity-50': activeSlide !== {{ $index }} && activeSlide !== null
                             }"
                             @mouseenter="activeSlide = {{ $index }}"
                             @mouseleave="activeSlide = null"
                        >
                            <div class="bg-white rounded-lg shadow-lg overflow-hidden transition-all duration-300 hover:shadow-xl">
                                <img 
                                    src="{{ $product->first_photo ? asset('storage/' . $product->first_photo) : asset('images/default-product.jpg') }}" 
                                    alt="{{ $product->name }}" 
                                    class="w-full h-48 object-cover"
                                >
                                <div class="p-4">
                                    <h4 class="text-lg font-semibold text-gray-800 mb-2">{{ $product->name }}</h4>
                                    <p class="text-indigo-600 font-bold text-xl">${{ number_format($product->price, 2) }}</p>
                                    <div class="mt-4 flex justify-between items-center">
                                        <span class="text-sm text-gray-500">{{ $product->category?->name ?? 'Uncategorized' }}</span>
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
@endsection

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
                
                const activeIndex = Math.floor(mouseX / slideWidth);
                
                if (activeIndex >= 0 && activeIndex < slides.length) {
                    this.activeSlide = activeIndex;
                }
            }
        }
    }
</script>
@endpush