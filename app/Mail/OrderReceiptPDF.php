<?php

namespace App\Mail;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderReceiptPDF
{
    public static function generate(Order $order)
    {
        $pdf = PDF::loadView('pdfs.order_receipt', [
            'order' => $order,
            'user' => $order->user,
            'items' => $order->items
        ]);

        return $pdf->output();
    }
}
