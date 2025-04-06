<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\Review;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('is_deleted', false);

        // Search functionality with prefix matching
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "{$searchTerm}%")  // Changed from %{$searchTerm}% to {$searchTerm}%
                  ->orWhereHas('category', function($subQ) use ($searchTerm) {
                      $subQ->where('name', 'like', "{$searchTerm}%");  // Also update category search
                  });
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('name', $request->input('category'));
            });
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        $query->where('stock', '>', 0);

        $products = $query->with('category')->paginate(12);  // Add eager loading for category

        // Get unique categories for filter dropdown
        $categories = Category::select('name')
            ->whereHas('products', function($q) {
                $q->where('is_deleted', false);
            })
            ->distinct()
            ->pluck('name');

        return view('shop.index', compact('products', 'categories'));
    }

    public function show(Product $product)
    {
        try {
            DB::beginTransaction();

            $existingReview = null;
            $deliveredOrder = null;

            if (auth()->check()) {
                // Find any delivered order for this product
                $deliveredOrder = auth()->user()->orders()
                    ->where('status', 'delivered')
                    ->whereHas('items', function($query) use ($product) {
                        $query->where('product_id', $product->id);
                    })
                    ->latest()
                    ->first();

                // Check for existing review regardless of order
                $existingReview = Review::where('user_id', auth()->id())
                    ->where('product_id', $product->id)
                    ->first();
            }

            DB::commit();

            return view('shop.show', compact('product', 'deliveredOrder', 'existingReview'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in ShopController show method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('shop.index')->with('error', 'An unexpected error occurred.');
        }
    }
}
