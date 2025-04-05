<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class WelcomeController extends Controller
{
    public function __invoke(Request $request)
    {
        // Base query for non-deleted products
        $query = Product::where('is_deleted', false);

        // Apply search if search term is provided
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            
            // Use Eloquent search with multiple columns
            $products = Product::where('is_deleted', false)
                ->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('category', 'LIKE', "%{$searchTerm}%");
                })
                ->orderBy('created_at', 'desc')
                ->paginate(5);
        } else {
            // If no search, fetch paginated products
            $products = $query->orderBy('created_at', 'desc')->paginate(5);
        }

        // Add first photo to each product
        $products->transform(function ($product) {
            // Handle different photo storage formats
            $firstPhoto = null;
            if ($product->photos) {
                // If it's a JSON string, decode it
                if (is_string($product->photos)) {
                    try {
                        $photos = json_decode($product->photos, true);
                        $firstPhoto = $photos[0] ?? null;
                    } catch (\Exception $e) {
                        // If JSON decoding fails, treat it as a single photo path
                        $firstPhoto = $product->photos;
                    }
                } 
                // If it's already an array
                elseif (is_array($product->photos)) {
                    $firstPhoto = $product->photos[0] ?? null;
                }
            }
            $product->first_photo = $firstPhoto;
            return $product;
        });

        return view('welcome', compact('products'));
    }
}
