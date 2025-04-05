<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Review;

class AdminController extends Controller
{
    public function orders()
    {
        $orders = Order::all();
        return view('orders.manage', compact('orders')); // Changed from 'orders.index' to 'orders.manage'
    }

    public function products(Request $request)
    {
        $products = Product::all(); // Show all products
        return view('products.index', compact('products'));
    }

    public function users()
    {
        $users = User::all();
        return view('users.index', compact('users'));
    }

    public function manage()
    {
        return view('admin.manage');
    }

    public function reviews()
    {
        $reviews = Review::with(['user', 'product'])->get();
        return view('admin.reviews.index', compact('reviews'));
    }
}
