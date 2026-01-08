{{-- resources/views/reports/daily-sale-report-pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily sale report</title>

    <style>
        :root {
            --accent: #f97316;
            --accent-soft: #fff7ed;
            --text-main: #0b1120;
            --text-muted: #6b7280;
            --border-soft: #e5e7eb;
            --table-stripe: #f9fafb;
        }

        /* Dompdf: local absolute path fonts work best */
        @font-face {
            font-family: "DriftFont";
            font-style: normal;
            font-weight: 400;
            src: url("{{ public_path('fonts/CustomFontRegular.ttf') }}") format("truetype");
        }
        @font-face {
            font-family: "DriftFont";
            font-style: normal;
            font-weight: 600;
            src: url("{{ public_path('fonts/CustomFontRegular.ttf') }}") format("truetype");
        }
        @font-face {
            font-family: "DriftFont";
            font-style: normal;
            font-weight: 700;
            src: url("{{ public_path('fonts/CustomFontRegular.ttf') }}") format("truetype");
        }

        html, body,
        div, span, p, a,
        table, thead, tbody, tr, th, td,
        h1, h2, h3, h4, h5, h6,
        strong, em, small {
            font-family: "DriftFont", DejaVu Sans, sans-serif !important;
        }

        * { box-sizing: border-box; }

        body {
            font-size: 10px;
            color: var(--text-main);
            margin: 18px;
            line-height: 1.45;
        }

        h1, h2, h3, h4 { margin: 0; font-weight: 600; }

        .report-header {
            border-bottom: 2px solid var(--accent);
            padding: 10px 12px;
            margin-bottom: 14px;
            display: table;
            width: 100%;
        }

        .header-left { display: table-cell; vertical-align: middle; width: 70%; }
        .header-right { display: table-cell; vertical-align: middle; width: 30%; text-align: right; }

        .brand-title {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: none;
        }

        .brand-subtitle {
            font-size: 10px;
            font-weight: 400;
            text-transform: none;
            color: var(--text-muted);
            letter-spacing: 0.02em;
            margin-top: 2px;
        }

        .report-title {
            font-size: 16px;
            margin-top: 6px;
            font-weight: 600;
            text-transform: none;
        }

        .logo { max-width: 95px; height: auto; }

        .meta-block { margin-bottom: 12px; font-size: 10px; }
        .meta-row { margin-bottom: 2px; }
        .meta-label { font-weight: 600; color: var(--text-muted); }

        /* Summary cards table (same idea as appointment summary report) */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .summary-table td { padding: 6px 8px; vertical-align: top; }
        .summary-table td.label {
            width: 25%;
            font-weight: 600;
            color: var(--text-muted);
            font-size: 10px;
            letter-spacing: 0.02em;
            text-transform: none;
        }
        .summary-table td.value-main {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-main);
        }
        .summary-table tr + tr td { border-top: 1px solid var(--border-soft); }

        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        .table th, .table td {
            border: 1px solid var(--border-soft);
            padding: 6px 6px;
            vertical-align: top;
        }

        .table th {
            background: var(--accent-soft);
            font-weight: 700;
            text-align: left;
            text-transform: none;
        }

        .table tbody tr:nth-child(odd) { background: var(--table-stripe); }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 9px;
            font-weight: 700;
            border: 1px solid var(--border-soft);
            background: #fff;
            text-transform: none;
        }

        .badge-cash { background: #dcfce7; color: #166534; }
        .badge-card { background: #e0f2fe; color: #075985; }
        .badge-split { background: #ffedd5; color: #9a3412; }
        .badge-gift { background: #ede9fe; color: #5b21b6; }
        .badge-other { background: #f4f4f5; color: #3f3f46; }
    </style>
</head>

<body>
    @php
        // Fallback summary if controller didn't pass it (prevents blank section)
        $rowsArr = is_array($rows ?? null) ? $rows : (method_exists($rows ?? null, 'toArray') ? $rows->toArray() : []);
        $sum = $summary ?? [];

        $fallbackTotalServices = count($rowsArr);

        $fallbackCash  = 0.0;
        $fallbackCard  = 0.0;
        $fallbackSplit = 0.0;
        $fallbackGift  = 0.0;
        $fallbackNet   = 0.0;

        foreach ($rowsArr as $rr) {
            $fallbackCash  += (float)($rr['cash_amount'] ?? 0);
            $fallbackCard  += (float)($rr['card_amount'] ?? 0);
            $fallbackSplit += (float)($rr['split_total'] ?? 0);
            $fallbackGift  += (float)($rr['gift_cards'] ?? 0);
            $fallbackNet   += (float)($rr['total_amount'] ?? 0);
        }

        $currency = $sum['currencySymbol'] ?? 'Rs';

        $totalServicesToday = (int)($sum['totalServicesToday'] ?? $sum['total_services_today'] ?? $sum['totalServices'] ?? $fallbackTotalServices);
        $totalCashAmount    = (float)($sum['totalCashAmount'] ?? $sum['total_cash_amount'] ?? $fallbackCash);
        $totalCardAmount    = (float)($sum['totalCardAmount'] ?? $sum['total_card_amount'] ?? $fallbackCard);
        $totalSplitAmount   = (float)($sum['totalSplitAmount'] ?? $sum['total_split_paid_amount'] ?? $fallbackSplit);
        $giftCardsTotal     = (float)($sum['giftCardsTotal'] ?? $sum['gift_cards_total'] ?? $fallbackGift);
        $netTotalAmount     = (float)($sum['netTotalAmount'] ?? $sum['net_total_amount'] ?? $fallbackNet);

        $badgeClass = function($m) {
            $m = strtolower((string)$m);
            if ($m === 'cash') return 'badge badge-cash';
            if ($m === 'card') return 'badge badge-card';
            if ($m === 'split') return 'badge badge-split';
            if ($m === 'gift_card' || $m === 'gift' || $m === 'giftcards') return 'badge badge-gift';
            return 'badge badge-other';
        };

        $methodLabel = function($m) {
            $m = strtolower((string)$m);
            if ($m === 'cash') return 'Cash';
            if ($m === 'card') return 'Card';
            if ($m === 'split') return 'Split';
            if ($m === 'gift_card' || $m === 'gift' || $m === 'giftcards') return 'Gift card';
            return $m !== '' ? $m : '—';
        };
    @endphp

    <div class="report-header">
        <div class="header-left">
            <div class="brand-title">Drift Barber</div>
            <div class="brand-subtitle">Unisex • Estd 2024</div>

            <h1 class="report-title">Daily sale report</h1>
        </div>
        <div class="header-right">
            <img
                src="{{ public_path('assets/images/Drift Barber1.png') }}"
                alt="Logo"
                class="logo"
            >
        </div>
    </div>

    <div class="meta-block">
        <div class="meta-row">
            <span class="meta-label">Day:</span>
            {{ $filters['date'] ?? '' }}
        </div>

        <div class="meta-row">
            <span class="meta-label">Branch:</span>
            @if(($filters['branch_id'] ?? null))
                @php $branch = $branches->firstWhere('id', $filters['branch_id']); @endphp
                {{ $branch?->name ?? 'Unknown' }}
            @else
                All branches
            @endif
        </div>

        <div class="meta-row">
            <span class="meta-label">Generated at:</span> {{ now()->format('Y-m-d H:i') }}
        </div>
    </div>

    {{-- ✅ Summary cards section (same style as appointment summary report) --}}
    <table class="summary-table">
        <tr>
            <td class="label">Total services today</td>
            <td class="value-main">{{ $totalServicesToday }}</td>

            <td class="label">Total cash amount</td>
            <td class="value-main">{{ $currency }} {{ number_format($totalCashAmount, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Total card amount</td>
            <td class="value-main">{{ $currency }} {{ number_format($totalCardAmount, 2) }}</td>

            <td class="label">Total split paid amount</td>
            <td class="value-main">{{ $currency }} {{ number_format($totalSplitAmount, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Gift cards total</td>
            <td class="value-main">{{ $currency }} {{ number_format($giftCardsTotal, 2) }}</td>

            <td class="label">Net total amount</td>
            <td class="value-main">{{ $currency }} {{ number_format($netTotalAmount, 2) }}</td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 6%;">Appt. id</th>
                <th style="width: 10%;">Team member</th>
                <th style="width: 12%;">Client</th>
                <th style="width: 16%;">Service label</th>
                <th style="width: 8%;">Payment method</th>
                <th class="text-right" style="width: 7%;">Cash</th>
                <th class="text-right" style="width: 7%;">Card</th>
                <th class="text-right" style="width: 7%;">Gift cards</th>
                <th class="text-right" style="width: 7%;">Tip</th>
                <th style="width: 10%;">Location</th>
                <th class="text-right" style="width: 7%;">Split total</th>
                <th class="text-right" style="width: 8%;">Total amount</th>
            </tr>
        </thead>
        <tbody>
        @forelse($rows as $r)
            <tr>
                <td>#{{ $r['booking_id'] ?? '—' }}</td>
                <td>{{ $r['staff_name'] ?? '—' }}</td>
                <td>{{ (!empty($r['client_name']) && $r['client_name'] !== '—') ? $r['client_name'] : 'Walk-in customer' }}</td>
                <td>{{ $r['service_label'] ?? '—' }}</td>
                <td><span class="{{ $badgeClass($r['payment_method'] ?? '') }}">{{ $methodLabel($r['payment_method'] ?? '') }}</span></td>
                <td class="text-right">{{ number_format((float)($r['cash_amount'] ?? 0), 2) }}</td>
                <td class="text-right">{{ number_format((float)($r['card_amount'] ?? 0), 2) }}</td>
                <td class="text-right">{{ number_format((float)($r['gift_cards'] ?? 0), 2) }}</td>
                <td class="text-right">{{ number_format((float)($r['tip'] ?? 0), 2) }}</td>
                <td>{{ $r['location'] ?? '—' }}</td>
                <td class="text-right">{{ number_format((float)($r['split_total'] ?? 0), 2) }}</td>
                <td class="text-right">{{ number_format((float)($r['total_amount'] ?? 0), 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="12" class="text-center">No sales found for the selected day.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

</body>
</html>
