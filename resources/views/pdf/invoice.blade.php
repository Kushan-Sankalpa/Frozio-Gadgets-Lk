<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $invoice->invoice_no }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
            font-size: 12px;
            margin: 24px;
        }
        .header-table,
        .meta-table,
        .items-table,
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: top;
        }
        .logo-wrap {
            text-align: center;
        }
        .logo-wrap img {
            max-width: 120px;
            max-height: 80px;
        }
        .invoice-title {
            font-size: 26px;
            font-weight: 700;
            text-align: right;
            margin-bottom: 8px;
        }
        .muted {
            color: #6b7280;
        }
        .section-title {
            font-size: 13px;
            font-weight: 700;
            margin: 18px 0 8px;
        }
        .items-table th,
        .items-table td {
            border: 1px solid #d1d5db;
            padding: 8px;
        }
        .items-table th {
            background: #f3f4f6;
            text-align: left;
            font-weight: 700;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .summary-wrap {
            margin-top: 14px;
            width: 100%;
        }
        .summary-table {
            width: 320px;
            margin-left: auto;
        }
        .summary-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        .summary-table tr:last-child td {
            font-weight: 700;
            font-size: 13px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 11px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td width="34%">
                @foreach(($shop['address_lines'] ?? []) as $line)
                    <div>{{ $line }}</div>
                @endforeach
                @if(!empty($shop['phone']))
                    <div>Phone: {{ $shop['phone'] }}</div>
                @endif
            </td>

            <td width="32%" class="logo-wrap">
                @if(!empty($shop['logo_url']))
                    <img src="{{ $shop['logo_url'] }}" alt="Logo">
                @endif
            </td>

            <td width="34%">
                <div class="invoice-title">INVOICE</div>
                <table class="meta-table">
                    <tr>
                        <td class="muted">Date</td>
                        <td class="text-right">{{ optional($invoice->invoice_date)->format('Y-m-d') }}</td>
                    </tr>
                    <tr>
                        <td class="muted">Invoice No</td>
                        <td class="text-right">{{ $invoice->invoice_no }}</td>
                    </tr>
                    <tr>
                        <td class="muted">Payment</td>
                        <td class="text-right">{{ ucfirst($invoice->payment_type ?? '-') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="section-title">Bill To</div>
    <table class="meta-table">
        <tr>
            <td width="50%">
                <div><strong>Name:</strong> {{ $invoice->customer_name }}</div>
                <div><strong>Contact:</strong> {{ $invoice->customer_contact_number }}</div>
                @if($invoice->customer_address)
                    <div><strong>Address:</strong> {{ $invoice->customer_address }}</div>
                @endif
                @if($invoice->customer_email)
                    <div><strong>Email:</strong> {{ $invoice->customer_email }}</div>
                @endif
            </td>
            <td width="50%">
                @if($invoice->sales_person)
                    <div><strong>Sales Person:</strong> {{ $invoice->sales_person }}</div>
                @endif
                @if($invoice->ship_date)
                    <div><strong>Ship Date:</strong> {{ optional($invoice->ship_date)->format('Y-m-d') }}</div>
                @endif
                @if($invoice->ship_via)
                    <div><strong>Ship Via:</strong> {{ $invoice->ship_via }}</div>
                @endif
            </td>
        </tr>
    </table>

    <div class="section-title">Items</div>
    <table class="items-table">
        <thead>
            <tr>
                <th width="7%">ITEM</th>
                <th width="47%">DESCRIPTION</th>
                <th width="8%" class="text-center">QTY</th>
                <th width="14%" class="text-right">UNIT PRICE</th>
                <th width="8%" class="text-center">%</th>
                <th width="16%" class="text-right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td class="text-center">{{ $item->item_no }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="text-center">{{ $item->qty }}</td>
                    <td class="text-right">{{ number_format((float) $item->regular_price, 2) }}</td>
                    <td class="text-center">
                        {{ $item->discount_type === 'percentage' ? rtrim(rtrim(number_format((float) ($item->discount_percent_display ?? $item->discount_value), 2), '0'), '.') : '-' }}
                    </td>
                    <td class="text-right">{{ number_format((float) $item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-wrap">
        <table class="summary-table">
            <tr>
                <td>Subtotal</td>
                <td class="text-right">{{ number_format((float) $invoice->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td>Total Discount</td>
                <td class="text-right">{{ number_format((float) $invoice->total_discount, 2) }}</td>
            </tr>
            <tr>
                <td>Tax</td>
                <td class="text-right">{{ number_format((float) $invoice->tax_amount, 2) }}</td>
            </tr>
            <tr>
                <td>Grand Total</td>
                <td class="text-right">{{ number_format((float) $invoice->grand_total, 2) }}</td>
            </tr>
            <tr>
                <td>Paid</td>
                <td class="text-right">{{ number_format((float) $invoice->paid_amount, 2) }}</td>
            </tr>
            <tr>
                <td>Balance Due</td>
                <td class="text-right">{{ number_format((float) $invoice->balance_due, 2) }}</td>
            </tr>
        </table>
    </div>

    @if($invoice->notes)
        <div class="section-title">Notes</div>
        <div>{{ $invoice->notes }}</div>
    @endif

    @if($invoice->terms)
        <div class="section-title">Terms / Remarks</div>
        <div>{{ $invoice->terms }}</div>
    @endif

    <div class="footer">
        {{ $shop['website'] ?? 'www.froziohub.com' }}
    </div>
</body>
</html>