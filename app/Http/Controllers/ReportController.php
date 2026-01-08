<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingSale;
use App\Models\BookingService;
use App\Models\Branch;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\RevenueExport;
use App\Exports\DailySalesExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Reports/index');
    }

    public function getdata()
    {
        // TODO
    }

    public function create(): Response
    {
        return Inertia::render('Reports/Create');
    }

    public function store()
    {
        // TODO
    }

    public function edit($id): Response
    {
        return Inertia::render('Reports/Edit', [
            'id' => $id,
        ]);
    }

    public function update()
    {
        // TODO
    }

    public function destroy($id)
    {
        // TODO
    }

    // =========================
    // Revenue report (UNCHANGED)
    // =========================
    public function revenue(Request $request): Response
    {
        $from     = $request->query('from');
        $to       = $request->query('to');
        $branchId = $request->query('branch_id');

        $baseQuery = Booking::query()
            ->with(['client', 'staff', 'services', 'staff.branches']);

        if ($from) {
            $baseQuery->whereDate('date', '>=', $from);
        }

        if ($to) {
            $baseQuery->whereDate('date', '<=', $to);
        }

        if ($branchId) {
            $baseQuery->whereHas('staff.branches', function ($q) use ($branchId) {
                $q->where('branches.id', $branchId);
            });
        }

        $completedQuery = (clone $baseQuery)->where('status', 'completed');

        $bookings = $completedQuery
            ->orderBy('date', 'asc')
            ->get();

        $totalRevenue    = $bookings->sum('total_price');
        $completedCount  = $bookings->count();
        $adr             = $completedCount ? $totalRevenue / $completedCount : 0;

        $scheduledTotal = (clone $baseQuery)
            ->where('status', 'scheduled')
            ->sum('total_price');

        $pendingPaymentsTotal = (clone $baseQuery)
            ->where('status', 'pending_payment')
            ->sum('total_price');

        $branches = Branch::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Reports/RevenueReport', [
            'bookings' => $bookings,
            'summary'  => [
                'totalRevenue'          => $totalRevenue,
                'adr'                   => $adr,
                'nights'                => $completedCount,
                'completedCount'        => $completedCount,
                'scheduledTotal'        => $scheduledTotal,
                'pendingPaymentsTotal'  => $pendingPaymentsTotal,
            ],
            'filters'  => [
                'from'      => $from,
                'to'        => $to,
                'branch_id' => $branchId,
            ],
            'branches' => $branches,
        ]);
    }

    public function revenuePdf(Request $request)
    {
        $from     = $request->query('from');
        $to       = $request->query('to');
        $branchId = $request->query('branch_id');

        $baseQuery = Booking::query()
            ->with(['client', 'staff', 'services', 'staff.branches']);

        if ($from) {
            $baseQuery->whereDate('date', '>=', $from);
        }

        if ($to) {
            $baseQuery->whereDate('date', '<=', $to);
        }

        if ($branchId) {
            $baseQuery->whereHas('staff.branches', function ($q) use ($branchId) {
                $q->where('branches.id', $branchId);
            });
        }

        $completedQuery = (clone $baseQuery)->where('status', 'completed');

        $bookings = $completedQuery
            ->orderBy('date', 'asc')
            ->get();

        $totalRevenue    = $bookings->sum('total_price');
        $completedCount  = $bookings->count();
        $adr             = $completedCount ? $totalRevenue / $completedCount : 0;

        $scheduledTotal = (clone $baseQuery)
            ->where('status', 'scheduled')
            ->sum('total_price');

        $pendingPaymentsTotal = (clone $baseQuery)
            ->where('status', 'pending_payment')
            ->sum('total_price');

        $branches = Branch::orderBy('name')->get(['id', 'name']);

        $summary = [
            'totalRevenue'          => $totalRevenue,
            'adr'                   => $adr,
            'nights'                => $completedCount,
            'completedCount'        => $completedCount,
            'scheduledTotal'        => $scheduledTotal,
            'pendingPaymentsTotal'  => $pendingPaymentsTotal,
        ];

        $filters = [
            'from'      => $from,
            'to'        => $to,
            'branch_id' => $branchId,
        ];

        $pdf = Pdf::loadView('reports.revenue-pdf', [
            'bookings' => $bookings,
            'summary'  => $summary,
            'filters'  => $filters,
            'branches' => $branches,
        ])->setPaper('a4', 'portrait');

        $fileName = 'revenue-report-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($fileName);
    }

    public function revenueExcel(Request $request)
    {
        $from     = $request->query('from');
        $to       = $request->query('to');
        $branchId = $request->query('branch_id');

        $fileName = 'revenue-report-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new RevenueExport($from, $to, $branchId),
            $fileName
        );
    }

    // =========================
    // Daily sale report (NEW)
    // =========================
    public function dailySales(Request $request): Response
    {
        $date = $request->query('date') ?: now()->toDateString();
        $branchId = $request->query('branch_id');

        $data = $this->buildDailySalesData($date, $branchId);

        $branches = Branch::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Reports/DailySalesReport', [
            'rows' => $data['rows'],
            'summary' => $data['summary'],
            'filters' => [
                'date' => $date,
                'branch_id' => $branchId ?: '',
            ],
            'branches' => $branches,
        ]);
    }

    public function dailySalesPdf(Request $request)
    {
        $date = $request->query('date') ?: now()->toDateString();
        $branchId = $request->query('branch_id');

        $data = $this->buildDailySalesData($date, $branchId);

        $branches = Branch::orderBy('name')->get(['id', 'name']);

        $pdf = Pdf::loadView('reports.daily-sales-pdf', [
            'rows' => $data['rows'],
            'summary' => $data['summary'],
            'filters' => [
                'date' => $date,
                'branch_id' => $branchId ?: '',
            ],
            'branches' => $branches,
        ])->setPaper('a4', 'landscape');

        $fileName = 'daily-sales-report-' . $date . '.pdf';

        return $pdf->download($fileName);
    }

    public function dailySalesExcel(Request $request)
    {
        $date = $request->query('date') ?: now()->toDateString();
        $branchId = $request->query('branch_id');

        $fileName = 'daily-sales-report-' . $date . '.xlsx';

        return Excel::download(
            new DailySalesExport($date, $branchId),
            $fileName
        );
    }

    private function buildDailySalesData(string $date, ?string $branchId): array
    {
        $services = BookingService::query()
            ->with([
                'booking:id,client_id,branch_id,date,status',
                'booking.branch:id,name',
                'booking.client:id,first_name,last_name',
                'booking.sales:id,booking_id,branch_id,payment_method,base_amount,tax_amount,tip_amount,total_with_tip,total_paid,remaining,payments_json',
                'staff:id,name',
            ])
            ->whereHas('booking', function ($q) use ($date, $branchId) {
                $q->where('status', '!=', 'blocked_time')
                    ->whereDate('date', $date);

                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
            })
            ->whereHas('booking.sales')
            ->orderBy('booking_id', 'asc')
            ->orderBy('starts_at', 'asc')
            ->get([
                'id',
                'booking_id',
                'staff_id',
                'label',
                'price',
                'final_price',
            ]);

        $grouped = $services->groupBy('booking_id');

        $rows = [];
        $rowId = 1;

        $summaryTotalServices = 0;

        $sumCash = 0.0;
        $sumCard = 0.0;
        $sumGift = 0.0;
        $sumSplit = 0.0;
        $sumNet = 0.0;

        foreach ($grouped as $bookingId => $svcs) {
            $first = $svcs->first();
            $booking = $first?->booking;

            $sale = $booking?->sales?->sortByDesc('id')->first();
            if (!$sale) continue;

            $amounts = $this->parseSaleAmounts($sale);

            // summary is booking-level (NOT duplicated by service rows)
            $sumCash += $amounts['cash'];
            $sumCard += $amounts['card'];
            $sumGift += $amounts['gift'];
            $sumSplit += $amounts['split'];
            $sumNet += $amounts['total'];

            $bookingNet = (float) $svcs->sum(function ($s) {
                return (float)($s->final_price ?? $s->price ?? 0);
            });

            $count = max(1, (int)$svcs->count());
            $summaryTotalServices += $count;

            foreach ($svcs as $svc) {
                $svcNet = (float)($svc->final_price ?? $svc->price ?? 0);
                $ratio = $bookingNet > 0 ? ($svcNet / $bookingNet) : (1 / $count);

                $client = $booking?->client;
                $clientName = trim((string)($client?->first_name ?? '') . ' ' . (string)($client?->last_name ?? ''));
                if ($clientName === '') $clientName = 'Walk-in customer';

                $rows[] = [
                    'row_id' => $rowId++,
                    'booking_id' => $booking?->id ?? $svc->booking_id,
                    'staff_name' => $svc->staff?->name ?? '—',
                    'client_name' => $clientName,
                    'service_label' => $svc->label ?? '',
                    'payment_method' => $amounts['method'],
                    'cash_amount' => $amounts['cash'] * $ratio,
                    'card_amount' => $amounts['card'] * $ratio,
                    'gift_cards' => $amounts['gift'] * $ratio,
                    'tip' => $amounts['tip'] * $ratio,
                    'location' => $booking?->branch?->name ?? '—',
                    'split_total' => $amounts['split'] * $ratio,
                    'total_amount' => $amounts['total'] * $ratio,
                ];
            }
        }

        return [
            'rows' => $rows,
            'summary' => [
                'totalServicesToday' => $summaryTotalServices,
                'totalCashAmount' => round($sumCash, 2),
                'totalCardAmount' => round($sumCard, 2),
                'totalSplitPaidAmount' => round($sumSplit, 2),
                'giftCardsTotal' => round($sumGift, 2),
                'netTotalAmount' => round($sumNet, 2),
            ],
        ];
    }

    private function parseSaleAmounts(BookingSale $sale): array
    {
        $cash = 0.0;
        $card = 0.0;
        $gift = 0.0;

        $tip = (float)($sale->tip_amount ?? 0);
        $methodRaw = strtolower((string)($sale->payment_method ?? ''));

        $payments = $sale->payments_json;
        if (is_array($payments)) {
            foreach ($payments as $p) {
                if (!is_array($p)) continue;

                $m = strtolower((string)($p['method'] ?? $p['payment_method'] ?? $p['type'] ?? $p['name'] ?? ''));
                $amt = (float)($p['amount'] ?? $p['paid_amount'] ?? $p['value'] ?? 0);

                if ($amt <= 0) continue;

                if (str_contains($m, 'cash')) $cash += $amt;
                else if (str_contains($m, 'card')) $card += $amt;
                else if (str_contains($m, 'gift')) $gift += $amt;
            }
        }

        $paid = (float)($sale->total_paid ?? 0);
        if (($cash + $card + $gift) <= 0 && $paid > 0) {
            if (str_contains($methodRaw, 'cash')) $cash = $paid;
            else if (str_contains($methodRaw, 'card')) $card = $paid;
            else if (str_contains($methodRaw, 'gift')) $gift = $paid;
        }

        $split = ($cash > 0 && $card > 0) ? ($cash + $card) : 0.0;

        $total = (float)($sale->total_with_tip ?? 0);
        if ($total <= 0) {
            $base = (float)($sale->base_amount ?? 0);
            $tax = (float)($sale->tax_amount ?? 0);
            $total = $base + $tax + $tip;
        }
        if ($total <= 0) {
            $total = $cash + $card + $gift + $tip;
        }

        $method = 'cash';
        if ($gift > 0 && ($cash + $card) <= 0) $method = 'gift_card';
        else if ($split > 0) $method = 'split';
        else if ($card > 0) $method = 'card';
        else if ($cash > 0) $method = 'cash';
        else if ($methodRaw !== '') $method = $methodRaw;
        else $method = '—';

        return [
            'method' => $method,
            'cash' => $cash,
            'card' => $card,
            'gift' => $gift,
            'tip' => $tip,
            'split' => $split,
            'total' => $total,
        ];
    }
}
