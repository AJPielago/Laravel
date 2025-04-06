<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        try {
            $validatedData = $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:500',
                'order_id' => 'required|exists:orders,id'
            ]);

            // Find the order and verify it's delivered and belongs to the user
            $order = Order::where('id', $validatedData['order_id'])
                         ->where('user_id', auth()->id())
                         ->where('status', 'delivered')
                         ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid order or unauthorized access.'
                ], 403);
            }

            // Verify that the order contains this product
            $hasProduct = $order->items()
                               ->where('product_id', $product->id)
                               ->exists();

            if (!$hasProduct) {
                return response()->json([
                    'success' => false,
                    'message' => 'This product is not in the specified order.'
                ], 400);
            }

            // Create or update the review
            $review = Review::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'product_id' => $product->id,
                    'order_id' => $order->id
                ],
                [
                    'rating' => $validatedData['rating'],
                    'comment' => $validatedData['comment']
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error in product review store: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit review: ' . $e->getMessage()
            ], 500);
        }
    }

    public function create(Order $order)
    {
        // Ensure the order belongs to the authenticated user
        if ($order->user_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Unauthorized to review this order.');
        }

        // Check if the order is delivered
        if ($order->status !== 'delivered') {
            return redirect()->back()->with('error', 'You can only review delivered orders.');
        }

        // Check if a review already exists for this order
        $existingReview = Review::where('order_id', $order->id)->first();
        if ($existingReview) {
            return redirect()->back()->with('error', 'You have already reviewed this order.');
        }

        // Find the first product in the order
        $orderItem = $order->items->first();
        if (!$orderItem) {
            return redirect()->back()->with('error', 'No products found in this order.');
        }

        // Pass the order and its first product to the view
        return view('reviews.create', [
            'order' => $order,
            'product' => $orderItem->product
        ]);
    }

    public function index()
    {
        // Check if the user is an admin
        if (auth()->check() && auth()->user()->isAdmin()) {
            return view('admin.reviews.index');
        }
        
        // For non-admin users, show a list of their reviews
        $reviews = Review::where('user_id', auth()->id())
            ->with('product')
            ->latest()
            ->get();
        
        return view('reviews.index', compact('reviews'));
    }

    public function adminIndex()
    {
        // Ensure only admin can access this route
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('admin.reviews.index');
    }

    public function data()
    {
        try {
            // Determine if the current user is an admin
            $isAdmin = auth()->check() && auth()->user()->isAdmin();
            
            // For admin, fetch all reviews with user and product details
            if ($isAdmin) {
                $query = Review::with(['user', 'product']);
            } else {
                // For regular users, fetch only their own reviews
                $query = Review::with(['user', 'product'])
                            ->where('user_id', auth()->id());
            }

            return DataTables::of($query)
                ->addColumn('product_name', function ($review) {
                    return $review->product->name ?? 'N/A';
                })
                ->addColumn('user_name', function ($review) use ($isAdmin) {
                    return $isAdmin ? ($review->user->name ?? 'N/A') : null;
                })
                ->addColumn('rating', function ($review) use ($isAdmin) {
                    return $isAdmin ? null : $review->rating;
                })
                ->addColumn('comment', function ($review) {
                    return $review->comment;
                })
                ->editColumn('created_at', function ($review) {
                    return $review->created_at->format('Y-m-d H:i:s');
                })
                ->addColumn('actions', function($review) {
                    return '<button onclick="deleteReview('.$review->id.')" class="text-red-600 hover:text-red-900 font-medium">Delete</button>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in reviews data: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch reviews',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function adminData()
    {
        try {
            $query = Review::with(['user', 'product']);

            return DataTables::of($query)
                ->addColumn('product_name', function ($review) {
                    return $review->product->name ?? 'N/A';
                })
                ->addColumn('user_name', function ($review) {
                    return $review->user->name ?? 'N/A';
                })
                ->editColumn('created_at', function ($review) {
                    return $review->created_at->format('Y-m-d H:i:s');
                })
                ->addColumn('actions', function($review) {
                    return '<button onclick="deleteReview('.$review->id.')" class="text-red-600 hover:text-red-900 font-medium">Delete</button>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in admin reviews data: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch reviews',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Review $review)
    {
        try {
            $review->delete();
            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting review: ' . $e->getMessage()
            ], 500);
        }
    }

    public function adminDestroy(Review $review)
    {
        // Ensure only admin can delete reviews
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $review->delete();
            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting review: ' . $e->getMessage()
            ], 500);
        }
    }

    public function redirectToProduct(Order $order)
    {
        // Ensure the order belongs to the authenticated user
        if ($order->user_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Unauthorized to review this order.');
        }

        // Check if the order is delivered
        if ($order->status !== 'delivered') {
            return redirect()->back()->with('error', 'You can only review delivered orders.');
        }

        // Find the first product in the order
        $orderItem = $order->items->first();
        if (!$orderItem) {
            return redirect()->back()->with('error', 'No products found in this order.');
        }

        // Redirect to the product page with a review prompt and order ID
        return redirect()->route('shop.show', $orderItem->product)
            ->with('review_prompt', true)
            ->with('order_id', $order->id);
    }

    public function storeProductReview(Request $request, Product $product)
    {
        try {
            $validatedData = $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:500',
                'order_id' => 'required|exists:orders,id'
            ]);

            // Check if the order belongs to the user and is delivered
            $order = Order::where('id', $validatedData['order_id'])
                ->where('user_id', auth()->id())
                ->where('status', 'delivered')
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid order or order is not delivered'
                ], 400);
            }

            // Check if the order contains this product
            $hasProduct = $order->items()->where('product_id', $product->id)->exists();
            if (!$hasProduct) {
                return response()->json([
                    'success' => false,
                    'message' => 'This product is not in the specified order'
                ], 400);
            }

            // Find existing review or create new one
            $review = Review::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'product_id' => $product->id
                ],
                [
                    'rating' => $validatedData['rating'],
                    'comment' => $validatedData['comment'],
                    'order_id' => $validatedData['order_id']
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error in product review store: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit review'
            ], 500);
        }
    }

    public function update(Request $request, Product $product, Review $review)
    {
        try {
            // Verify user owns the review
            if ($review->user_id !== auth()->id() || $review->product_id !== $product->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not found or unauthorized access.'
                ], 404);
            }

            $validatedData = $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'required|string|max:500',
            ]);

            $review->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Review updated successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating review: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update review'
            ], 500);
        }
    }
}
