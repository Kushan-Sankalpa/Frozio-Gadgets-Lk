<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation</title>
</head>
<body style="margin:0;padding:0;background:#f6f7fb;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f6f7fb;padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:680px;background:#ffffff;border-radius:16px;overflow:hidden;">
                <tr>
                    <td style="background:#111827;color:#ffffff;padding:24px 28px;">
                        <h1 style="margin:0;font-size:24px;line-height:1.3;">Thank you for your order</h1>
                        <p style="margin:8px 0 0;font-size:14px;opacity:0.92;">Order number: <strong>{{ $order->order_number }}</strong></p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:28px;">
                        <p style="margin:0 0 14px;font-size:15px;line-height:1.7;">
                            Hi {{ $order->full_name }}, your order has been confirmed successfully.
                        </p>
                        <p style="margin:0 0 18px;font-size:14px;line-height:1.7;color:#4b5563;">
                            Below is a simple summary of your order.
                        </p>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
                            <thead>
                                <tr>
                                    <th align="left" style="padding:12px;border-bottom:1px solid #e5e7eb;font-size:13px;color:#6b7280;">Product</th>
                                    <th align="center" style="padding:12px;border-bottom:1px solid #e5e7eb;font-size:13px;color:#6b7280;">Qty</th>
                                    <th align="right" style="padding:12px;border-bottom:1px solid #e5e7eb;font-size:13px;color:#6b7280;">Price</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td style="padding:14px 12px;border-bottom:1px solid #f1f5f9;vertical-align:top;">
                                        <div style="font-size:14px;font-weight:600;color:#111827;">{{ $item->product_name }}</div>
                                        @php
                                            $parts = array_filter([
                                                $item->color_name,
                                                $item->storage_label,
                                                $item->size_label,
                                                $item->variant_label,
                                            ]);
                                        @endphp
                                        @if(!empty($parts))
                                            <div style="margin-top:6px;font-size:12px;color:#6b7280;">{{ implode(' • ', $parts) }}</div>
                                        @endif
                                    </td>
                                    <td align="center" style="padding:14px 12px;border-bottom:1px solid #f1f5f9;font-size:14px;">{{ $item->quantity }}</td>
                                    <td align="right" style="padding:14px 12px;border-bottom:1px solid #f1f5f9;font-size:14px;font-weight:600;">Rs {{ number_format((float) $item->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:18px;border-collapse:collapse;">
                            <tr>
                                <td style="padding:8px 0;font-size:14px;color:#4b5563;">Subtotal</td>
                                <td align="right" style="padding:8px 0;font-size:14px;color:#111827;">Rs {{ number_format((float) $order->subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0;font-size:14px;color:#4b5563;">Shipping</td>
                                <td align="right" style="padding:8px 0;font-size:14px;color:#111827;">Rs {{ number_format((float) $order->shipping_fee, 2) }}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px 0 0;font-size:16px;font-weight:700;color:#111827;">Total</td>
                                <td align="right" style="padding:10px 0 0;font-size:18px;font-weight:700;color:#111827;">Rs {{ number_format((float) $order->grand_total, 2) }}</td>
                            </tr>
                        </table>

                        <div style="margin-top:24px;padding:16px;border-radius:12px;background:#f8fafc;">
                            <div style="font-size:13px;color:#6b7280;margin-bottom:8px;">Delivery details</div>
                            <div style="font-size:14px;line-height:1.7;">
                                {{ $order->full_name }}<br>
                                {{ $order->address_line_1 }}
                                @if($order->address_line_2)<br>{{ $order->address_line_2 }}@endif
                                <br>{{ $order->city }}@if($order->postal_code), {{ $order->postal_code }}@endif
                                <br>{{ $order->phone }}
                            </div>
                        </div>

                        <p style="margin:24px 0 0;font-size:13px;line-height:1.7;color:#6b7280;">
                            This email was sent from {{ config('mail.from.address') }}.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
