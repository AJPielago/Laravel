<!DOCTYPE html>
<html>
<head>
    <title>Order Status Update</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #4338ca; margin: 0; }
        .status { font-size: 18px; color: #4338ca; margin: 20px 0; text-align: center; }
        .message { font-size: 18px; margin: 20px 0; text-align: center; }
        .message .next-step { color: #6366f1; font-style: italic; margin-top: 10px; }
        .order-details { background: #f3f4f6; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .items { margin: 20px 0; }
        .item { padding: 10px 0; border-bottom: 1px solid #ddd; }
        .total { font-weight: bold; margin-top: 20px; text-align: right; }
        .footer { text-align: center; margin-top: 30px; font-size: 14px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Order Status Update</h1>
        </div>

        <div class="status">
            Status: {{ ucfirst($status) }}
        </div>

        <div class="message">
            {!! $orderMessage !!}
        </div>

        <div class="order-details">
            <h2>Order Details</h2>
            <p>Order ID: #{{ $order->id }}</p>
            <p>Date: {{ $order->created_at->format('F j, Y') }}</p>
            <p>Customer: {{ $user->name }}</p>

            <div class="items">
                <h3>Items:</h3>
                @foreach($items as $item)
                    <div class="item">
                        <p>{{ $item->product->name }} x {{ $item->quantity }}</p>
                        <p>Price: ${{ number_format($item->price, 2) }}</p>
                    </div>
                @endforeach
            </div>

            <div class="total">
                Total: ${{ number_format($order->total, 2) }}
            </div>
        </div>

        <div class="footer">
            <p>Thank you for shopping with us!</p>
            <p>If you have any questions, please contact our support team.</p>
        </div>
    </div>
</body>
</html>
