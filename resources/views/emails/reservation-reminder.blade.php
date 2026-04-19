<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Reminder</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #7c3aed, #6d28d9); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
        .reservation-info { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .info-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb; }
        .info-row:last-child { border-bottom: none; }
        .label { color: #6b7280; }
        .value { font-weight: 600; }
        .status { display: inline-block; padding: 5px 15px; background: #ede9fe; color: #6d28d9; border-radius: 20px; font-size: 0.875rem; }
        .footer { text-align: center; margin-top: 20px; color: #6b7280; font-size: 0.875rem; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0;">Reservation Reminder</h1>
        <p style="margin: 10px 0 0 0;">Your table is waiting for you!</p>
    </div>
    
    <div class="content">
        <div class="reservation-info">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <strong>Reservation #{{ $reservation->id }}</strong>
                <span class="status">{{ ucfirst($reservation->status) }}</span>
            </div>
            
            <div class="info-row">
                <span class="label">Date</span>
                <span class="value">{{ $reservation->reservation_date->format('F j, Y') }}</span>
            </div>
            
            <div class="info-row">
                <span class="label">Time</span>
                <span class="value">{{ $reservation->reservation_time->format('h:i A') }}</span>
            </div>
            
            <div class="info-row">
                <span class="label">Party Size</span>
                <span class="value">{{ $reservation->party_size }} guests</span>
            </div>
            
            <div class="info-row">
                <span class="label">Table</span>
                <span class="value">{{ $reservation->table?->table_number ?? 'TBA' }}</span>
            </div>
        </div>
        
        <p>We look forward to seeing you! If you need to modify or cancel your reservation, please contact us as soon as possible.</p>
        
        <div class="footer">
            <p>© {{ date('Y') }} Cafe. All rights reserved.</p>
        </div>
    </div>
</body>
</html>