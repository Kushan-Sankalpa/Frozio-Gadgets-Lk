<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Appointment list report</title>

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
            text-transform: none;
            border: 1px solid var(--border-soft);
            background: #fff;
        }

        .badge-pending { background: #f4f4f5; color: #3f3f46; }
        .badge-part { background: #ffedd5; color: #9a3412; }
        .badge-complete { background: #dcfce7; color: #166534; }
    </style>
</head>

<body>

    <div class="report-header">
        <div class="header-left">
            <div class="brand-title">Drift Barber</div>
            <div class="brand-subtitle">Unisex • Estd 2024</div>

            <h1 class="report-title">Appointment list report</h1>
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

    @php
        $statusLabel = function($s) {
            $s = strtolower((string)$s);
            if ($s === 'completed') return 'Completed';
            if ($s === 'part_paid') return 'Part paid';
            return 'Pending';
        };
        $statusClass = function($s) {
            $s = strtolower((string)$s);
            if ($s === 'completed') return 'badge badge-complete';
            if ($s === 'part_paid') return 'badge badge-part';
            return 'badge badge-pending';
        };
    @endphp

    <table class="table">
        <thead>
            <tr>
                <th style="width: 7%;">Appt. ref</th>
                <th style="width: 16%;">Client</th>
                <th style="width: 12%;">Contact no.</th>
                <th style="width: 14%;">Team member</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 10%;">Scheduled date</th>
                <th style="width: 9%;">Scheduled time</th>
                <th style="width: 14%;">Service label</th>
                <th class="text-right" style="width: 8%;">Duration</th>
                <th style="width: 12%;">Location</th>
                <th class="text-right" style="width: 12%;">Net sales</th>
            </tr>
        </thead>
        <tbody>
        @forelse($rows as $r)
            <tr>
                <td>#{{ $r['booking_id'] ?? '—' }}</td>
               <td>
    {{ (!empty($r['client_name']) && $r['client_name'] !== '—') ? $r['client_name'] : 'Walk-in customer' }}
</td>

                <td>{{ $r['client_phone'] ?? '—' }}</td>
                <td>{{ $r['staff_name'] ?? '—' }}</td>
                <td><span class="{{ $statusClass($r['payment_status'] ?? '') }}">{{ $statusLabel($r['payment_status'] ?? '') }}</span></td>
                <td>{{ $r['scheduled_date'] ?? '—' }}</td>
                <td>{{ $r['scheduled_time'] ?? '—' }}</td>
                <td>{{ $r['service_label'] ?? '—' }}</td>
                <td class="text-right">{{ (int)($r['duration_minutes'] ?? 0) }} min</td>
                <td>{{ $r['location'] ?? '—' }}</td>
                <td class="text-right">{{ number_format((float)($r['net_sales'] ?? 0), 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="11" class="text-center">No appointment services found for this period.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

</body>
</html>
