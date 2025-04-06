<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class CategoryController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function index()
    {
        Log::info('Categories index accessed');
        
        $categories = Category::withCount('products')
            ->orderBy('name')
            ->paginate(10);
        
        Log::info('Categories fetched', ['count' => $categories->count()]);
        
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:categories|max:255',
            'description' => 'nullable|string|max:1000'
        ]);

        Category::create($validated);

        return redirect()->route('categories.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Category Created',
                'details' => "Name: {$validated['name']}",
                'style' => 'gradient' // Add this to ensure gradient styling
            ]);
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|unique:categories,name,' . $category->id,
                'description' => 'nullable|string|max:1000'
            ]);

            $category->update($validated);

            return redirect()->route('categories.index')
                ->with('toast', [
                    'type' => 'success',
                    'message' => 'Category Updated',
                    'details' => "Category: {$category->name}"
                ]);

        } catch (\Exception $e) {
            Log::error('Category update failed', [
                'category_id' => $category->id,
                'error' => $e->getMessage()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to update category')
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Update Failed',
                    'details' => $e->getMessage()
                ]);
        }
    }

    public function destroy(Category $category)
    {
        try {
            // Check if the category has any associated products
            if ($category->products()->count() > 0) {
                return redirect()->route('categories.index')
                    ->with('error', 'Cannot delete category with existing products');
            }

            $category->delete();

            return redirect()->route('categories.index')
                ->with('toast', [
                    'type' => 'success',
                    'message' => 'Category Deleted',
                    'details' => "Name: {$category->name}"
                ]);
        } catch (\Exception $e) {
            Log::error('Category deletion error', [
                'category_id' => $category->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('categories.index')
                ->with('error', 'Failed to delete category')
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Deletion Failed',
                    'details' => $e->getMessage()
                ]);
        }
    }
}
