<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Monthly report</title>

    <style>
        :root {
            --accent: #f97316;
            --accent-soft: #fff7ed;
            --text-main: #0b1120;
            --text-muted: #6b7280;
            --border-soft: #e5e7eb;
            --table-stripe: #f9fafb;
        }

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
            font-size: 12px;
            color: var(--text-main);
            margin: 24px;
            line-height: 1.45;
        }

        h1, h2, h3, h4 { margin: 0; font-weight: 600; }

        .report-header {
            border-bottom: 2px solid var(--accent);
            padding: 10px 12px;
            margin-bottom: 18px;
            display: table;
            width: 100%;
        }

        .header-left { display: table-cell; vertical-align: middle; width: 70%; }
        .header-right { display: table-cell; vertical-align: middle; width: 30%; text-align: right; }

        .brand-title { font-size: 14px; font-weight: 700; letter-spacing: 0.02em; text-transform: none; }
        .brand-subtitle { font-size: 10px; font-weight: 400; color: var(--text-muted); letter-spacing: 0.02em; margin-top: 2px; text-transform: none; }
        .report-title { font-size: 16px; margin-top: 6px; font-weight: 600; text-transform: none; }

        .logo { max-width: 95px; height: auto; }

        .meta-block { margin-bottom: 18px; font-size: 10px; }
        .meta-row { margin-bottom: 2px; }
        .meta-label { font-weight: 600; color: var(--text-muted); }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 26px;
        }
        .summary-table td { padding: 8px 8px; vertical-align: top; }
        .summary-table td.label {
            width: 70%;
            font-weight: 600;
            color: var(--text-muted);
            font-size: 10px;
            letter-spacing: 0.02em;
            text-transform: none;
        }
        .summary-table td.value-main {
            width: 30%;
            text-align: right;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-main);
        }
        .summary-table tr + tr td { border-top: 1px solid var(--border-soft); }

        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
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
    </style>
</head>

<body>
    <div class="report-header">
        <div class="header-left">
            <div class="brand-title">Drift Barber</div>
            <div class="brand-subtitle">Unisex • Estd 2024</div>

            <h1 class="report-title">Monthly report (Sales with Team)</h1>
        </div>
        <div class="header-right">
            <img src="{{ public_path('assets/images/Drift Barber1.png') }}" class="logo" alt="Logo">
        </div>
    </div>

    @php
        $from = $filters['from'] ?? now()->startOfMonth()->toDateString();
        $to   = $filters['to'] ?? now()->toDateString();

        $branchLabel = 'All branches';
        $bid = $filters['branch_id'] ?? '';

        if ($bid !== '' && !empty($branches)) {
            if ($branches instanceof \Illuminate\Support\Collection) {
                $branch = $branches->firstWhere('id', $bid);
                if ($branch) $branchLabel = $branch->name ?? $branchLabel;
            } else {
                foreach ($branches as $b) {
                    $id = is_array($b) ? ($b['id'] ?? null) : ($b->id ?? null);
                    if ((string)$id === (string)$bid) {
                        $branchLabel = is_array($b) ? ($b['name'] ?? $branchLabel) : ($b->name ?? $branchLabel);
                        break;
                    }
                }
            }
        }
    @endphp

    <div class="meta-block">
        <div class="meta-row">
            <span class="meta-label">Period:</span> {{ $from }} – {{ $to }}
        </div>

        <div class="meta-row">
            <span class="meta-label">Branch:</span> {{ $branchLabel }}
        </div>

        <div class="meta-row">
            <span class="meta-label">Generated at:</span> {{ now()->format('Y-m-d H:i') }}
        </div>
    </div>

    <table class="summary-table">
        <tr>
            <td class="label">Total appointments</td>
            <td class="value-main">{{ (int)($summary['totalAppointments'] ?? 0) }}</td>
        </tr>
        <tr>
            <td class="label">Total sales (services)</td>
            <td class="value-main">{{ (int)($summary['totalSales'] ?? 0) }}</td>
        </tr>
        <tr>
            <td class="label">Net total (LKR)</td>
            <td class="value-main">Rs {{ number_format((float)($summary['netTotal'] ?? 0), 2) }}</td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 44%;">Team member</th>
                <th class="text-right" style="width: 14%;">Sales</th>
                <th class="text-right" style="width: 22%;">Net total (LKR)</th>
                <th class="text-right" style="width: 20%;">Tip (LKR)</th>
            </tr>
        </thead>
        <tbody>
        @forelse($rows as $r)
            <tr>
                <td>{{ $r['team_member'] ?? '—' }}</td>
                <td class="text-right">{{ number_format((int)($r['sales'] ?? 0), 0) }}</td>
                <td class="text-right">Rs {{ number_format((float)($r['net_total'] ?? 0), 2) }}</td>
                <td class="text-right">Rs {{ number_format((float)($r['tip'] ?? 0), 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center">No sales found for this period.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
