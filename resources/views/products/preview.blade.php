@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Preview Product Images</h1>
    
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Product Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p><strong>Name:</strong> {{ $product->name }}</p>
                <p><strong>Price:</strong> ${{ number_format($product->price, 2) }}</p>
                <p><strong>Stock:</strong> {{ $product->stock }}</p>
                <p><strong>Category:</strong> {{ $product->category_id }}</p>
            </div>
        </div>
    </div>

    <div class="mt-8">
        <h2 class="text-xl font-semibold mb-4">Uploaded Images</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @forelse($previewPaths as $path)
                <div class="bg-gray-100 rounded-lg overflow-hidden shadow-md">
                    <img src="{{ asset($path) }}" alt="Product Preview" class="w-full h-48 object-cover">
                </div>
            @empty
                <p class="col-span-full text-gray-500">No images uploaded.</p>
            @endforelse
        </div>
    </div>

    <div class="mt-8 flex space-x-4">
        <form action="{{ route('products.store') }}" method="POST">
            @csrf
            <input type="hidden" name="name" value="{{ $product->name }}">
            <input type="hidden" name="price" value="{{ $product->price }}">
            <input type="hidden" name="stock" value="{{ $product->stock }}">
            <input type="hidden" name="category_id" value="{{ $product->category_id }}">
            
            @foreach($previewPaths as $path)
                <input type="hidden" name="preview_paths[]" value="{{ $path }}">
            @endforeach
            
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Confirm and Save Product
            </button>
        </form>
        
        <a href="{{ route('products.create') }}" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300">
            Cancel and Go Back
        </a>
    </div>
</div>
@endsection
