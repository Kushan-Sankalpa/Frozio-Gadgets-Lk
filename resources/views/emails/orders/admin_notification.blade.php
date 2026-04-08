@php
    $fontUrl = asset('assets/fonts/CustomFontRegular.otf');
    $fontFamily = "'DriftBarberCustomFont', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Arial, sans-serif";
    $accentColor = '#ADD8E6';

    $logoFile = public_path('assets/images/froziohubcolored.png');
    $logoBase64 = null;

    if (file_exists($logoFile)) {
        $extension = strtolower(pathinfo($logoFile, PATHINFO_EXTENSION));
        $mime = match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            default => 'image/png',
        };

        $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoFile));
    }

    $logoSrc = $logoBase64 ?: asset('assets/images/froziohubcolored.png');
    $statusLabel = ucfirst((string) ($order->status ?? 'confirmed'));
    $shippingMethodLabel = $order->shipping_method_name ?: 'Standard Delivery';

    $resolveImage = function (?string $rawImage): ?string {
        if (empty($rawImage)) {
            return null;
        }

        if (\Illuminate\Support\Str::startsWith($rawImage, ['http://', 'https://', 'data:'])) {
            return $rawImage;
        }

        $trimmed = ltrim($rawImage, '/');
        $publicPath = public_path($trimmed);

        if (file_exists($publicPath)) {
            $extension = strtolower(pathinfo($publicPath, PATHINFO_EXTENSION));
            $mime = match ($extension) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'svg' => 'image/svg+xml',
                default => 'image/jpeg',
            };

            return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($publicPath));
        }

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($rawImage)) {
            $diskPath = \Illuminate\Support\Facades\Storage::disk('public')->path($rawImage);

            if (file_exists($diskPath)) {
                $extension = strtolower(pathinfo($diskPath, PATHINFO_EXTENSION));
                $mime = match ($extension) {
                    'jpg', 'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'webp' => 'image/webp',
                    'svg' => 'image/svg+xml',
                    default => 'image/jpeg',
                };

                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($diskPath));
            }

            return url(\Illuminate\Support\Facades\Storage::disk('public')->url($rawImage));
        }

        return url($trimmed);
    };
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Order Received</title>
    <style>
        @font-face {
            font-family: 'DriftBarberCustomFont';
            src: url('{{ $fontUrl }}') format('opentype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f4f6fb;font-family:{{ $fontFamily }};color:#111827;">
<div style="display:none;max-height:0;overflow:hidden;font-size:1px;line-height:1px;color:#f4f6fb;opacity:0;">
    New order received {{ $order->order_number }}
</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6fb;margin:0;padding:24px 12px;font-family:{{ $fontFamily }};">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:760px;background-color:#ffffff;border:1px solid #e5e7eb;border-radius:20px;overflow:hidden;border-top:4px solid {{ $accentColor }};">
                <tr>
                    <td style="padding:28px 28px 22px 28px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td align="left" valign="top" style="padding-right:12px;">
                                    <img src="{{ $logoSrc }}" alt="Frozio Hub" style="display:block;max-width:170px;width:170px;height:auto;border:0;">
                                </td>
                                <td align="right" valign="top">
                                    <div style="font-size:11px;color:#94a3b8;letter-spacing:0.2em;text-transform:uppercase;">Order</div>
                                    <div style="font-size:20px;line-height:1.3;font-weight:700;color:#111827;">{{ $order->order_number }}</div>
                                    <div style="margin-top:6px;display:inline-block;padding:4px 10px;border-radius:999px;background:#f1f5f9;border:1px solid #e2e8f0;font-size:11px;color:#475569;">
                                        Status: {{ $statusLabel }}
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <div style="padding-top:20px;">
                            <div style="font-size:26px;line-height:1.25;font-weight:700;color:#111827;">New order received</div>
                            <div style="padding-top:8px;font-size:14px;line-height:1.7;color:#4b5563;">
                                A new order has been placed on the website. Please review the order summary and delivery details below.
                            </div>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 28px;">
                        <div style="height:1px;background:#eef2f7;"></div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:26px 28px 28px 28px;">
                        <div style="font-size:18px;font-weight:700;color:#111827;padding-bottom:14px;">Order Summary</div>

                        @foreach ($order->items as $item)
                            @php
                                $rawImage = $item->image ?? data_get($item->meta, 'image');
                                $imageUrl = $resolveImage($rawImage);

                                $metaParts = array_filter([
                                    $item->variant_label,
                                    $item->color_name,
                                    $item->storage_label,
                                    $item->size_label,
                                    $item->sku ? 'SKU: ' . $item->sku : null,
                                    $item->product_type ? 'Type: ' . ucfirst($item->product_type) : null,
                                ]);
                            @endphp

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb;border-radius:16px;margin-bottom:14px;background:#ffffff;">
                                <tr>
                                    <td style="padding:16px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td valign="top" style="width:96px;padding-right:14px;">
                                                    @if ($imageUrl)
                                                        <img src="{{ $imageUrl }}" alt="{{ $item->product_name }}" style="display:block;width:84px;height:84px;border-radius:12px;border:1px solid #e5e7eb;object-fit:cover;background:#f8fafc;">
                                                    @else
                                                        <div style="width:84px;height:84px;border-radius:12px;border:1px solid #e5e7eb;background:#f9fafb;text-align:center;line-height:84px;font-size:12px;color:#9ca3af;">
                                                            No Image
                                                        </div>
                                                    @endif
                                                </td>
                                                <td valign="top">
                                                    <div style="font-size:16px;line-height:1.5;font-weight:700;color:#111827;">{{ $item->product_name }}</div>

                                                    @if (!empty($metaParts))
                                                        <div style="padding-top:5px;font-size:13px;line-height:1.7;color:#6b7280;">
                                                            {{ implode(' &bull; ', $metaParts) }}
                                                        </div>
                                                    @endif

                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding-top:12px;">
                                                        <tr>
                                                            <td style="font-size:13px;line-height:1.8;color:#6b7280;">Qty: <strong style="color:#111827;">{{ $item->quantity }}</strong></td>
                                                        </tr>
                                                        <tr>
                                                            <td style="font-size:13px;line-height:1.8;color:#6b7280;">Unit Price: <strong style="color:#111827;">Rs {{ number_format((float) $item->unit_price, 2) }}</strong></td>
                                                        </tr>
                                                        <tr>
                                                            <td style="font-size:13px;line-height:1.8;color:#6b7280;">Line Total: <strong style="color:#111827;">Rs {{ number_format((float) $item->line_total, 2) }}</strong></td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        @endforeach

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:22px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:16px;">
                            <tr>
                                <td style="padding:18px 18px 6px 18px;font-size:16px;font-weight:700;color:#111827;">Order Totals</td>
                            </tr>
                            <tr>
                                <td style="padding:0 18px 18px 18px;">
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td style="padding:8px 0;font-size:14px;color:#6b7280;">Order Status</td>
                                            <td align="right" style="padding:8px 0;font-size:14px;font-weight:700;color:#111827;">{{ $statusLabel }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:8px 0;font-size:14px;color:#6b7280;">Subtotal</td>
                                            <td align="right" style="padding:8px 0;font-size:14px;font-weight:600;color:#111827;">Rs {{ number_format((float) $order->subtotal, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:8px 0;font-size:14px;color:#6b7280;">Delivery Fee</td>
                                            <td align="right" style="padding:8px 0;font-size:14px;font-weight:600;color:#111827;">Rs {{ number_format((float) $order->shipping_fee, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 0 0 0;font-size:16px;font-weight:700;color:#111827;border-top:1px solid #e5e7eb;">Total</td>
                                            <td align="right" style="padding:12px 0 0 0;font-size:18px;font-weight:700;color:#111827;border-top:1px solid #e5e7eb;">Rs {{ number_format((float) $order->grand_total, 2) }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:22px;">
                            <tr>
                                <td valign="top">
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#ffffff;border:1px solid #e5e7eb;border-radius:16px;">
                                        <tr>
                                            <td style="padding:18px;">
                                                <div style="font-size:16px;font-weight:700;color:#111827;padding-bottom:10px;">Delivery Information</div>
                                                <div style="font-size:14px;line-height:1.8;color:#4b5563;">
                                                    <strong style="color:#111827;">{{ $order->full_name }}</strong><br>
                                                    {{ $order->address_line_1 }}
                                                    @if ($order->address_line_2)<br>{{ $order->address_line_2 }}@endif
                                                    <br>{{ $order->city }}@if ($order->postal_code), {{ $order->postal_code }}@endif
                                                    <br>{{ $order->phone }}
                                                    <br>{{ $order->email }}
                                                </div>

                                                @if ($order->delivery_note)
                                                    <div style="padding-top:12px;font-size:13px;line-height:1.7;color:#6b7280;">
                                                        <strong style="color:#111827;">Delivery Note:</strong> {{ $order->delivery_note }}
                                                    </div>
                                                @endif

                                                <div style="margin-top:12px;padding:10px 12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;font-size:13px;line-height:1.6;color:#475569;">
                                                    <strong style="color:#111827;">Shipping Method:</strong> {{ $shippingMethodLabel }}
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <div style="padding-top:22px;font-size:12px;line-height:1.8;color:#9ca3af;">
                            This email was sent automatically when a customer placed an order through the website checkout.
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
