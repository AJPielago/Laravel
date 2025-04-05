<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Mail\OrderReceiptPDF;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $order;
    protected $status;

    public function __construct(Order $order, string $status = 'confirmed')
    {
        $this->order = $order;
        $this->status = $status;
    }

    public function build()
    {
        try {
            Log::info("Building order confirmation email", [
                'order_id' => $this->order->id,
                'user_email' => $this->order->user->email ?? 'no email',
                'items_count' => $this->order->items->count()
            ]);

            $mail = $this->subject("Order #{$this->order->id} Confirmation")
                        ->view('emails.order_confirmation')
                        ->with([
                            'order' => $this->order,
                            'user' => $this->order->user,
                            'items' => $this->order->items,
                            'status' => $this->status
                        ]);

            // Attach PDF receipt
            try {
                $pdfContent = OrderReceiptPDF::generate($this->order);
                $mail->attachData($pdfContent, "order_{$this->order->id}_receipt.pdf", [
                    'mime' => 'application/pdf'
                ]);
                Log::info("PDF receipt attached successfully");
            } catch (\Exception $e) {
                Log::error("Failed to attach PDF receipt", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'order_id' => $this->order->id
                ]);
            }

            return $mail;
        } catch (\Exception $e) {
            Log::error("Failed to build order confirmation email", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $this->order->id ?? 'unknown'
            ]);
            throw $e;
        }
    }
}
