<div class="flex space-x-2">
    <a href="{{ route('products.edit', $product->id) }}" 
       class="text-indigo-600 hover:text-indigo-900 font-medium">
        Edit
    </a>
    <form action="{{ route($product->is_deleted ? 'products.restore' : 'products.destroy', $product->id) }}" 
          method="POST" 
          class="inline"
          onsubmit="return confirm('{{ $product->is_deleted ? 'Are you sure you want to activate this product?' : 'Are you sure you want to deactivate this product?' }}')">
        @csrf
        @if(!$product->is_deleted)
            @method('DELETE')
        @endif
        <button type="submit" 
                class="{{ $product->is_deleted ? 'text-green-600 hover:text-green-900' : 'text-red-600 hover:text-red-900' }} font-medium ml-2">
            {{ $product->is_deleted ? 'Activate' : 'Deactivate' }}
        </button>
    </form>
</div>
