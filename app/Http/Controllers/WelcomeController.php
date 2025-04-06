<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->filled('search')) {
            $products = Product::search($request->search)
                ->query(function ($query) {
                    return $query->with('category')
                                ->where('is_deleted', false)
                                ->orderBy('created_at', 'desc');
                })
                ->paginate(12);
        } else {
            $products = Product::with('category')
                             ->where('is_deleted', false)
                             ->latest()
                             ->paginate(12);
        }
        
        return view('welcome', compact('products'));
    }
}
