<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmed</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #4F46E5, #7C3AED);
            color: white;
            padding: 30px 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        .header p {
            margin: 10px 0 0;
            font-size: 18px;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
        }
        .status {
            background-color: #EEF2FF;
            color: #4F46E5;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
            text-align: center;
            border: 1px solid #E0E7FF;
        }
        .status strong {
            display: block;
            font-size: 18px;
            margin-bottom: 5px;
        }
        .order-details {
            margin: 30px 0;
        }
        .order-details h2 {
            color: #4F46E5;
            border-bottom: 2px solid #E0E7FF;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .item {
            padding: 15px;
            background-color: #F9FAFB;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        .item:last-child {
            margin-bottom: 0;
        }
        .total {
            margin-top: 20px;
            padding: 15px;
            background-color: #EEF2FF;
            border-radius: 6px;
            text-align: right;
            font-size: 18px;
            color: #4F46E5;
        }
        .shipping-info {
            margin: 30px 0;
            padding: 20px;
            background-color: #F9FAFB;
            border-radius: 6px;
        }
        .next-steps {
            margin: 30px 0;
        }
        .next-steps h3 {
            color: #4F46E5;
            margin-bottom: 15px;
        }
        .next-steps ol {
            margin: 0;
            padding-left: 20px;
        }
        .next-steps li {
            margin-bottom: 10px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #6B7280;
            font-size: 14px;
            border-top: 1px solid #E5E7EB;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Order Confirmed!</h1>
            <p>Order #{{ $order->id }}</p>
        </div>
        
        <div class="content">
            <div class="greeting">
                <p>Dear {{ $order->user->name }},</p>
                <p>Great news! Your order has been confirmed and is now being processed.</p>
            </div>
            
            <div class="status">
                <strong>Order Status: Confirmed</strong>
                Your order has been approved and will be processed for shipping.
            </div>
            
            <div class="order-details">
                <h2>Order Details</h2>
                
                @foreach($order->items as $item)
                <div class="item">
                    <p>
                        <strong>{{ $item->product->name }}</strong><br>
                        Quantity: {{ $item->quantity }}<br>
                        Price: ${{ number_format($item->price, 2) }}<br>
                        Subtotal: ${{ number_format($item->price * $item->quantity, 2) }}
                    </p>
                </div>
                @endforeach
                
                <div class="total">
                    <strong>Total: ${{ number_format($order->total, 2) }}</strong>
                </div>
            </div>
            
            <div class="shipping-info">
                <h3>Shipping Information</h3>
                <p><strong>Address:</strong><br>
                {{ $order->shipping_address }}</p>
                
                <p><strong>Contact Phone:</strong><br>
                {{ $order->phone }}</p>
            </div>
            
            <div class="next-steps">
                <h3>What happens next?</h3>
                <ol>
                    <li>Our team will prepare your order for shipping</li>
                    <li>You'll receive a shipping confirmation email with tracking information</li>
                    <li>Your items will be delivered to your provided shipping address</li>
                    <li>Once delivered, we'll send you a delivery confirmation</li>
                </ol>
            </div>
        </div>
        
        <div class="footer">
            <p>Thank you for shopping with Lara Shop!</p>
            <p>If you have any questions, please contact our support team.</p>
        </div>
    </div>
</body>
</html>
