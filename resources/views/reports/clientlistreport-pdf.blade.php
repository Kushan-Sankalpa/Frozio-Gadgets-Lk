<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Client list report</title>

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
            src: url("file://{{ public_path('fonts/CustomFontRegular.otf') }}") format("opentype");
        }
        @font-face {
            font-family: "DriftFont";
            font-style: normal;
            font-weight: 600;
            src: url("file://{{ public_path('fonts/CustomFontSemiBold.otf') }}") format("opentype");
        }
        @font-face {
            font-family: "DriftFont";
            font-style: normal;
            font-weight: 700;
            src: url("file://{{ public_path('fonts/CustomFontSemiBold.otf') }}") format("opentype");
        }

        html, body, * {
            font-family: "DriftFont", DejaVu Sans, sans-serif !important;
        }

        * { box-sizing: border-box; }

        body {
            font-size: 10.5px;
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

        .brand-title { font-size: 14px; font-weight: 700; letter-spacing: 0.02em; }
        .brand-subtitle { font-size: 10px; font-weight: 400; color: var(--text-muted); margin-top: 2px; }
        .report-title { font-size: 16px; margin-top: 6px; font-weight: 600; }

        .logo { max-width: 95px; height: auto; }

        .meta-block { margin-bottom: 12px; font-size: 10px; }
        .meta-row { margin-bottom: 2px; }
        .meta-label { font-weight: 600; color: var(--text-muted); }

        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
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

            <h1 class="report-title">Client list report</h1>
        </div>
        <div class="header-right">
            <img
                src="{{ public_path('assets/images/Drift Barber1.png') }}"
                alt="Drift Barber Logo"
                class="logo"
            >
        </div>
    </div>

    <div class="meta-block">
        <div class="meta-row">
            <span class="meta-label">Period:</span>
            @if(($filters['from'] ?? null) && ($filters['to'] ?? null))
                {{ $filters['from'] }} – {{ $filters['to'] }}
            @else
                All dates
            @endif
        </div>

        <div class="meta-row">
            <span class="meta-label">Generated at:</span> {{ now()->format('Y-m-d H:i') }}
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 16%;">Client</th>
                <th style="width: 12%;">Contact no.</th>
                <th style="width: 18%;">Email</th>
                <th style="width: 10%;">Added on</th>
                <th style="width: 10%;">First appt.</th>
                <th style="width: 10%;">Last appt.</th>
                <th class="text-right" style="width: 10%;">Loyalty points</th>
                <th style="width: 14%;">Loyalty tier</th>
            </tr>
        </thead>
        <tbody>
        @forelse($rows as $r)
            <tr>
                <td>{{ $r['client_name'] ?? '—' }}</td>
                <td>{{ $r['contact_no'] ?? '—' }}</td>
                <td>{{ $r['email'] ?? '—' }}</td>
                <td>{{ $r['added_on'] ?? '—' }}</td>
                <td>{{ $r['first_appt'] ?? '—' }}</td>
                <td>{{ $r['last_appt'] ?? '—' }}</td>
                <td class="text-right">{{ number_format((float)($r['loyalty_points'] ?? 0), 0) }}</td>
                <td>{{ $r['loyalty_tier'] ?? '—' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center">No clients found for this period.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
