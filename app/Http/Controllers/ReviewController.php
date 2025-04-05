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

    public function data()
    {
        try {
            $reviews = Review::with(['user', 'product'])->get();
            
            return DataTables::of($reviews)
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
                ->toJson();
        } catch (\Exception $e) {
            Log::error('Error in reviews data: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch reviews'], 500);
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
}
