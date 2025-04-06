<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;  // Add this line
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        $products = $query->where('is_deleted', false)
                         ->latest()
                         ->paginate(10);

        return view('products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        // Check if the current user is an admin
        if (!Auth::user() || Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'name' => 'required',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id', // Change this line
            'photos' => 'nullable|array',
            'photos.*' => ['nullable', 'file', function($attribute, $value, $fail) {
                // Check if the file is an image
                $allowedMimes = ['jpg', 'jpeg', 'png'];
                $extension = strtolower($value->getClientOriginalExtension());
                
                // Check file extension
                if (!in_array($extension, $allowedMimes)) {
                    $fail("The $attribute must be a file of type: " . implode(', ', $allowedMimes) . '.');
                }

                // Check if it's a valid image
                if (!getimagesize($value)) {
                    $fail("The $attribute must be a valid image file.");
                }

                // Optional: Check file size
                if ($value->getSize() > 2048 * 1024) {
                    $fail("The $attribute must not be larger than 2MB.");
                }
            }],
        ], [
            'photos.*.file' => 'Each uploaded file must be a valid image.',
            'photos.*.mimes' => 'Images must be of type: jpg, jpeg, or png.',
            'photos.*.max' => 'Each image must not exceed 2MB.',
            'category_id.required' => 'Please select a category',
            'category_id.exists' => 'The selected category does not exist'
        ]);

        // Log all incoming request data for debugging
        Log::info('Product Store Request', [
            'input' => $request->all(),
            'files' => $request->hasFile('photos') ? 
                array_map(function($file) { 
                    return [
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime' => $file->getMimeType()
                    ]; 
                }, $request->file('photos')) : 
                'No files'
        ]);

        $product = new Product($request->except('photos'));

        $product->category_id = $validated['category_id']; // Make sure this matches

        if ($request->hasFile('photos')) {
            $photoPaths = [];
            $photoOrder = json_decode($request->input('photo_order', '[]'), true);
            $uploadedFiles = $request->file('photos');
            
            // Log photo order and uploaded files
            Log::info('Photo Upload Debug', [
                'photo_order' => $photoOrder,
                'uploaded_files_count' => count($uploadedFiles)
            ]);
            
            // Create a map of filename to file for easy lookup
            $fileMap = [];
            foreach ($uploadedFiles as $file) {
                $fileMap[$file->getClientOriginalName()] = $file;
            }
            
            // Store files in the order specified by photo_order
            foreach ($photoOrder as $filename) {
                if (isset($fileMap[$filename])) {
                    $photo = $fileMap[$filename];
                    $filePath = $photo->storeAs('products', $filename, 'public');
                    $photoPaths[] = $filePath;
                    
                    Log::info('Stored Photo', [
                        'original_name' => $filename,
                        'stored_path' => $filePath
                    ]);
                }
            }
            
            // Fallback: if no files stored via photo_order, store all files
            if (empty($photoPaths)) {
                foreach ($uploadedFiles as $photo) {
                    $filename = $photo->getClientOriginalName();
                    $filePath = $photo->storeAs('products', $filename, 'public');
                    $photoPaths[] = $filePath;
                    
                    Log::info('Fallback Stored Photo', [
                        'original_name' => $filename,
                        'stored_path' => $filePath
                    ]);
                }
            }
            
            // Ensure photos are stored as a JSON array
            $product->photos = json_encode($photoPaths);
            
            Log::info('Product Photos Before Save', [
                'product_id' => $product->id,
                'photos_json' => $product->photos,
                'photos_array' => $photoPaths
            ]);
        } else {
            $product->photos = json_encode([]);
        }

        $product->save();

        // Log product after save
        Log::info('Product After Save', [
            'product_id' => $product->id,
            'photos' => $product->photos
        ]);

        $details = "Name: {$product->name}\nPrice: $" . number_format($product->price, 2) . "\nStock: {$product->stock}";
        return redirect()->route('products.index')
            ->with('success', 'Product successfully added!')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Product Added Successfully',
                'details' => $details
            ]);
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'existing_photos' => 'nullable|string',
            'deleted_photos' => 'nullable|string',
        ]);

        // Log request data for debugging
        Log::info('Product Update Request', [
            'product_id' => $product->id,
            'data' => $request->all(),
            'files' => $request->hasFile('photos') ? count($request->file('photos')) : 0,
            'existing_photos' => $request->input('existing_photos'),
            'deleted_photos' => $request->input('deleted_photos')
        ]);

        // Parse existing and deleted photos
        $existingPhotos = json_decode($request->input('existing_photos', '[]'), true);
        $deletedPhotos = json_decode($request->input('deleted_photos', '[]'), true);

        // Remove deleted photos
        foreach ($deletedPhotos as $photoPath) {
            if (Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
                Log::info('Deleted photo', ['path' => $photoPath]);
            }
        }

        // Handle new photo uploads
        $photoPaths = is_array($existingPhotos) ? $existingPhotos : [];
        $photoPaths = array_values(array_diff($photoPaths, $deletedPhotos)); // Remove deleted photos from the list
        
        $changes = [];
        
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $filename = time() . '_' . $photo->getClientOriginalName();
                $path = $photo->storeAs('products', $filename, 'public');
                $photoPaths[] = $path;
            }
            $changes[] = 'Added ' . count($request->file('photos')) . ' new photo(s)';
        }

        if (count($deletedPhotos) > 0) {
            $changes[] = 'Removed ' . count($deletedPhotos) . ' photo(s)';
        }

        // Update product with validated data
        $product->update([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
            'price' => $validatedData['price'],
            'stock' => $validatedData['stock'],
            'category_id' => $validatedData['category_id'],
            'photos' => json_encode($photoPaths) // Store photos as JSON string
        ]);

        // Log after update
        Log::info('Product updated', [
            'product_id' => $product->id,
            'changes' => $changes,
            'photos' => $product->photos
        ]);

        $details = "Name: {$product->name}\nPrice: $" . number_format($product->price, 2) . "\nStock: {$product->stock}";
        if (!empty($changes)) {
            $details .= "\nChanges: " . implode(", ", $changes);
        }

        return redirect()->route('products.index')
            ->with('success', 'Product successfully updated!')
            ->with('toast', [
                'type' => 'success',
                'message' => count($changes) > 0 ? 'Product Updated Successfully' : 'No changes made',
                'details' => $details
            ]);
    }

    public function destroy(Product $product)
    {
        $productName = $product->name;
        $product->is_deleted = true;
        $product->save();
        
        return redirect()->route('products.index')
            ->with('success', 'Product deactivated successfully!')
            ->with('toast', [
                'type' => 'warning',
                'message' => 'Product Deactivated',
                'details' => "Product: {$productName}\nStatus: Deactivated\nNote: Product will not be visible in shop"
            ]);
    }

    public function restore($id)
    {
        $product = Product::find($id);
        $productName = $product->name;
        $product->is_deleted = false;
        $product->save();
        
        return redirect()->route('products.index')
            ->with('success', 'Product reactivated successfully!')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Product Reactivated Successfully',
                'details' => "Product: {$productName}\nStatus: Active"
            ]);
    }

    public function show(Product $product)
    {
        // Check if the product is deleted or out of stock
        if ($product->is_deleted) {
            abort(404, 'Product not found');
        }

        return view('shop.show', compact('product'));
    }

    public function getData()
    {
        try {
            $products = Product::with('category');

            return DataTables::of($products)
                ->addColumn('photo', function ($product) {
                    if ($product->photo) {
                        return '<img src="' . asset('storage/' . $product->photo) . '" alt="Product" class="w-16 h-16 object-cover rounded-lg shadow-sm">';
                    }
                    return '<div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>';
                })
                ->addColumn('category', function ($product) {
                    return $product->category ? $product->category->name : 'N/A';
                })
                ->addColumn('action', function ($product) {
                    return '
                        <div class="flex space-x-2">
                            <a href="' . route('products.edit', $product->id) . '" class="text-indigo-600 hover:text-indigo-900 font-medium">Edit</a>
                            <form action="' . route('products.destroy', $product->id) . '" method="POST" class="inline">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="submit" class="text-red-600 hover:text-red-900 font-medium ml-2">Delete</button>
                            </form>
                        </div>
                    ';
                })
                ->rawColumns(['photo', 'action'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in getData: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch data'], 500);
        }
    }
}