<!DOCTYPE html>
<html>
<head>
    <title>Order Received</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(to right, #4F46E5, #7C3AED);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 0 0 8px 8px;
        }
        .order-details {
            margin: 20px 0;
        }
        .item {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .total {
            margin-top: 20px;
            font-weight: bold;
            text-align: right;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            color: #666;
        }
        .status {
            background-color: #FEF3C7;
            color: #92400E;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Order Received</h1>
            <p>Order #{{ $order->id }}</p>
        </div>
        
        <div class="content">
            <p>Dear {{ $order->user->name }},</p>
            
            <p>Thank you for your order! We have received your order and it is currently being reviewed.</p>
            
            <div class="status">
                <strong>Order Status: Pending Review</strong><br>
                We will send you another email once your order is confirmed.
            </div>
            
            <div class="order-details">
                <h2>Order Details:</h2>
                
                @foreach($order->items as $item)
                <div class="item">
                    <p>
                        <strong>{{ $item->product->name }}</strong><br>
                        Quantity: {{ $item->quantity }}<br>
                        Price: ${{ number_format($item->price, 2) }}
                    </p>
                </div>
                @endforeach
                
                <div class="total">
                    Total: ${{ number_format($order->total, 2) }}
                </div>
            </div>
            
            <p><strong>Shipping Address:</strong><br>
            {{ $order->shipping_address }}</p>
            
            <p><strong>Contact Phone:</strong><br>
            {{ $order->phone }}</p>
            
            <p>What happens next?</p>
            <ol>
                <li>Our team will review your order</li>
                <li>Once confirmed, we'll send you a confirmation email</li>
                <li>Your order will then be processed for shipping</li>
            </ol>
        </div>
        
        <div class="footer">
            <p>If you have any questions about your order, please contact our customer service.</p>
            <p>Thank you for shopping with us!</p>
        </div>
    </div>
</body>
</html>
