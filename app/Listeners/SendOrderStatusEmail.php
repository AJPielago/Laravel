<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Mail\OrderStatusMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusEmail
{
    public function handle(OrderStatusChanged $event): void
    {
        try {
            $order = $event->order;
            $newStatus = strtolower($event->newStatus);

            // Only send for these statuses (not for confirmed as it's handled by SendOrderConfirmationEmail)
            if (!in_array($newStatus, ['processing', 'shipped', 'delivered', 'cancelled'])) {
                return;
            }

            Log::info('SendOrderStatusEmail listener triggered', [
                'order_id' => $order->id,
                'status' => $newStatus
            ]);

            // Load relationships if they haven't been loaded
            if (!$order->relationLoaded('user')) {
                $order->load('user');
            }
            if (!$order->relationLoaded('items')) {
                $order->load('items.product');
            }

            // Validate order data
            if (!$order->user) {
                throw new \Exception("No user associated with order {$order->id}");
            }

            if (!$order->user->email) {
                throw new \Exception("No email address for user of order {$order->id}");
            }

            // Send the email
            $mail = new OrderStatusMail($order, $newStatus);
            Mail::to($order->user->email)->send($mail);

            Log::info("Order status email sent successfully", [
                'order_id' => $order->id,
                'user_email' => $order->user->email,
                'status' => $newStatus
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send order status email", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $order->id ?? 'unknown',
                'status' => $newStatus ?? 'unknown'
            ]);
        }
    }
}
