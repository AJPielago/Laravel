<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Mail\OrderReceiptPDF;

class OrderStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $order;
    protected $orderStatus;
    protected $orderMessage;

    public function __construct(Order $order, string $status)
    {
        $this->order = $order;
        $this->orderStatus = strtolower($status);
        $this->orderMessage = match(strtolower($status)) {
            'pending' => 'Your order has been received and is pending confirmation. We will review and confirm it shortly.',
            'confirmed' => 'Great news! Your order has been confirmed. We will begin processing it soon.',
            'processing' => 'Your order is now being processed. We will ship it as soon as it\'s ready.',
            'shipped' => 'Your order is on its way! You will receive tracking information shortly.',
            'delivered' => 'Your order has been delivered. We hope you enjoy your purchase!',
            'cancelled' => 'Your order has been cancelled. Please contact us if you have any questions.',
            default => "Your order status has been updated to {$status}. Please contact us if you have any questions."
        };
    }

    public function build()
    {
        try {
            Log::info("Building order status update email", [
                'order_id' => $this->order->id,
                'status' => $this->orderStatus,
                'user_email' => $this->order->user->email ?? 'no email'
            ]);

            $subject = "Order #{$this->order->id} Status: " . ucfirst($this->orderStatus);

            // Create the base email
            $mail = $this->subject($subject)
                 ->view('emails.order_status')
                 ->with([
                     'order' => $this->order,
                     'user' => $this->order->user,
                     'items' => $this->order->items,
                     'status' => $this->orderStatus,
                     'orderMessage' => $this->orderMessage
                 ]);

            // Attach PDF receipt for confirmed and shipped status
            if (in_array($this->orderStatus, ['confirmed', 'shipped'])) {
                try {
                    $pdfContent = OrderReceiptPDF::generate($this->order);
                    $mail->attachData($pdfContent, "order_{$this->order->id}_receipt.pdf", [
                        'mime' => 'application/pdf'
                    ]);
                    Log::info("PDF receipt attached successfully for {$this->orderStatus} status");
                } catch (\Exception $e) {
                    Log::error("Failed to attach PDF receipt", [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'order_id' => $this->order->id
                    ]);
                }
            }

            return $mail;
        } catch (\Exception $e) {
            Log::error("Failed to build order status email", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $this->order->id ?? 'unknown'
            ]);
            throw $e;
        }
    }
}
