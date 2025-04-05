<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class OrderReceivedMail extends Mailable
{
    public $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function build()
    {
        return $this->subject("Order #{$this->order->id} Received")
                    ->view('emails.order_received')
                    ->with(['order' => $this->order]);
    }
}
