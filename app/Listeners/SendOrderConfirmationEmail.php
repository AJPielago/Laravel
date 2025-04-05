<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Events\OrderStatusChanged;
use App\Mail\OrderConfirmationMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmationEmail
{
    public function handle($event): void
    {
        try {
            Log::info('SendOrderConfirmationEmail listener triggered', [
                'event_class' => get_class($event),
                'order_id' => $event->order->id ?? 'unknown'
            ]);

            $order = $event->order;

            // For OrderStatusChanged events, only send when status becomes confirmed
            if ($event instanceof OrderStatusChanged) {
                if (strtolower($event->newStatus) !== 'confirmed') {
                    Log::info("Skipping email - status not confirmed", [
                        'order_id' => $order->id,
                        'new_status' => $event->newStatus
                    ]);
                    return;
                }

                Log::info("Order status changed to confirmed", [
                    'order_id' => $order->id,
                    'old_status' => $event->oldStatus,
                    'new_status' => $event->newStatus
                ]);
            }

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

            Log::info("Preparing to send order confirmation email", [
                'order_id' => $order->id,
                'user_id' => $order->user->id,
                'user_email' => $order->user->email,
                'items_count' => $order->items->count()
            ]);

            // Try rendering the email first to catch any view errors
            $mail = new OrderConfirmationMail($order);
            $html = $mail->render();

            Log::info("Email template rendered successfully", [
                'order_id' => $order->id,
                'html_length' => strlen($html)
            ]);

            // Send the email
            Mail::to($order->user->email)->send($mail);

            Log::info("Order confirmation email sent successfully", [
                'order_id' => $order->id,
                'user_email' => $order->user->email,
                'mail_config' => [
                    'driver' => config('mail.default'),
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                    'encryption' => config('mail.mailers.smtp.encryption'),
                    'from_address' => config('mail.from.address'),
                    'from_name' => config('mail.from.name')
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send order confirmation email", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $order->id ?? 'unknown',
                'mail_config' => [
                    'driver' => config('mail.default'),
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                    'encryption' => config('mail.mailers.smtp.encryption'),
                    'from_address' => config('mail.from.address'),
                    'from_name' => config('mail.from.name')
                ]
            ]);
            throw $e;
        }
    }
}
