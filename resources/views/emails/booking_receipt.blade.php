<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Receipt #{{ $booking->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #1a1a1a;
            padding: 20px;
            line-height: 1.6;
        }
        .email-container {
            max-width: 650px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        .header {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 50%, #7f1d1d 100%);
            padding: 50px 40px;
            text-align: center;
            color: #ffffff;
            position: relative;
            overflow: hidden;
        }
        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
        }
        .logo-section {
            margin-bottom: 20px;
            position: relative;
            z-index: 2;
        }
        .salon-name {
            font-size: 36px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 8px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        .salon-tagline {
            font-size: 14px;
            opacity: 0.95;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-weight: 300;
        }
        .receipt-badge {
            display: inline-block;
            background-color: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 12px 24px;
            border-radius: 30px;
            margin-top: 20px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            z-index: 2;
        }
        .receipt-label {
            font-size: 12px;
            opacity: 0.9;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .receipt-number {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .content {
            padding: 45px 40px;
            background-color: #ffffff;
        }
        .greeting {
            font-size: 20px;
            color: #1f2937;
            margin-bottom: 35px;
            font-weight: 600;
        }
        .greeting-name {
            color: #dc2626;
        }
        .section {
            margin-bottom: 40px;
        }
        .section-title {
            font-size: 22px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 3px solid #dc2626;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .summary-grid {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border-radius: 12px;
            padding: 25px;
            border: 2px solid #dc2626;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.15);
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid rgba(220, 38, 38, 0.2);
        }
        .summary-row:last-child {
            border-bottom: none;
            padding-top: 18px;
            margin-top: 10px;
            border-top: 2px solid #dc2626;
            font-weight: 700;
            font-size: 20px;
        }
        .summary-label {
            color: #7f1d1d;
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .summary-value {
            color: #dc2626;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            font-size: 18px;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-scheduled {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-completed {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-pending {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .status-confirmed {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .item-list {
            list-style: none;
        }
        .item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 20px;
            background-color: #ffffff;
            border: 2px solid #fee2e2;
            border-left: 5px solid #dc2626;
            border-radius: 8px;
            margin-bottom: 12px;
            transition: all 0.3s;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }
        .item:hover {
            background-color: #fef2f2;
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.15);
        }
        .item-label {
            color: #374151;
            font-size: 16px;
            font-weight: 500;
        }
        .item-price {
            color: #dc2626;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            font-size: 18px;
        }
        .sales-item {
            padding: 16px 20px;
            background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
            border-left: 4px solid #dc2626;
            border-radius: 8px;
            margin-bottom: 12px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
        }
        .sales-item:hover {
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.15);
            transform: translateX(3px);
        }
        .sales-date {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 8px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .sales-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .sales-amount {
            font-weight: 700;
            color: #dc2626;
            font-variant-numeric: tabular-nums;
            font-size: 18px;
        }
        .sales-method {
            font-size: 13px;
            color: #7f1d1d;
            background-color: #fee2e2;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .footer {
            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
            padding: 40px;
            text-align: center;
            color: #ffffff;
        }
        .barber-pole {
            width: 60px;
            height: 60px;
            margin: 0 auto 20px;
            background: linear-gradient(45deg, 
                #dc2626 25%, 
                #ffffff 25%, 
                #ffffff 50%, 
                #dc2626 50%, 
                #dc2626 75%, 
                #ffffff 75%, 
                #ffffff 100%);
            background-size: 20px 20px;
            border-radius: 50%;
            border: 3px solid #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }
        .thank-you {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #ffffff;
        }
        .footer-tagline {
            font-size: 16px;
            color: #d1d5db;
            margin-bottom: 25px;
            font-style: italic;
        }
        .contact-info {
            font-size: 14px;
            color: #9ca3af;
            line-height: 1.8;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        .contact-info a {
            color: #fca5a5;
            text-decoration: none;
        }
        .divider {
            height: 2px;
            background: linear-gradient(90deg, transparent, #dc2626, transparent);
            margin: 30px 0;
        }
        @media only screen and (max-width: 600px) {
            body {
                padding: 10px;
            }
            .header {
                padding: 35px 25px;
            }
            .salon-name {
                font-size: 28px;
            }
            .salon-tagline {
                font-size: 12px;
            }
            .content {
                padding: 30px 25px;
            }
            .summary-grid {
                padding: 20px;
            }
            .item {
                flex-direction: column;
                align-items: flex-start;
            }
            .item-price {
                margin-top: 8px;
                font-size: 16px;
            }
            .sales-details {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            .footer {
                padding: 30px 25px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header Section -->
        <div class="header">
            <div class="logo-section">
                <div class="salon-name">Drift Barber Saloon</div>
                <div class="salon-tagline">Premium Grooming Experience</div>
            </div>
            <div class="receipt-badge">
                <div class="receipt-label">Booking Receipt</div>
                <div class="receipt-number">#{{ $booking->id }}</div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="content">
            @if($client)
                <div class="greeting">
                    Hello <span class="greeting-name">{{ $client->name ?? $client->full_name ?? 'Valued Customer' }}</span>,
                </div>
                <p style="color: #6b7280; margin-bottom: 35px; font-size: 15px; line-height: 1.7;">
                    Thank you for choosing Drift Barber Saloon. Here's your booking receipt with all the details of your visit.
                </p>
            @endif

            @if($summary)
                <div class="section">
                    <h2 class="section-title">Payment Summary</h2>
                    <div class="summary-grid">
                        <div class="summary-row">
                            <span class="summary-label">Total Amount</span>
                            <span class="summary-value">{{ $currencySymbol }} {{ number_format($summary->total_price ?? 0, 2) }}</span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Amount Paid</span>
                            <span class="summary-value">{{ $currencySymbol }} {{ number_format($summary->total_paid ?? 0, 2) }}</span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Remaining Balance</span>
                            <span class="summary-value">{{ $currencySymbol }} {{ number_format($summary->remaining ?? 0, 2) }}</span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Status</span>
                            <span class="status-badge status-{{ strtolower($summary->status ?? 'scheduled') }}">
                                {{ ucfirst($summary->status ?? 'scheduled') }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            @if(count($services))
                <div class="section">
                    <h2 class="section-title">Services Booked</h2>
                    <ul class="item-list">
                        @foreach($services as $svc)
                            <li class="item">
                                <span class="item-label">{{ $svc['label'] ?? 'Service' }}</span>
                                <span class="item-price">{{ $currencySymbol }} {{ number_format($svc['final_price'] ?? 0, 2) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(count($sales))
                <div class="section">
                    <h2 class="section-title">Payment History</h2>
                    @foreach($sales as $s)
                        <div class="sales-item">
                            <div class="sales-date">
                                {{ \Illuminate\Support\Carbon::parse($s['created_at'])->format('d M Y, H:i') }}
                            </div>
                            <div class="sales-details">
                                <span class="sales-amount">{{ $currencySymbol }} {{ number_format($s['total_with_tip'] ?? $s['total'] ?? 0, 2) }}</span>
                                <span class="sales-method">{{ $s['payment_method'] ?? 'Payment' }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Footer Section -->
        <div class="footer">
            <div class="barber-pole"></div>
            <div class="thank-you">Thank You for Your Visit!</div>
            <div class="footer-tagline">Where Style Meets Precision</div>
            <div class="divider"></div>
            <div class="contact-info">
                <p><strong>Drift Barber Saloon</strong></p>
                <p style="margin-top: 10px;">
                    Questions about your booking?<br>
                    We're here to help!
                </p>
                <p style="margin-top: 15px; font-size: 13px;">
                    This is an automated receipt. Please keep it for your records.
                </p>
            </div>
        </div>
    </div>
</body>
</html>