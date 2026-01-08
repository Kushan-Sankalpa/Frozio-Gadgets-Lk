<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Revenue report</title>
    <style>
        :root {
            --accent: #f97316;      /* orange */
            --accent-soft: #fff7ed; /* very light orange */
            --text-main: #0b1120;   /* near-zinc-950 */
            --text-muted: #6b7280;  /* zinc-500 */
            --border-soft: #e5e7eb; /* zinc-200 */
            --table-stripe: #f9fafb;
            --header-bg: #f3f4f6;   /* light gray for header background */
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: "DejaVu Sans", Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: var(--text-main);
            margin: 24px;
            line-height: 1.45;
        }

        h1, h2, h3, h4 {
            margin: 0;
            font-weight: 600;
        }

        /* HEADER */

        .report-header {
            border-bottom: 2px solid var(--accent);
            padding: 10px 12px;
            margin-bottom: 18px;
            display: table;
            width: 100%;
            /* background-color: var(--header-bg);  */
        }

        .header-left {
            display: table-cell;
            vertical-align: middle;
            width: 70%;
        }

        .header-right {
            display: table-cell;
            vertical-align: middle;
            width: 30%;
            text-align: right;
        }

        .brand-title {
            font-size: 14px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .brand-subtitle {
            font-size: 10px;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 0.16em;
            margin-top: 2px;
        }

        .report-title {
            font-size: 16px;
            margin-top: 6px;
        }

        .report-meta {
            margin-top: 4px;
            font-size: 10px;
            color: var(--text-muted);
        }

        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 999px;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            background-color: var(--accent-soft);
            color: var(--accent);
            margin-right: 4px;
        }

        .logo {
            max-width: 95px;
            height: auto;
        }

        /* META */

        .meta-block {
            margin-bottom: 18px;
            font-size: 10px;
        }

        .meta-row {
            margin-bottom: 2px;
        }

        .meta-label {
            font-weight: 600;
            color: var(--text-muted);
        }

        /* SUMMARY TABLE */

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 26px; /* more space before bookings table */
        }

        .summary-table td {
            padding: 4px 6px;
            vertical-align: top;
        }

        .summary-table td.label {
            width: 25%;
            font-weight: 500;
            color: var(--text-muted);
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .summary-table td.value-main {
            font-size: 12px;
            font-weight: 500;   /* softer, not heavy bold */
            color: var(--text-main);
        }

        .summary-table tr + tr td {
            border-top: 1px solid var(--border-soft);
        }

        .section-title {
            margin-top: 4px;
            margin-bottom: 8px;
            font-size: 12px;
            font-weight: 600;
        }

        /* BOOKINGS TABLE */

        .bookings-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        .bookings-table th,
        .bookings-table td {
            border: 1px solid var(--border-soft);
            padding: 4px 5px;
        }

        .bookings-table th {
            background: var(--accent-soft);
            color: var(--text-main);
            font-weight: 600;
            text-align: left;
        }

        .bookings-table th.text-right,
        .bookings-table td.text-right {
            text-align: right;
        }

        .bookings-table tbody tr:nth-child(odd) {
            background: var(--table-stripe);
        }

        .text-center { text-align: center; }
        .text-right  { text-align: right; }

        .footer-note {
            margin-top: 20px;
            font-size: 9px;
            color: var(--text-muted);
            border-top: 1px dashed var(--border-soft);
            padding-top: 6px;
        }
    </style>
</head>
<body>
    <div class="report-header">
        <div class="header-left">
            <div class="brand-title">DRIFT BARBER</div>
            <div class="brand-subtitle">UNISEX • ESTD 2024</div>

            <h1 class="report-title">Revenue report</h1>

            {{-- <div class="report-meta">
                <span class="badge">Finance</span>
                <span class="badge">Summary</span>
            </div> --}}
        </div>
        <div class="header-right">
            <img src="{{ public_path('assets/images/Drift Barber1.png') }}"
                 alt="Drift Barber Logo"
                 class="logo">
        </div>
    </div>

    <div class="meta-block">
        <div class="meta-row">
            <span class="meta-label">Period:</span>
            @if($filters['from'] && $filters['to'])
                {{ $filters['from'] }} – {{ $filters['to'] }}
            @else
                All dates
            @endif
        </div>

        <div class="meta-row">
            <span class="meta-label">Branch:</span>
            @if($filters['branch_id'])
                @php
                    $branch = $branches->firstWhere('id', $filters['branch_id']);
                @endphp
                {{ $branch?->name ?? 'Unknown' }}
            @else
                All branches
            @endif
        </div>

        <div class="meta-row">
            <span class="meta-label">Generated at:</span> {{ now()->format('Y-m-d H:i') }}
        </div>
    </div>

    {{-- SUMMARY (6 items, clean table) --}}
    <table class="summary-table">
        <tr>
            <td class="label">Total revenue</td>
            <td class="value-main">
                {{ number_format($summary['totalRevenue'], 2) }}
            </td>
            <td class="label">Average revenue / completed</td>
            <td class="value-main">
                {{ number_format($summary['adr'], 2) }}
            </td>
        </tr>
        <tr>
            <td class="label">Completed bookings</td>
            <td class="value-main">
                {{ $summary['completedCount'] }}
            </td>
            <td class="label">Report nights</td>
            <td class="value-main">
                {{ $summary['nights'] }}
            </td>
        </tr>
        <tr>
            <td class="label">Scheduled payments</td>
            <td class="value-main">
                {{ number_format($summary['scheduledTotal'], 2) }}
            </td>
            <td class="label">Pending payments</td>
            <td class="value-main">
                {{ number_format($summary['pendingPaymentsTotal'], 2) }}
            </td>
        </tr>
    </table>

    {{-- COMPLETED BOOKINGS TABLE --}}
    <h3 class="section-title">Completed bookings</h3>

    <table class="bookings-table">
        <thead>
            <tr>
                <th style="width: 12%;">Date</th>
                <th style="width: 12%;">Booking ID</th>
                <th style="width: 24%;">Client</th>
                <th style="width: 18%;">Staff</th>
                <th style="width: 24%;">Service label</th>
                <th class="text-right" style="width: 10%;">Total</th>
            </tr>
        </thead>
        <tbody>
        @forelse($bookings as $b)
            <tr>
                <td>
                    {{ \Illuminate\Support\Carbon::parse($b->date)->format('Y-m-d') }}
                </td>
                <td>#{{ $b->id }}</td>
                <td>
                    @php
                        $first = $b->client->first_name ?? '';
                        $last  = $b->client->last_name ?? '';
                        $name  = trim($first.' '.$last);
                    @endphp
                    {{ $name ?: '—' }}
                </td>
                <td>{{ $b->staff->name ?? '—' }}</td>
                <td>
                    @if($b->services && $b->services->count())
                        {{ $b->services->pluck('label')->filter()->join(', ') }}
                    @else
                        —
                    @endif
                </td>
                <td class="text-right">
                    {{ number_format($b->total_price, 2) }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center">
                    No completed bookings found for this period.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{-- <div class="footer-note">
        This report is generated by the Drift Barber system for internal accounting and performance
        review. Values are based on current booking data and may change if bookings are edited later.
    </div> --}}
</body>
</html>
