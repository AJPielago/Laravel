<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ReviewController extends Controller
{
    public function index()
    {
        return view('reviews.index');
    }

    public function store(Request $request, Product $product)
    {
        $order = Order::findOrFail($request->order_id);
        
        if ($order->status !== 'completed') {
            return redirect()->back()->with('error', 'You can only review products from completed orders.');
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10',
            'order_id' => 'required|exists:orders,id'
        ]);

        // Verify user has purchased the product in a completed order
        $hasCompletedOrder = Order::where('user_id', auth()->id())
            ->where('status', 'completed')
            ->whereHas('items', function($query) use ($product) {
                $query->where('product_id', $product->id);
            })->exists();

        if (!$hasCompletedOrder) {
            return redirect()->back()->with('error', 'You can only review products you have purchased.');
        }

        $review = Review::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'product_id' => $product->id,
                'order_id' => $request->order_id
            ],
            [
                'rating' => $request->rating,
                'comment' => $request->comment
            ]
        );

        return redirect()->back()->with('success', 'Review submitted successfully!');
    }

    public function update(Request $request, Review $review)
    {
        $this->authorize('update', $review);
        
        if ($review->order->status !== 'completed') {
            return redirect()->back()->with('error', 'You can only update reviews for completed orders.');
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10'
        ]);

        $review->update($request->only(['rating', 'comment']));

        return redirect()->back()->with('success', 'Review updated successfully!');
    }

    public function destroy(Review $review)
    {
        $this->authorize('delete', $review);
        
        $review->delete();
        return response()->json(['success' => true]);
    }

    public function getReviews()
    {
        $reviews = Review::with(['user', 'product'])->select('reviews.*');

        return DataTables::of($reviews)
            ->addColumn('user_name', function ($review) {
                return $review->user->name;
            })
            ->addColumn('product_name', function ($review) {
                return $review->product->name;
            })
            ->addColumn('actions', function ($review) {
                $deleteBtn = auth()->user()->isAdmin() ? 
                    '<button onclick="deleteReview('.$review->id.')" class="text-red-600 hover:text-red-900">Delete</button>' : '';
                return $deleteBtn;
            })
            ->rawColumns(['actions'])
            ->make(true);
    }
}
