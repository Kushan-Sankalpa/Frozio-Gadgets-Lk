<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $statusLabel }} Order</title>
</head>
<body style="margin:0;padding:0;background:#f6f7fb;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f6f7fb;padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:680px;background:#ffffff;border-radius:16px;overflow:hidden;">
                <tr>
                    <td style="background:#111827;color:#ffffff;padding:24px 28px;">
                        <h1 style="margin:0;font-size:24px;line-height:1.3;">Your order is now {{ strtolower($statusLabel) }}</h1>
                        <p style="margin:8px 0 0;font-size:14px;opacity:0.92;">Invoice number: <strong>{{ $invoice->invoice_no }}</strong></p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:28px;">
                        <p style="margin:0 0 14px;font-size:15px;line-height:1.7;">
                            Hi {{ $invoice->customer_name }},
                        </p>

                        @if ($orderStatus === 'confirmed')
                            <p style="margin:0 0 18px;font-size:14px;line-height:1.7;color:#4b5563;">
                                Your order has been confirmed successfully. We have attached the latest invoice PDF to this email.
                            </p>
                        @elseif ($orderStatus === 'dispatched')
                            <p style="margin:0 0 18px;font-size:14px;line-height:1.7;color:#4b5563;">
                                Your order has been dispatched. We have attached the updated invoice PDF and included your tracking details below.
                            </p>
                        @elseif ($orderStatus === 'delivered')
                            <p style="margin:0 0 18px;font-size:14px;line-height:1.7;color:#4b5563;">
                                Your order has been marked as delivered. The invoice has been updated and attached as a PDF.
                            </p>
                        @elseif ($orderStatus === 'cancelled')
                            <p style="margin:0 0 18px;font-size:14px;line-height:1.7;color:#4b5563;">
                                Your order has been cancelled. Please find the latest invoice PDF attached for reference.
                            </p>
                        @else
                            <p style="margin:0 0 18px;font-size:14px;line-height:1.7;color:#4b5563;">
                                Your order status has been updated.
                            </p>
                        @endif

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin-bottom:20px;">
                            <tr>
                                <td style="padding:8px 0;font-size:14px;color:#4b5563;">Order Status</td>
                                <td align="right" style="padding:8px 0;font-size:14px;font-weight:600;color:#111827;">{{ $statusLabel }}</td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0;font-size:14px;color:#4b5563;">Invoice Status</td>
                                <td align="right" style="padding:8px 0;font-size:14px;font-weight:600;color:#111827;">{{ ucfirst((string) $invoice->status) }}</td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0;font-size:14px;color:#4b5563;">Payment Type</td>
                                <td align="right" style="padding:8px 0;font-size:14px;font-weight:600;color:#111827;">{{ ucfirst(str_replace('_', ' ', (string) $invoice->payment_type)) }}</td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0;font-size:14px;color:#4b5563;">Grand Total</td>
                                <td align="right" style="padding:8px 0;font-size:14px;font-weight:600;color:#111827;">Rs {{ number_format((float) $invoice->grand_total, 2) }}</td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0;font-size:14px;color:#4b5563;">Balance Due</td>
                                <td align="right" style="padding:8px 0;font-size:14px;font-weight:600;color:#111827;">Rs {{ number_format((float) $invoice->balance_due, 2) }}</td>
                            </tr>
                        </table>

                        @if ($orderStatus === 'dispatched')
                            <div style="margin:0 0 20px;padding:16px;border-radius:12px;background:#eff6ff;border:1px solid #bfdbfe;">
                                <div style="font-size:16px;font-weight:700;color:#1d4ed8;margin-bottom:8px;">Tracking Details</div>
                                <div style="font-size:14px;line-height:1.8;color:#1f2937;">
                                    <strong>Tracking ID:</strong> {{ $invoice->tracking_id ?: '-' }}<br>
                                    <strong>Delivery Company:</strong> {{ $invoice->delivery_agent ? ucfirst($invoice->delivery_agent) : '-' }}
                                </div>
                            </div>
                        @endif

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
                            <thead>
                                <tr>
                                    <th align="left" style="padding:12px;border-bottom:1px solid #e5e7eb;font-size:13px;color:#6b7280;">Product</th>
                                    <th align="center" style="padding:12px;border-bottom:1px solid #e5e7eb;font-size:13px;color:#6b7280;">Qty</th>
                                    <th align="right" style="padding:12px;border-bottom:1px solid #e5e7eb;font-size:13px;color:#6b7280;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($invoice->items as $item)
                                <tr>
                                    <td style="padding:14px 12px;border-bottom:1px solid #f1f5f9;vertical-align:top;">
                                        <div style="font-size:14px;font-weight:600;color:#111827;">{{ $item->model_name ?: $item->description }}</div>
                                        <div style="margin-top:6px;font-size:12px;color:#6b7280;">{{ $item->description }}</div>
                                    </td>
                                    <td align="center" style="padding:14px 12px;border-bottom:1px solid #f1f5f9;font-size:14px;">{{ $item->qty }}</td>
                                    <td align="right" style="padding:14px 12px;border-bottom:1px solid #f1f5f9;font-size:14px;font-weight:600;">Rs {{ number_format((float) $item->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <div style="margin-top:24px;padding:16px;border-radius:12px;background:#f8fafc;">
                            <div style="font-size:13px;color:#6b7280;margin-bottom:8px;">Delivery details</div>
                            <div style="font-size:14px;line-height:1.7;">
                                {{ $invoice->customer_name }}<br>
                                {{ $invoice->customer_address ?: '-' }}<br>
                                {{ $invoice->customer_contact_number }}
                            </div>
                        </div>

                        <p style="margin:24px 0 0;font-size:13px;line-height:1.7;color:#6b7280;">
                            This email was sent automatically from {{ config('mail.from.address') }}.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
