<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        $total = 0;
        
        foreach($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        return view('cart.index', compact('cart', 'total'));
    }

    public function add(Request $request, Product $product)
    {
        $cart = session()->get('cart', []);
        
        if(isset($cart[$product->id])) {
            $cart[$product->id]['quantity']++;
        } else {
            $cart[$product->id] = [
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
                'image' => $product->first_photo
            ];
        }
        
        session()->put('cart', $cart);

        // Check if it's an AJAX request
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Added to cart: {$product->name} - $" . number_format($product->price, 2)
            ]);
        }

        return redirect()->back()->with('success', "Added to cart: {$product->name} - $" . number_format($product->price, 2));
    }

    public function addItem(Request $request, Product $product)
    {
        return $this->add($request, $product);
    }

    public function remove(Product $product)
    {
        $cart = session()->get('cart', []);
        
        if(isset($cart[$product->id])) {
            unset($cart[$product->id]);
            session()->put('cart', $cart);
        }
        
        return redirect()->back();
    }

    public function update(Request $request, Product $product)
    {
        $cart = session()->get('cart', []);
        
        if(isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] = max(1, $request->quantity);
            session()->put('cart', $cart);
        }
        
        return redirect()->back();
    }

    public function checkout()
    {
        $cart = session()->get('cart', []);
        $total = 0;
        
        foreach($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        return view('cart.checkout', compact('cart', 'total'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'shipping_address' => 'required|string',
            'phone' => 'required|string',
        ]);

        $cart = session()->get('cart', []);
        if(empty($cart)) {
            return redirect()->back()->with('error', 'Your cart is empty');
        }

        $total = 0;
        foreach($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $order = Order::create([
            'user_id' => Auth::id(),
            'total' => $total,
            'shipping_address' => $request->shipping_address,
            'phone' => $request->phone,
            'status' => 'pending'
        ]);

        foreach($cart as $id => $details) {
            $order->items()->create([
                'product_id' => $id,
                'quantity' => $details['quantity'],
                'price' => $details['price']
            ]);
        }

        session()->forget('cart');
        session()->flash('checkout_success', [
            'total' => $total,
            'items' => count($cart),
            'order_id' => $order->id
        ]);
        
        return redirect()->route('customer.dashboard');
    }

    private function createOrder($cart, $total)
    {
        // Logic to create an order and return the order instance
    }
}
