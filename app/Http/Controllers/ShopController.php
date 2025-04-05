<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
        try {
            // Log the start of the method with product details
            Log::info('ShopController show method started', [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'is_authenticated' => auth()->check()
            ]);

            // Verify product exists and can be retrieved
            if (!$product) {
                Log::error('Product not found', ['product_id' => $product->id]);
                return redirect()->route('shop.index')->with('error', 'Product not found.');
            }

            $existingReview = null;
            $latestOrderId = null;

            // Start a database transaction for additional safety
            DB::beginTransaction();

            if (auth()->check()) {
                // Detailed logging for database queries
                Log::info('Checking delivered orders', [
                    'user_id' => auth()->id(),
                    'product_id' => $product->id
                ]);

                // Check if user has purchased this product
                $deliveredOrders = Order::where('user_id', auth()->id())
                    ->whereHas('items', function($query) use ($product) {
                        $query->where('product_id', $product->id);
                    })
                    ->where('status', 'delivered')
                    ->get();

                Log::info('Delivered orders query result', [
                    'orders_count' => $deliveredOrders->count()
                ]);

                // Get the latest order ID if the user has purchased the product
                $latestOrderId = $deliveredOrders->count() > 0 
                    ? $deliveredOrders->sortByDesc('delivered_at')->first()->id 
                    : null;

                // Check if user has already reviewed this product
                $existingReview = Review::where('user_id', auth()->id())
                    ->where('product_id', $product->id)
                    ->first();

                Log::info('Existing review check', [
                    'existing_review' => $existingReview ? $existingReview->id : 'No existing review'
                ]);
            }

            // Commit the transaction
            DB::commit();

            // Prepare view data
            $viewData = [
                'product' => $product,
                'existingReview' => $existingReview,
                'latestOrderId' => $latestOrderId
            ];

            Log::info('Rendering shop.show view', $viewData);

            return view('shop.show', $viewData);

        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();

            // Log the full error details
            Log::error('Error in ShopController show method', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'product_id' => $product->id,
                'user_id' => auth()->check() ? auth()->id() : 'Not authenticated'
            ]);

            // Redirect with a generic error message
            return redirect()->route('shop.index')->with('error', 'An unexpected error occurred. Please try again.');
        }
    }
}
