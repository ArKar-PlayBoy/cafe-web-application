<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #16a34a, #15803d); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
        .order-info { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .order-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb; }
        .order-item:last-child { border-bottom: none; }
        .total { font-size: 1.25rem; font-weight: bold; color: #16a34a; }
        .status { display: inline-block; padding: 5px 15px; background: #dcfce7; color: #166534; border-radius: 20px; font-size: 0.875rem; }
        .footer { text-align: center; margin-top: 20px; color: #6b7280; font-size: 0.875rem; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0;">Order Confirmed!</h1>
        <p style="margin: 10px 0 0 0;">Thank you for your order</p>
    </div>
    
    <div class="content">
        <div class="order-info">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <strong>Order #{{ $order->id }}</strong>
                <span class="status">{{ ucfirst($order->status) }}</span>
            </div>
            
            <p><strong>Payment Method:</strong> {{ $order->payment_method_label }}</p>
            <p><strong>Payment Status:</strong> {{ ucfirst($order->payment_status) }}</p>
            
            <h3 style="margin: 20px 0 10px 0;">Order Items</h3>
            @foreach($order->items as $item)
            <div class="order-item">
                <span>{{ $item->quantity }}x {{ $item->menuItem->name }}</span>
                <span>${{ number_format($item->quantity * $item->price, 2) }}</span>
            </div>
            @endforeach
            
            <div class="order-item" style="border-top: 2px solid #e5e7eb; margin-top: 15px; padding-top: 15px;">
                <span style="font-weight: bold;">Total</span>
                <span class="total">${{ number_format($order->total, 2) }}</span>
            </div>
        </div>
        
        <p>We'll notify you when your order is ready for pickup. Thank you for choosing Cafe!</p>
        
        <div class="footer">
            <p>© {{ date('Y') }} Cafe. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
