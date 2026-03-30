<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Order Received</title>
</head>
<body style="margin:0;padding:0;background:#f6f8fb;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <div style="max-width:760px;margin:0 auto;padding:32px 16px;">
        <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:18px;overflow:hidden;">
            <div style="background:#14532d;padding:24px 28px;">
                <h1 style="margin:0;font-size:24px;line-height:1.3;color:#ffffff;">New Order Received</h1>
                <p style="margin:8px 0 0 0;font-size:14px;color:#dcfce7;">Order Number: <strong style="color:#ffffff;">{{ $order->order_number }}</strong></p>
            </div>

            <div style="padding:28px;">
                <div style="display:block;margin-bottom:24px;">
                    <h2 style="margin:0 0 12px 0;font-size:18px;">Customer Details</h2>
                    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                        <tr>
                            <td style="padding:8px 0;width:180px;font-size:14px;color:#475569;">Customer Name</td>
                            <td style="padding:8px 0;font-size:14px;font-weight:600;">{{ $order->full_name }}</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 0;font-size:14px;color:#475569;">Email</td>
                            <td style="padding:8px 0;font-size:14px;font-weight:600;">{{ $order->email }}</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 0;font-size:14px;color:#475569;">Phone</td>
                            <td style="padding:8px 0;font-size:14px;font-weight:600;">{{ $order->phone }}</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 0;font-size:14px;color:#475569;">Shipping Method</td>
                            <td style="padding:8px 0;font-size:14px;font-weight:600;">{{ $order->shipping_method_name }}</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 0;font-size:14px;color:#475569;">Address</td>
                            <td style="padding:8px 0;font-size:14px;font-weight:600;">
                                {{ $order->address_line_1 }}
                                @if ($order->address_line_2)
                                    , {{ $order->address_line_2 }}
                                @endif
                                , {{ $order->city }}
                                @if ($order->postal_code)
                                    , {{ $order->postal_code }}
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>

                @if ($order->delivery_note)
                    <div style="margin-bottom:24px;padding:16px;background:#fff7ed;border:1px solid #fed7aa;border-radius:12px;">
                        <h3 style="margin:0 0 8px 0;font-size:16px;">Delivery Note</h3>
                        <p style="margin:0;font-size:14px;line-height:1.7;">{{ $order->delivery_note }}</p>
                    </div>
                @endif

                <h2 style="margin:24px 0 12px 0;font-size:18px;">Order Items</h2>

                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th align="left" style="padding:12px;border-bottom:1px solid #e2e8f0;font-size:13px;">Product</th>
                            <th align="center" style="padding:12px;border-bottom:1px solid #e2e8f0;font-size:13px;">Qty</th>
                            <th align="right" style="padding:12px;border-bottom:1px solid #e2e8f0;font-size:13px;">Unit Price</th>
                            <th align="right" style="padding:12px;border-bottom:1px solid #e2e8f0;font-size:13px;">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td style="padding:14px 12px;border-bottom:1px solid #f1f5f9;vertical-align:top;">
                                    <div style="font-size:14px;font-weight:700;">{{ $item->product_name }}</div>

                                    @php
                                        $metaParts = array_filter([
                                            $item->variant_label,
                                            $item->color_name,
                                            $item->storage_label,
                                            $item->size_label,
                                            $item->sku ? 'SKU: ' . $item->sku : null,
                                            $item->product_type ? 'Type: ' . $item->product_type : null,
                                        ]);
                                    @endphp

                                    @if (!empty($metaParts))
                                        <div style="margin-top:4px;font-size:12px;color:#64748b;">
                                            {{ implode(' • ', $metaParts) }}
                                        </div>
                                    @endif
                                </td>
                                <td align="center" style="padding:14px 12px;border-bottom:1px solid #f1f5f9;font-size:14px;">{{ $item->quantity }}</td>
                                <td align="right" style="padding:14px 12px;border-bottom:1px solid #f1f5f9;font-size:14px;">Rs {{ number_format((float) $item->unit_price, 2) }}</td>
                                <td align="right" style="padding:14px 12px;border-bottom:1px solid #f1f5f9;font-size:14px;font-weight:700;">Rs {{ number_format((float) $item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div style="margin-top:24px;padding-top:16px;border-top:1px solid #e2e8f0;">
                    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                        <tr>
                            <td style="padding:6px 0;font-size:14px;color:#475569;">Subtotal</td>
                            <td align="right" style="padding:6px 0;font-size:14px;font-weight:600;">Rs {{ number_format((float) $order->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td style="padding:6px 0;font-size:14px;color:#475569;">Shipping</td>
                            <td align="right" style="padding:6px 0;font-size:14px;font-weight:600;">Rs {{ number_format((float) $order->shipping_fee, 2) }}</td>
                        </tr>
                        <tr>
                            <td style="padding:10px 0 0 0;font-size:16px;font-weight:700;">Grand Total</td>
                            <td align="right" style="padding:10px 0 0 0;font-size:18px;font-weight:700;">Rs {{ number_format((float) $order->grand_total, 2) }}</td>
                        </tr>
                    </table>
                </div>

                <div style="margin-top:24px;padding:14px 16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;font-size:13px;color:#475569;">
                    This email was sent automatically when a customer placed an order on the frontend checkout.
                </div>
            </div>
        </div>
    </div>
</body>
</html>
