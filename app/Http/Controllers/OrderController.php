<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Events\OrderPlaced;
use App\Events\OrderStatusChanged;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmationMail;
use App\Mail\OrderReceivedMail;
use App\Mail\OrderStatusMail;

class OrderController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    private $allowedStatusTransitions = [
        'pending' => ['confirmed'],
        'confirmed' => ['shipped'],
        'shipped' => ['delivered'],
        'delivered' => []
    ];

    public function index()
    {
        $orders = Order::with(['user', 'items.product'])->latest()->get();
        return view('orders.index', compact('orders'));
    }

    public function create()
    {
        return view('orders.create');
    }

    public function store(Request $request)
    {
        try {
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

            Log::info("Creating order", [
                'user_id' => Auth::id(),
                'total' => $total,
                'items' => count($cart)
            ]);

            $order = Order::create([
                'user_id' => Auth::id(),
                'total' => $total,
                'shipping_address' => $request->shipping_address,
                'phone' => $request->phone,
                'status' => 'pending'
            ]);

            foreach($cart as $id => $details) {
                // Find the product and check stock
                $product = Product::findOrFail($id);
                
                // Validate stock availability
                if ($product->stock < $details['quantity']) {
                    Log::warning("Insufficient stock for product", [
                        'product_id' => $id,
                        'product_name' => $product->name,
                        'requested_quantity' => $details['quantity'],
                        'available_stock' => $product->stock
                    ]);
                    
                    return redirect()->back()->with('error', 
                        "Insufficient stock for {$product->name}. Only {$product->stock} available."
                    );
                }
                
                // Reduce stock
                $product->decrement('stock', $details['quantity']);
                
                // Create order item
                $order->items()->create([
                    'product_id' => $id,
                    'quantity' => $details['quantity'],
                    'price' => $details['price']
                ]);
            }

            session()->forget('cart');

            Log::info("Order created successfully, sending received email", [
                'order_id' => $order->id
            ]);

            // Send order received email immediately
            try {
                Mail::to($order->user->email)->queue(new OrderReceivedMail($order));
                Log::info("Order received email queued successfully");
            } catch (\Exception $e) {
                Log::error("Order received email failed", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            // Dispatch order placed event
            event(new OrderPlaced($order));

            Log::info("Event dispatched successfully");

            return redirect()->route('customer.dashboard')->with([
                'checkout_success' => [
                    'order_id' => $order->id,
                    'total' => $total,
                    'items' => count($cart)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Order creation failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function updateStatus(Request $request, Order $order)
    {
        try {
            $oldStatus = $order->status;
            $newStatus = strtolower($request->status);
            
            // Validate the status
            if (!in_array($newStatus, ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])) {
                throw new \Exception('Invalid order status.');
            }
            
            // Update the order status
            $order->status = $newStatus;
            $order->save();

            // Make sure relationships are loaded
            $order->load(['user', 'items.product']);
            
            // Send status update email
            if ($oldStatus !== $newStatus) {
                Log::info("Order status changed, sending status update email", [
                    'order_id' => $order->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'is_confirmed' => $newStatus === 'confirmed' ? 'yes' : 'no'
                ]);

                try {
                    // Send email
                    if ($newStatus === 'confirmed') {
                        // For confirmed status, send OrderStatusMail directly
                        Mail::to($order->user->email)->send(new OrderStatusMail($order, $newStatus));
                        Log::info("Confirmed status email sent directly");
                    } else {
                        // For other statuses, queue the email
                        Mail::to($order->user->email)
                            ->queue(new OrderStatusMail($order, $newStatus));
                        Log::info("Status update email queued");
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to send order status email", [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'order_id' => $order->id
                    ]);
                }
            }
            
            // Dispatch event for any additional processing
            event(new OrderStatusChanged($order, $oldStatus, $newStatus));
            
            return redirect()->route('orders.manage', $order)->with([
                'status_change' => [
                    'old' => $oldStatus,
                    'new' => $newStatus,
                    'email_sent' => true
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("Order status update failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $order->id
            ]);
            return redirect()->back()->with('error', 'Failed to update order status: ' . $e->getMessage());
        }
    }

    public function manage(Order $order)
    {
        $order->load(['user', 'items.product']);
        return view('orders.manage', compact('order'));
    }

    public function show(Order $order)
    {
        return view('orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        return view('orders.edit', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        try {
            $oldStatus = strtolower($order->status);
            $newStatus = strtolower($request->status);
            
            // Validate the status
            if (!in_array($newStatus, ['pending', 'confirmed', 'shipped', 'delivered'])) {
                throw new \Exception('Invalid order status.');
            }
            
            $order->update(['status' => $newStatus] + $request->except('status'));
            
            // Always dispatch the event when status changes
            if ($oldStatus !== $newStatus) {
                Log::info("Order status changed, dispatching event", [
                    'order_id' => $order->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus
                ]);
                
                // Make sure relationships are loaded
                $order->load(['user', 'items.product']);
                
                event(new OrderStatusChanged($order, $oldStatus, $newStatus));
            }
            
            return redirect()->route('orders.index')->with('success', 'Order updated successfully!');
        } catch (\Exception $e) {
            Log::error("Order update failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $order->id
            ]);
            return redirect()->back()->with('error', 'Failed to update order: ' . $e->getMessage());
        }
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return redirect()->route('orders.index')->with('success', 'Order deleted successfully!');
    }

    public function history()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with(['items.product'])
            ->latest()
            ->paginate(10);
        
        return view('orders.history', compact('orders'));
    }

    public function cancel(Order $order)
    {
        try {
            // Only allow cancellation of pending orders
            if ($order->status !== 'pending') {
                return redirect()->back()->with('error', 'This order cannot be cancelled.');
            }

            // Restore product stock
            foreach ($order->items as $item) {
                $product = $item->product;
                $product->increment('stock', $item->quantity);
            }

            // Update order status to cancelled
            $order->update(['status' => 'cancelled']);

            return redirect()->route('orders.history')->with('success', 'Order has been cancelled successfully.');
        } catch (\Exception $e) {
            Log::error("Order cancellation failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $order->id
            ]);
            return redirect()->back()->with('error', 'Failed to cancel order: ' . $e->getMessage());
        }
    }
}
