<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Appointment summary report</title>

    <style>
        :root {
            --accent: #f97316;
            --accent-soft: #fff7ed;
            --text-main: #0b1120;
            --text-muted: #6b7280;
            --border-soft: #e5e7eb;
            --table-stripe: #f9fafb;
        }

        /* ✅ IMPORTANT: Dompdf works best when font is loaded as a local absolute path */
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

        /* ✅ Force SAME font everywhere (including h1/h2 which sometimes fallback) */
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

        h1, h2, h3, h4 {
            margin: 0;
            font-weight: 600;
        }

        .report-header {
            border-bottom: 2px solid var(--accent);
            padding: 10px 12px;
            margin-bottom: 18px;
            display: table;
            width: 100%;
        }

        .header-left { display: table-cell; vertical-align: middle; width: 70%; }
        .header-right { display: table-cell; vertical-align: middle; width: 30%; text-align: right; }

        /* ✅ NOT uppercase */
        .brand-title {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: none;
        }

        .brand-subtitle {
            font-size: 10px;
            font-weight: 400;
            color: var(--text-muted);
            letter-spacing: 0.02em;
            margin-top: 2px;
            text-transform: none;
        }

        .report-title {
            font-size: 16px;
            margin-top: 6px;
            font-weight: 600;
            text-transform: none;
        }

        .logo { max-width: 95px; height: auto; }

        .meta-block { margin-bottom: 18px; font-size: 10px; }
        .meta-row { margin-bottom: 2px; }
        .meta-label { font-weight: 600; color: var(--text-muted); }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 26px;
        }

        .summary-table td { padding: 6px 8px; vertical-align: top; }
        .summary-table td.label {
            width: 25%;
            font-weight: 600;
            color: var(--text-muted);
            font-size: 10px;
            letter-spacing: 0.02em;
            text-transform: none; /* ✅ */
        }
        .summary-table td.value-main {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-main);
        }
        .summary-table tr + tr td { border-top: 1px solid var(--border-soft); }

        .section-title {
            margin-top: 4px;
            margin-bottom: 8px;
            font-size: 12px;
            font-weight: 700;
            text-transform: none; /* ✅ */
        }

        .bookings-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        .bookings-table th, .bookings-table td {
            border: 1px solid var(--border-soft);
            padding: 6px 6px;
        }

        .bookings-table th {
            background: var(--accent-soft);
            color: var(--text-main);
            font-weight: 700;
            text-align: left;
            text-transform: none; /* ✅ */
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .bookings-table tbody tr:nth-child(odd) { background: var(--table-stripe); }
    </style>
</head>

<body>

    <div class="report-header">
        <div class="header-left">
            <div class="brand-title">Drift Barber</div>
            <div class="brand-subtitle">Unisex • Estd 2024</div>

            <h1 class="report-title">Appointment summary report</h1>
        </div>
        <div class="header-right">
            <img src="{{ public_path('assets/images/Drift Barber1.png') }}" class="logo" alt="Logo">
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

    <table class="summary-table">
        <tr>
            <td class="label">Total appointments</td>
            <td class="value-main">{{ (int)($summary['totalAppointments'] ?? 0) }}</td>
            <td class="label">Total services</td>
            <td class="value-main">{{ (int)($summary['totalServices'] ?? 0) }}</td>
        </tr>
        <tr>
            <td class="label">Total clients</td>
            <td class="value-main">{{ (int)($summary['totalClients'] ?? 0) }}</td>
            <td class="label">Completed appointments</td>
            <td class="value-main">{{ (int)($summary['completedAppointments'] ?? 0) }}</td>
        </tr>
        <tr>
            <td class="label">Total appointment value</td>
            <td class="value-main">{{ number_format((float)($summary['totalValue'] ?? 0), 2) }}</td>
            <td class="label">Average appointment value</td>
            <td class="value-main">{{ number_format((float)($summary['avgValue'] ?? 0), 2) }}%</td>
        </tr>
    </table>

    <h3 class="section-title">Appointment summary by branch</h3>

    <table class="bookings-table">
        <thead>
            <tr>
                <th style="width: 26%;">Locations</th>
                <th class="text-right" style="width: 12%;">Appointments</th>
                <th class="text-right" style="width: 12%;">Services</th>
                <th class="text-right" style="width: 12%;">Total clients</th>
                <th class="text-right" style="width: 19%;">Total appt. value</th>
                <th class="text-right" style="width: 19%;">Average appt. value</th>
            </tr>
        </thead>
        <tbody>
        @forelse($rows as $r)
            <tr>
                <td>{{ $r['location'] ?? '—' }}</td>
                <td class="text-right">{{ (int)($r['appointments'] ?? 0) }}</td>
                <td class="text-right">{{ (int)($r['services'] ?? 0) }}</td>
                <td class="text-right">{{ (int)($r['total_clients'] ?? 0) }}</td>
                <td class="text-right">{{ number_format((float)($r['total_appointment_value'] ?? 0), 2) }}</td>
                <td class="text-right">{{ number_format((float)($r['avg_appointment_value'] ?? 0), 2) }}%</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center">No appointments found for this period.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

</body>
</html>
