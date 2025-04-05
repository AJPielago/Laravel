<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('is_deleted', false);

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        $query->where('stock', '>', 0);

        $products = $query->paginate(12);

        // Get unique categories for filter dropdown
        $categories = Product::where('is_deleted', false)
            ->distinct('category')
            ->whereIn('category', ['Pencils', 'Papers', 'Accessories', 'Boards', 'Colors', 'Erasers'])
            ->pluck('category')
            ->filter()
            ->values();

        return view('shop.index', compact('products', 'categories'));
    }

    public function show(Product $product)
    {
        if ($product->is_deleted || $product->stock <= 0) {
            abort(404);
        }
        
        return view('shop.show', compact('product'));
    }
}
