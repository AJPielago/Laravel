<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Review;
use App\Models\Category;

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

    public function categories()
    {
        $categories = Category::withCount('products')
            ->orderBy('name')
            ->paginate(10);

        return view('categories.index', compact('categories'));
    }

    public function createCategory()
    {
        return view('admin.categories.create');
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:categories|max:255',
            'description' => 'nullable|string|max:1000'
        ]);

        Category::create($validated);

        return redirect()->route('admin.categories')
            ->with('success', 'Category created successfully');
    }

    public function editCategory(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function updateCategory(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:1000'
        ]);

        $category->update($validated);

        return redirect()->route('admin.categories')
            ->with('success', 'Category updated successfully');
    }

    public function destroyCategory(Category $category)
    {
        $category->delete();
        return redirect()->route('admin.categories')
            ->with('success', 'Category deleted successfully');
    }
}
