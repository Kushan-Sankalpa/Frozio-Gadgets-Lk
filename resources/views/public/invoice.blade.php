<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Invoice</title>
  <style>
    .receipt-scope, .receipt-scope *{ box-sizing:border-box; font-family:-apple-system,BlinkMacSystemFont,"SF Pro Text","Segoe UI",system-ui,sans-serif; }
    .receipt-scope{ margin:0; padding:24px; background:#f3f4f6; }
    .wrapper{ max-width:720px; margin:0 auto; }
    .card{ background:#fff; border-radius:18px; padding:24px 28px; border:1px solid #e5e7eb; }
    .brand{ font-size:14px; font-weight:700; color:#111827; margin-bottom:2px; }
    .brand-sub{ font-size:11px; color:#6b7280; margin-bottom:12px; }
    .pill{ display:inline-flex; align-items:center; border-radius:999px; padding:4px 12px; font-size:12px; font-weight:600; color:#065f46; background:#d1fae5; margin-bottom:10px; }
    h1{ margin:0; font-size:22px; color:#111827; }
    .sub{ margin-top:4px; font-size:12px; color:#6b7280; }
    .section-title{ margin:20px 0 8px; font-size:13px; font-weight:600; color:#111827; text-transform:uppercase; letter-spacing:.06em; }
    table{ width:100%; border-collapse:collapse; font-size:13px; margin-top:4px; }
    th{ padding:8px 0; text-align:left; color:#6b7280; border-bottom:1px solid #e5e7eb; font-size:12px; }
    td{ padding:6px 0; color:#111827; vertical-align:top; }
    .client-box{ margin-top:16px; padding:12px 14px; border-radius:14px; background:#f9fafb; border:1px solid #e5e7eb; font-size:13px; }
    .totals{ margin-top:16px; border-top:1px solid #e5e7eb; padding-top:10px; font-size:13px; }
    .totals-row{ display:flex; justify-content:space-between; margin-top:4px; }
    .totals-label{ color:#6b7280; }
    .totals-total{ font-weight:600; color:#111827; }
    .footer{ margin-top:18px; padding-top:10px; border-top:1px dashed #e5e7eb; font-size:11px; color:#6b7280; display:flex; justify-content:space-between; align-items:center; }
    .btn{ display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:10px 14px; border-radius:999px; border:1px solid #e5e7eb; background:#111827; color:#fff; text-decoration:none; font-weight:700; font-size:13px; }
    .btn.secondary{ background:#fff; color:#111827; }
    .actions{ display:flex; gap:10px; margin-top:14px; flex-wrap:wrap; }
  </style>
</head>
<body class="receipt-scope">
@php
  $fmt = fn($v) => number_format((float)($v ?? 0), 0, '.', ',');
  $client = $booking->client;
  $clientName = $client ? trim(($client->first_name ?? '').' '.($client->last_name ?? '')) : 'Walk-in';
  if ($client && $clientName === '') $clientName = $client->name ?? 'Walk-in';
@endphp

<div class="wrapper">
  <div class="card">
    <div class="brand">Drift Barber</div>
    <div class="brand-sub">Sale #{{ $sale->id }}</div>

    <div class="pill">✔︎ Completed sale</div>
    <h1>Invoice</h1>
    <div class="sub">
      Booking #{{ $booking->id }}
      @if($booking->created_at) · {{ $booking->created_at->format('D, d M Y') }} @endif
    </div>

    <div class="actions">
      <a class="btn" href="{{ $pdfUrl }}">Download PDF</a>
      <a class="btn secondary" href="{{ $pdfUrl }}" target="_blank" rel="noopener">Open PDF</a>
    </div>

    <div class="section-title">Client</div>
    <div class="client-box">
      <div style="font-weight:600;color:#111827;margin-bottom:2px;">{{ $clientName }}</div>
      @if($client?->email)<div>{{ $client->email }}</div>@endif
      @if($client?->phone)<div>{{ $client->phone }}</div>@endif
    </div>

    <div class="section-title">Services</div>
    <table>
      <thead>
        <tr>
          <th>Service</th>
          <th style="width:90px;">Duration</th>
          <th style="width:120px;">Staff</th>
          <th style="width:90px;text-align:right;">Total</th>
        </tr>
      </thead>
      <tbody>
      @foreach(($booking->services ?? []) as $svc)
        @php
          $mins = (int)($svc->duration_minutes ?? 0) + (int)($svc->extra_minutes ?? 0);
          $dur = $mins >= 60 ? floor($mins/60).'h'.(($mins%60)?' '.($mins%60).'min':'') : ($mins ? $mins.'min' : '');
          $lineTotal = (float)($svc->final_price ?? $svc->price ?? 0);
        @endphp
        <tr>
          <td>{{ $svc->label ?? 'Service' }}</td>
          <td>{{ $dur }}</td>
          <td>{{ $booking->staff?->name ?? '' }}</td>
          <td style="text-align:right;">{{ $currencySymbol }} {{ $fmt($lineTotal) }}</td>
        </tr>
      @endforeach
      </tbody>
    </table>

    <div class="totals">
      <div class="totals-row">
        <span class="totals-label">Total</span>
        <span class="totals-total">{{ $currencySymbol }} {{ $fmt($sale->total_with_tip ?? $booking->total_price) }}</span>
      </div>
    </div>

    <div class="footer">
      <span>Paid with {{ ucfirst($sale->payment_method ?? 'cash') }}</span>
      <span>{{ $currencySymbol }} {{ $fmt($sale->total_paid ?? 0) }}</span>
    </div>
  </div>
</div>
</body>
</html>
