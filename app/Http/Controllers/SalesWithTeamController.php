<?php

namespace App\Http\Controllers;

use App\Exports\DailyReportExport;
use App\Exports\MonthlyReportExport;
use App\Models\Booking;
use App\Models\BookingSale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class SalesWithTeamController extends Controller
{
    private function normalizeDate(?string $date): string
    {
        // Default filter = today
        return $date ?: now()->toDateString();
    }

    private function normalizeMonthlyRange(?string $from, ?string $to): array
    {
        // Default filter = this month (start -> today)
        $defaultFrom = now()->startOfMonth()->toDateString();
        $defaultTo   = now()->toDateString();

        $f = $from ?: $defaultFrom;
        $t = $to ?: $defaultTo;

        // Swap if user passes reversed range
        if ($f > $t) {
            [$f, $t] = [$t, $f];
        }

        return [$f, $t];
    }

    private function parseTipsIntoMap(iterable $salesRows): array
    {
        // returns [staff_id => tip_sum]
        $tipByStaff = [];

        $add = function ($staffId, $amount) use (&$tipByStaff) {
            if (!$staffId) return;
            $sid = (int) $staffId;
            $amt = (float) $amount;
            if ($amt <= 0) return;
            $tipByStaff[$sid] = ($tipByStaff[$sid] ?? 0) + $amt;
        };

        foreach ($salesRows as $sale) {
            $tips = $sale->tips_json ?? null;

            // Prefer tips_json if present (to avoid double counting)
            if (is_array($tips) && !empty($tips)) {
                // Case 1: list of tip rows
                if (array_is_list($tips)) {
                    foreach ($tips as $t) {
                        if (!is_array($t)) continue;

                        $sid = $t['staff_id'] ?? $t['tip_staff_id'] ?? $t['user_id'] ?? null;
                        $amt = $t['amount'] ?? $t['tip_amount'] ?? $t['value'] ?? null;

                        if ($sid !== null && $amt !== null) $add($sid, $amt);
                    }
                } else {
                    // Case 2: associative map (e.g. {"12": 500, "15": 200})
                    foreach ($tips as $k => $v) {
                        if (is_numeric($k) && is_numeric($v)) {
                            $add($k, $v);
                            continue;
                        }

                        // Case 3: associative with nested arrays
                        if (is_array($v)) {
                            $sid = $v['staff_id'] ?? $v['tip_staff_id'] ?? $v['user_id'] ?? (is_numeric($k) ? $k : null);
                            $amt = $v['amount'] ?? $v['tip_amount'] ?? $v['value'] ?? null;
                            if ($sid !== null && $amt !== null) $add($sid, $amt);
                        }
                    }
                }

                continue;
            }

            // Fallback: single tip fields
            if (!empty($sale->tip_staff_id) && !empty($sale->tip_amount)) {
                $add($sale->tip_staff_id, $sale->tip_amount);
            }
        }

        return $tipByStaff;
    }

    private function buildDailyData(Request $request): array
    {
        $date = $this->normalizeDate($request->query('date'));
        $branchId = $request->query('branch_id') ?: '';

        $branches = DB::table('branches')
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get()
            ->map(fn ($b) => ['id' => $b->id, 'name' => $b->name])
            ->all();

        // Appointments for the day (excluding blocked_time)
        $appointmentsQuery = Booking::query()
            ->whereDate('date', $date)
            ->where('status', '!=', 'blocked_time');

        if ($branchId !== '') {
            $appointmentsQuery->where('branch_id', $branchId);
        }

        $totalAppointments = (int) $appointmentsQuery->count();

        // Service aggregates per staff (the core of this report)
        $serviceAgg = DB::table('booking_services')
            ->join('bookings', 'bookings.id', '=', 'booking_services.booking_id')
            ->whereDate('bookings.date', $date)
            ->where('bookings.status', '!=', 'blocked_time')
            ->when($branchId !== '', fn ($q) => $q->where('bookings.branch_id', $branchId))
            ->whereNotNull('booking_services.staff_id')
            ->groupBy('booking_services.staff_id')
            ->selectRaw('
                booking_services.staff_id as staff_id,
                COUNT(*) as services_count,
                SUM(COALESCE(booking_services.final_price, booking_services.price, 0)) as net_total
            ')
            ->get();

        $staffIds = $serviceAgg->pluck('staff_id')->filter()->unique()->values()->all();

        // Staff names (use DB directly to avoid heavy eager-loading in User model)
        $staffNames = [];
        if (!empty($staffIds)) {
            $staffNames = DB::table('users')
                ->whereIn('id', $staffIds)
                ->pluck('name', 'id')
                ->toArray();
        }

        // Tips for the same day/branch (from booking_sales)
        $salesRows = BookingSale::query()
            ->join('bookings', 'bookings.id', '=', 'booking_sales.booking_id')
            ->whereDate('bookings.date', $date)
            ->where('bookings.status', '!=', 'blocked_time')
            ->when($branchId !== '', fn ($q) => $q->where('bookings.branch_id', $branchId))
            ->get([
                'booking_sales.tip_staff_id',
                'booking_sales.tip_amount',
                'booking_sales.tips_json',
            ]);

        $tipByStaff = $this->parseTipsIntoMap($salesRows);

        $rows = collect($serviceAgg)->map(function ($r) use ($staffNames, $tipByStaff) {
            $sid = (int) $r->staff_id;
            $servicesCount = (int) ($r->services_count ?? 0);
            $netTotal = (float) ($r->net_total ?? 0);
            $tipTotal = (float) ($tipByStaff[$sid] ?? 0);

            return [
                'staff_id' => $sid,
                'team_member' => $staffNames[$sid] ?? ('Staff #' . $sid),
                'sales' => $servicesCount,
                'net_total' => round($netTotal, 2),
                'tip' => round($tipTotal, 2),
            ];
        })
        ->sortByDesc('net_total')
        ->values()
        ->all();

        $totalSales = (int) collect($rows)->sum('sales');
        $netTotalToday = (float) collect($rows)->sum('net_total');

        return [
            'rows' => $rows,
            'summary' => [
                'totalAppointments' => $totalAppointments,
                'totalSales' => $totalSales,
                'netTotal' => round($netTotalToday, 2),
            ],
            'filters' => [
                'date' => $date,
                'branch_id' => $branchId,
            ],
            'branches' => $branches,
        ];
    }

    public function daily(Request $request): Response
    {
        $data = $this->buildDailyData($request);
        return Inertia::render('Reports/DailyReport', $data);
    }

    public function dailyPdf(Request $request)
    {
        $data = $this->buildDailyData($request);

        $pdf = Pdf::loadView('reports.dailyreport-pdf', $data)
            ->setPaper('a4', 'portrait');

        $fileName = 'daily-report-' . ($data['filters']['date'] ?? now()->format('Y-m-d')) . '.pdf';
        return $pdf->download($fileName);
    }

    public function dailyExcel(Request $request)
    {
        $date = $this->normalizeDate($request->query('date'));
        $branchId = $request->query('branch_id') ?: '';

        $fileName = 'daily-report-' . $date . '.xlsx';
        return Excel::download(new DailyReportExport($date, $branchId), $fileName);
    }

    // ==========================
    // MONTHLY REPORT (NEW)
    // ==========================

    private function buildMonthlyData(Request $request): array
    {
        [$from, $to] = $this->normalizeMonthlyRange($request->query('from'), $request->query('to'));
        $branchId = $request->query('branch_id') ?: '';

        $branches = DB::table('branches')
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get()
            ->map(fn ($b) => ['id' => $b->id, 'name' => $b->name])
            ->all();

        // Total appointments in range (excluding blocked_time)
        $appointmentsQuery = Booking::query()
            ->whereBetween('date', [$from, $to])
            ->where('status', '!=', 'blocked_time');

        if ($branchId !== '') {
            $appointmentsQuery->where('branch_id', $branchId);
        }

        $totalAppointments = (int) $appointmentsQuery->count();

        // Service aggregates per staff within range
        $serviceAgg = DB::table('booking_services')
            ->join('bookings', 'bookings.id', '=', 'booking_services.booking_id')
            ->whereBetween('bookings.date', [$from, $to])
            ->where('bookings.status', '!=', 'blocked_time')
            ->when($branchId !== '', fn ($q) => $q->where('bookings.branch_id', $branchId))
            ->whereNotNull('booking_services.staff_id')
            ->groupBy('booking_services.staff_id')
            ->selectRaw('
                booking_services.staff_id as staff_id,
                COUNT(*) as services_count,
                SUM(COALESCE(booking_services.final_price, booking_services.price, 0)) as net_total
            ')
            ->get();

        $staffIds = $serviceAgg->pluck('staff_id')->filter()->unique()->values()->all();

        $staffNames = [];
        if (!empty($staffIds)) {
            $staffNames = DB::table('users')
                ->whereIn('id', $staffIds)
                ->pluck('name', 'id')
                ->toArray();
        }

        // Tips for same range/branch (from booking_sales)
        $salesRows = BookingSale::query()
            ->join('bookings', 'bookings.id', '=', 'booking_sales.booking_id')
            ->whereBetween('bookings.date', [$from, $to])
            ->where('bookings.status', '!=', 'blocked_time')
            ->when($branchId !== '', fn ($q) => $q->where('bookings.branch_id', $branchId))
            ->get([
                'booking_sales.tip_staff_id',
                'booking_sales.tip_amount',
                'booking_sales.tips_json',
            ]);

        $tipByStaff = $this->parseTipsIntoMap($salesRows);

        $rows = collect($serviceAgg)->map(function ($r) use ($staffNames, $tipByStaff) {
            $sid = (int) $r->staff_id;
            $servicesCount = (int) ($r->services_count ?? 0);
            $netTotal = (float) ($r->net_total ?? 0);
            $tipTotal = (float) ($tipByStaff[$sid] ?? 0);

            return [
                'staff_id' => $sid,
                'team_member' => $staffNames[$sid] ?? ('Staff #' . $sid),
                'sales' => $servicesCount,
                'net_total' => round($netTotal, 2),
                'tip' => round($tipTotal, 2),
            ];
        })
        ->sortByDesc('net_total')
        ->values()
        ->all();

        $totalSales = (int) collect($rows)->sum('sales');
        $netTotal = (float) collect($rows)->sum('net_total');

        return [
            'rows' => $rows,
            'summary' => [
                'totalAppointments' => $totalAppointments,
                'totalSales' => $totalSales,
                'netTotal' => round($netTotal, 2),
            ],
            'filters' => [
                'from' => $from,
                'to' => $to,
                'branch_id' => $branchId,
            ],
            'branches' => $branches,
        ];
    }

    public function monthly(Request $request): Response
    {
        $data = $this->buildMonthlyData($request);
        return Inertia::render('Reports/MonthlyReport', $data);
    }

    public function monthlyPdf(Request $request)
    {
        $data = $this->buildMonthlyData($request);

        $pdf = Pdf::loadView('reports.monthlyreport-pdf', $data)
            ->setPaper('a4', 'portrait');

        $from = $data['filters']['from'] ?? now()->startOfMonth()->toDateString();
        $to   = $data['filters']['to'] ?? now()->toDateString();

        $fileName = 'monthly-report-' . $from . '-to-' . $to . '.pdf';
        return $pdf->download($fileName);
    }

    public function monthlyExcel(Request $request)
    {
        [$from, $to] = $this->normalizeMonthlyRange($request->query('from'), $request->query('to'));
        $branchId = $request->query('branch_id') ?: '';

        $fileName = 'monthly-report-' . $from . '-to-' . $to . '.xlsx';
        return Excel::download(new MonthlyReportExport($from, $to, $branchId), $fileName);
    }
}
