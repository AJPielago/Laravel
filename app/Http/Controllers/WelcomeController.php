<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WelcomeController extends Controller
{
    public function index(Request $request)
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
            $products = $query->orderBy('created_at', 'desc')->paginate(5);
        }

        // Add first photo to each product
        $products->transform(function ($product) {
            $firstPhoto = null;
            if ($product->photos) {
                if (is_string($product->photos)) {
                    try {
                        $photos = json_decode($product->photos, true);
                        $firstPhoto = $photos[0] ?? null;
                    } catch (\Exception $e) {
                        $firstPhoto = $product->photos;
                    }
                } elseif (is_array($product->photos)) {
                    $firstPhoto = $product->photos[0] ?? null;
                }
            }
            $product->first_photo = $firstPhoto;
            return $product;
        });

        return view('welcome', compact('products'));
    }
}
