<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Mail\OrderStatusMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusUpdateEmail
{
    public function handle(OrderStatusChanged $event): void
    {
        try {
            $order = $event->order;
            $newStatus = strtolower($event->newStatus);

            // Only handle non-confirmation status changes
            if ($newStatus === 'confirmed' || $newStatus === 'pending') {
                return;
            }

            Log::info('SendOrderStatusUpdateEmail listener triggered', [
                'order_id' => $order->id,
                'old_status' => $event->oldStatus,
                'new_status' => $newStatus
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

            Log::info("Preparing to send status update email", [
                'order_id' => $order->id,
                'user_id' => $order->user->id,
                'user_email' => $order->user->email,
                'status' => $newStatus
            ]);

            // Create and send the email
            $mail = new OrderStatusMail($order, $newStatus);
            Mail::to($order->user->email)->send($mail);

            Log::info("Status update email sent successfully", [
                'order_id' => $order->id,
                'user_email' => $order->user->email,
                'status' => $newStatus,
                'mail_config' => [
                    'driver' => config('mail.default'),
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port')
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send status update email", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $order->id ?? 'unknown',
                'status' => $newStatus ?? 'unknown'
            ]);
            throw $e;
        }
    }
}
