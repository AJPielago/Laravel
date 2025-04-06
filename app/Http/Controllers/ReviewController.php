<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'order_id' => 'required|exists:orders,id',
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'required|string|min:10',
            ]);

            // Check if user has already reviewed this product
            $existingReview = Review::where('user_id', auth()->id())
                                  ->where('product_id', $validated['product_id'])
                                  ->first();

            if ($existingReview) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already reviewed this product'
                ], 400);
            }

            $review = Review::create([
                'user_id' => auth()->id(),
                'product_id' => $validated['product_id'],
                'order_id' => $validated['order_id'],
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
            ]);

            Log::info('Review created successfully', ['review_id' => $review->id]);

            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Review creation failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit review: ' . $e->getMessage()
            ], 500);
        }
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
}
