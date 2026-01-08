{{-- resources/views/pdf/invoice.blade.php --}}
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <style>
    /* DOMPDF-safe styling (avoid flex/grid; use tables + block layout) */
    @page { margin: 18mm 18mm 18mm 18mm; }

    body {
      font-family: "Roobert TRIAL", DejaVu Sans, sans-serif;
      font-size: 12px;
      color: #111827;
      margin: 0;
      padding: 0;
      background: #ffffff;
    }

    /* Font embedding for DOMPDF:
       Place files:
       public/fonts/RoobertTRIAL-Regular.ttf
       public/fonts/RoobertTRIAL-Bold.ttf
    */
    @if(!empty($roobertRegularFileUrl))
    @font-face {
      font-family: "Roobert TRIAL";
      font-style: normal;
      font-weight: 400;
      src: url("{{ $roobertRegularFileUrl }}") format("truetype");
    }
    @endif

    @if(!empty($roobertBoldFileUrl))
    @font-face {
      font-family: "Roobert TRIAL";
      font-style: normal;
      font-weight: 700;
      src: url("{{ $roobertBoldFileUrl }}") format("truetype");
    }
    @endif

    /* Layout helpers */
    table { width: 100%; border-collapse: collapse; }
    .right { text-align: right; }
    .center { text-align: center; }
    .nowrap { white-space: nowrap; }

    /* Header */
    .header td { vertical-align: top; }
    .title {
      font-size: 34px;
      font-weight: 700;
      margin: 0 0 6px 0;
      letter-spacing: -0.02em;
    }

    /* Meta (Invoice number + Booking id) - align like screenshot */
    .meta-table { width: auto; border-collapse: collapse; }
    .meta-table td { padding: 2px 0; }
    .meta-label {
      width: 115px;
      color: #9ca3af;
      font-size: 12px;
    }
    .meta-value {
      color: #111827;
      font-weight: 700;
      font-size: 12px;
      padding-left: 18px;
      white-space: nowrap;
    }

    .logo-wrap { text-align: right; }
    .logo {
      width: 84px;
      height: 84px;
      object-fit: contain;
    }

    /* Cards */
    .card {
      border: 1px solid #e5e7eb;
      border-radius: 12px;
      background: #fff;
    }
    .card-pad { padding: 14px 16px; }

    .two-col td { vertical-align: top; padding: 14px 16px; }
    .two-col .sep { width: 1px; padding: 0; background: #e5e7eb; }

    .small-label {
      font-size: 11px;
      color: #9ca3af;
      margin: 0 0 6px 0;
    }
    .strong { font-weight: 700; color: #111827; }
    .muted { color: #6b7280; }

    /* Services table box */
    .box {
      border: 1px solid #e5e7eb;
      border-radius: 12px;
      overflow: hidden;
      background: #fff;
    }
    .svc thead th {
      background: #f3f4f6;
      color: #6b7280;
      font-size: 11px;
      font-weight: 700;
      padding: 10px 12px;
      text-align: left;
      border-bottom: 1px solid #e5e7eb;
    }
    .svc thead th.center { text-align: center; }
    .svc thead th.right { text-align: right; }

    .svc tbody td {
      padding: 12px 12px;
      border-bottom: 1px solid #f3f4f6;
      vertical-align: top;
    }
    .svc tbody tr:last-child td { border-bottom: none; }

    /* Column alignment to match screenshot */
    .col-service { width: 55%; }
    .col-staff { width: 25%; }
    .col-total { width: 20%; }

    /* Totals (right card) */
    .totals-wrap { width: 100%; margin-top: 16px; }
    .totals-table td { padding: 7px 0; }
    .totals-table .label { color: #6b7280; }
    .totals-table .val { text-align: right; white-space: nowrap; }
    .totals-divider { border-top: 1px solid #e5e7eb; margin: 12px 0; }
    .grand { font-weight: 700; font-size: 13px; }

    /* Notes */
    .notes-title {
      font-size: 13px;
      font-weight: 700;
      margin: 0 0 10px 0;
    }
    .notes-rule { border-top: 1px solid #e5e7eb; margin: 6px 0 12px 0; }

    /* Footer bar (like screenshot) */
    .footer {
      position: fixed;
      left: -18mm;
      right: -18mm;
      bottom: -18mm;
      height: 20mm;
      background: #c45b3a;
      color: #ffffff;
      font-size: 13px;
      line-height: 14mm;
    }
    .footer table { width: 100%; height: 14mm; border-collapse: collapse; }
    .footer td { padding: 0 18mm; vertical-align: middle; line-height: 14mm; }
    .footer .right { text-align: right; }
  </style>
</head>

<body>
@php
  /**
   * LOGO:
   * Convert the remote PNG to an embedded data-uri (most reliable in DOMPDF).
   */
  $logoUrl = 'https://saptify.driftbarber.com/assets/images/Asset%202%201.png';
  $logoSrc = $logoUrl;

  try {
    $bin = @file_get_contents($logoUrl);
    if ($bin) {
      $mime = 'image/png';
      $info = @getimagesizefromstring($bin);
      if ($info && !empty($info['mime'])) $mime = $info['mime'];
      $logoSrc = 'data:' . $mime . ';base64,' . base64_encode($bin);
    }
  } catch (\Throwable $e) {}

  /**
   * FONT:
   * Place TTF files in public/fonts
   */
  $regPath  = public_path('fonts/RoobertTRIAL-Regular.ttf');
  $boldPath = public_path('fonts/RoobertTRIAL-Bold.ttf');

  $roobertRegularFileUrl = file_exists($regPath)
    ? 'file://' . str_replace('\\', '/', $regPath)
    : null;

  $roobertBoldFileUrl = file_exists($boldPath)
    ? 'file://' . str_replace('\\', '/', $boldPath)
    : null;

  $currency = $currencySymbol ?? ($sale->currency_symbol ?? $sale->currency ?? 'LKR');

  $fmt2 = function ($v) {
    $n = (float)($v ?? 0);
    return number_format($n, 2, '.', ',');
  };

  // Invoice + booking identifiers
  $invoiceNumber = $sale->invoice_number
    ?? $sale->invoice_no
    ?? ('IN' . str_pad((string)($sale->id ?? 1), 3, '0', STR_PAD_LEFT));

  $bookingId = $booking->id ?? ($sale->booking_id ?? '');

  // Client block
  $client = $booking->client ?? null;

  $clientName = 'Walk-in';
  if ($client) {
    $first = trim((string)($client->first_name ?? ''));
    $last  = trim((string)($client->last_name ?? ''));
    $clientName = trim($first.' '.$last);
    if ($clientName === '') $clientName = (string)($client->name ?? 'Walk-in');
  }

  $clientEmail = $client->email ?? null;
  $clientPhone = $client->phone ?? null;

  // Issued by + created
  $issuedBy = $sale->issued_by_name
    ?? optional($sale->user)->name
    ?? optional($booking->staff)->name
    ?? optional($booking->user)->name
    ?? '';

  $createdAt = $sale->created_at ?? $booking->created_at ?? null;
  $createdDate = $createdAt ? \Carbon\Carbon::parse($createdAt)->format('M d, Y') : '';
  $createdTime = $createdAt ? \Carbon\Carbon::parse($createdAt)->format('h:i A') : '';

  // Services
  $services = collect($booking->services ?? []);
  $fallbackStaff = $issuedBy;

  // Totals
  $computedSubtotal = (float)$services->sum(function ($s) {
    return (float)($s->final_price ?? $s->price ?? 0);
  });

  $subtotal = (float)($sale->subtotal ?? $sale->base_amount ?? $computedSubtotal);
  $discount = (float)($sale->discount_amount ?? $sale->discount ?? 0);
  $tax      = (float)($sale->tax_amount ?? $sale->tax ?? 0);

  $total = (float)($sale->total_amount ?? $sale->total ?? 0);
  if ($total <= 0) $total = max(0, ($subtotal - $discount + $tax));

  // Notes (only show section if real text)
  $notes = trim((string)($sale->note ?? $sale->notes ?? $booking->note ?? $booking->notes ?? ''));

  /**
   * FOOTER: Selected branch address/phone
   * Tries common relations/fields; adjust these 2 lines if your model field names differ.
   */
  $branch =
      $booking->branch
      ?? $booking->salonBranch
      ?? $sale->branch
      ?? $sale->salonBranch
      ?? null;

  $branchAddress =
      optional($branch)->address
      ?? optional($branch)->full_address
      ?? optional($branch)->location
      ?? ($booking->branch_address ?? $sale->branch_address ?? null);

  $branchPhone =
      optional($branch)->contact_number
      ?? optional($branch)->contact_phone
      ?? ($booking->branch_phone ?? $sale->branch_phone ?? null);

  // Fallback (only if branch not found)
  if (trim((string)$branchAddress) === '') $branchAddress = '167, Sri Jayawardenepura Kotte 10100';
  if (trim((string)$branchPhone) === '')   $branchPhone   = '+94 74 131 7373';
@endphp

  {{-- HEADER --}}
  <table class="header">
    <tr>
      <td style="width: 72%;">
        <div class="title">Invoice</div>

        <table class="meta-table">
          <tr>
            <td class="meta-label">Invoice Number</td>
            <td class="meta-value">{{ $invoiceNumber }}</td>
          </tr>
          <tr>
            <td class="meta-label">Booking Id</td>
            <td class="meta-value">{{ $bookingId }}</td>
          </tr>
        </table>
      </td>

      <td class="logo-wrap" style="width: 28%;">
        <img class="logo" src="{{ $logoSrc }}" alt="Logo">
      </td>
    </tr>
  </table>

  <div style="height: 18px;"></div>

  {{-- BILLED TO / ISSUED BY --}}
  <div class="card">
    <table class="two-col">
      <tr>
        <td style="width: 58%;">
          <div class="small-label">Billed To</div>
          <div class="strong" style="margin-bottom: 6px;">{{ $clientName }}</div>
          @if($clientEmail)
            <div style="margin-bottom: 3px;">{{ $clientEmail }}</div>
          @endif
          @if($clientPhone)
            <div>{{ $clientPhone }}</div>
          @endif
        </td>

        <td class="sep"></td>

        <td style="width: 42%;">
          <table style="width: 100%; border-collapse: collapse;">
            <tr>
              <td class="small-label" style="padding:0; width: 80px;">Issued By</td>
              <td style="padding:0;" class="strong">{{ $issuedBy }}</td>
            </tr>
            <tr><td style="height: 12px;"></td><td></td></tr>
            <tr>
              <td class="small-label" style="padding:0;">Created</td>
              <td style="padding:0;">
                <div class="strong">{{ $createdDate }}</div>
                <div class="strong">{{ $createdTime }}</div>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </div>

  <div style="height: 16px;"></div>

  {{-- SERVICES TABLE (aligned like screenshot) --}}
  <div class="box">
    <table class="svc">
      <thead>
        <tr>
          <th class="col-service">Service</th>
          <th class="col-staff center nowrap">Staff</th>
          <th class="col-total right nowrap">Total ({{ $currency }})</th>
        </tr>
      </thead>
      <tbody>
        @forelse($services as $svc)
          @php
            $serviceLabel = $svc->label ?? $svc->name ?? 'Service';
            $rowStaff = optional($svc->staff)->name ?? $svc->staff_name ?? $fallbackStaff;
            $lineTotal = (float)($svc->final_price ?? $svc->price ?? 0);
          @endphp
          <tr>
            <td>{{ $serviceLabel }}</td>
            <td class="center nowrap">{{ $rowStaff }}</td>
            <td class="right nowrap">{{ $fmt2($lineTotal) }}</td>
          </tr>
        @empty
          <tr>
            <td class="muted" colspan="3">—</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- TOTALS (RIGHT) --}}
  <div class="totals-wrap">
    <table>
      <tr>
        <td style="width: 55%;"></td>
        <td style="width: 45%;">
          <div class="card card-pad">
            <table class="totals-table">
              <tr>
                <td class="label">Subtotal</td>
                <td class="val">{{ $fmt2($subtotal) }}</td>
              </tr>
              <tr>
                <td class="label">Discount</td>
                <td class="val">{{ $fmt2($discount) }}</td>
              </tr>
              <tr>
                <td class="label">Tax</td>
                <td class="val">{{ $fmt2($tax) }}</td>
              </tr>
            </table>

            <div class="totals-divider"></div>

            <table>
              <tr>
                <td class="grand">Total</td>
                <td class="right nowrap grand">{{ $currency }} {{ $fmt2($total) }}</td>
              </tr>
            </table>
          </div>
        </td>
      </tr>
    </table>
  </div>

  {{-- NOTES (only if provided) --}}
  @if($notes !== '')
    <div style="height: 18px;"></div>
    <div class="card card-pad">
      <div class="notes-title">Notes</div>
      <div class="notes-rule"></div>
      <div>{{ $notes }}</div>
    </div>
  @endif

  {{-- FOOTER BAR (branch address + phone) --}}
  <div class="footer">
    <table>
      <tr>
        <td class="nowrap">{{ $branchAddress }}</td>
        <td class="right nowrap">{{ $branchPhone }}</td>
      </tr>
    </table>
  </div>

</body>
</html>
