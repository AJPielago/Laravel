<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WelcomeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $products = Product::where('is_deleted', 0)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%")
                        ->orWhereHas('category', function ($categoryQuery) use ($search) {
                            $categoryQuery->where('name', 'LIKE', "%{$search}%");
                        });
                });
            })
            ->with('category') // Eager load category relationship
            ->latest()  // Order by created_at desc
            ->paginate(5); // Change to 5 items per page

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
