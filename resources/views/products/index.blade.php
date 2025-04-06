@extends('layouts.app')

@section('content')
@auth
    @if(auth()->user()->role === 'admin')
        <div class="max-w-[95%] mx-auto">  <!-- Changed from default width to 95% width -->
            <div class="bg-white shadow-lg rounded-lg p-8">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-indigo-600">Products</h1>
                    <div class="space-x-4">
                        <a href="{{ route('products.create') }}" 
                           class="bg-indigo-600 text-white px-4 py-2 rounded-full hover:bg-indigo-700 transition">
                            Add New Product
                        </a>
                        <a href="{{ route('categories.index') }}" 
                           class="bg-indigo-600 text-white px-4 py-2 rounded-full hover:bg-indigo-700 transition">
                            Manage Categories
                        </a>
                        <a href="{{ route('products.import.form') }}" 
                           class="bg-emerald-600 text-white px-4 py-2 rounded-full hover:bg-emerald-700 transition">
                            Import Products
                        </a>
                    </div>
                </div>

                <!-- Filtering Section -->
                <div class="bg-gray-50 p-4 rounded-xl mb-4">
                    <form id="filter-form" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                            <select id="category-filter" name="category" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                <option value="">All Categories</option>
                                <option value="Pencils">Pencils</option>
                                <option value="Papers">Papers</option>
                                <option value="Accessories">Accessories</option>
                                <option value="Boards">Boards</option>
                                <option value="Colors">Colors</option>
                                <option value="Erasers">Erasers</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Min Price</label>
                            <input type="number" id="min-price-filter" name="min_price" 
                                   placeholder="Minimum Price"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Max Price</label>
                            <input type="number" id="max-price-filter" name="max_price" 
                                   placeholder="Maximum Price"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div class="flex items-end space-x-2">
                            <button type="button" id="apply-filters" 
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition flex-grow">
                                Apply Filters
                            </button>
                            <button type="button" id="reset-filters" 
                                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded-xl hover:bg-gray-300 transition">
                                Reset
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Products Table -->
                <div class="w-[95%] mx-auto overflow-x-auto">  <!-- Changed from auto width to 95% width -->
                    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b">
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Photo</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Name</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Category</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Price</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Stock</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Status</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $product)
                                    <tr class="border-b hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-6">
                                            @php
                                                $imagePath = str_replace(['products\/', 'products\\/', '\\\/'], 'products/', $product->first_photo);
                                            @endphp
                                            <img src="{{ asset('storage/' . $imagePath) }}" 
                                                 alt="{{ $product->name }}" 
                                                 class="w-20 h-20 object-cover rounded-lg">
                                        </td>
                                        <td class="px-6 py-6">{{ $product->name }}</td>
                                        <td class="px-6 py-6">{{ $product->category->name ?? 'No Category' }}</td>
                                        <td class="px-6 py-6">${{ number_format($product->price, 2) }}</td>
                                        <td class="px-6 py-6">{{ $product->stock }}</td>
                                        <td class="px-6 py-6">
                                            <span class="px-2 py-1 text-sm rounded-full {{ $product->is_deleted ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                                {{ $product->is_deleted ? 'Deactivated' : 'Active' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-6">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('products.edit', $product->id) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">Edit</a>
                                                <form action="{{ route($product->is_deleted ? 'products.restore' : 'products.destroy', $product) }}" method="POST" class="inline">
                                                    @csrf
                                                    @if(!$product->is_deleted)
                                                        @method('DELETE')
                                                    @else
                                                        @method('PUT')
                                                    @endif
                                                    <button type="submit" class="{{ $product->is_deleted ? 'text-green-600 hover:text-green-900' : 'text-red-600 hover:text-red-900' }} font-medium ml-2">
                                                        {{ $product->is_deleted ? 'Activate' : 'Deactivate' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pagination -->
                <div class="mt-6 flex justify-center">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    @else
        <div class="bg-white shadow-lg rounded-lg p-8 max-w-2xl mx-auto">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Access Denied</strong>
                <span class="block sm:inline">You don't have permission to manage products.</span>
            </div>
        </div>
    @endif
@else
    <div class="bg-white shadow-lg rounded-lg p-8 max-w-2xl mx-auto">
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Authentication Required</strong>
            <span class="block sm:inline">Please <a href="{{ route('login') }}" class="text-red-800 hover:text-red-900 underline">log in</a> to access this page.</span>
        </div>
    </div>
@endauth
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#apply-filters').on('click', function() {
        var selectedCategory = $('#category-filter').val();
        var minPrice = parseFloat($('#min-price-filter').val()) || 0;
        var maxPrice = parseFloat($('#max-price-filter').val()) || Number.MAX_SAFE_INTEGER;
        
        $('tbody tr').each(function() {
            var $row = $(this);
            var rowCategory = $row.children('td').eq(2).text().trim(); // Adjusted index
            var priceText = $row.children('td').eq(3).text().trim(); // Adjusted index
            var rowPrice = parseFloat(priceText.replace(/[^0-9.]/g, ''));
            
            var categoryMatch = !selectedCategory || selectedCategory === '' || 
                rowCategory.toLowerCase() === selectedCategory.toLowerCase();
            var priceMatch = rowPrice >= minPrice && rowPrice <= maxPrice;
            
            $row.toggle(categoryMatch && priceMatch);
        });
    });
    
    $('#reset-filters').on('click', function() {
        $('#category-filter').val('');
        $('#min-price-filter').val('');
        $('#max-price-filter').val('');
        $('tbody tr').show();
    });
});
</script>
@endpush
