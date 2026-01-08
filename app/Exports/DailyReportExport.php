<?php

namespace App\Exports;

use App\Models\BookingSale;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DailyReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        private string $date,
        private string $branchId = ''
    ) {}

    private function parseTipsIntoMap(iterable $salesRows): array
    {
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

            if (is_array($tips) && !empty($tips)) {
                if (array_is_list($tips)) {
                    foreach ($tips as $t) {
                        if (!is_array($t)) continue;
                        $sid = $t['staff_id'] ?? $t['tip_staff_id'] ?? $t['user_id'] ?? null;
                        $amt = $t['amount'] ?? $t['tip_amount'] ?? $t['value'] ?? null;
                        if ($sid !== null && $amt !== null) $add($sid, $amt);
                    }
                } else {
                    foreach ($tips as $k => $v) {
                        if (is_numeric($k) && is_numeric($v)) {
                            $add($k, $v);
                            continue;
                        }
                        if (is_array($v)) {
                            $sid = $v['staff_id'] ?? $v['tip_staff_id'] ?? $v['user_id'] ?? (is_numeric($k) ? $k : null);
                            $amt = $v['amount'] ?? $v['tip_amount'] ?? $v['value'] ?? null;
                            if ($sid !== null && $amt !== null) $add($sid, $amt);
                        }
                    }
                }
                continue;
            }

            if (!empty($sale->tip_staff_id) && !empty($sale->tip_amount)) {
                $add($sale->tip_staff_id, $sale->tip_amount);
            }
        }

        return $tipByStaff;
    }

    public function collection(): Collection
    {
        $serviceAgg = DB::table('booking_services')
            ->join('bookings', 'bookings.id', '=', 'booking_services.booking_id')
            ->whereDate('bookings.date', $this->date)
            ->where('bookings.status', '!=', 'blocked_time')
            ->when($this->branchId !== '', fn ($q) => $q->where('bookings.branch_id', $this->branchId))
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

        $salesRows = BookingSale::query()
            ->join('bookings', 'bookings.id', '=', 'booking_sales.booking_id')
            ->whereDate('bookings.date', $this->date)
            ->where('bookings.status', '!=', 'blocked_time')
            ->when($this->branchId !== '', fn ($q) => $q->where('bookings.branch_id', $this->branchId))
            ->get([
                'booking_sales.tip_staff_id',
                'booking_sales.tip_amount',
                'booking_sales.tips_json',
            ]);

        $tipByStaff = $this->parseTipsIntoMap($salesRows);

        return collect($serviceAgg)->map(function ($r) use ($staffNames, $tipByStaff) {
            $sid = (int) $r->staff_id;
            return (object) [
                'team_member' => $staffNames[$sid] ?? ('Staff #' . $sid),
                'sales' => (int) ($r->services_count ?? 0),
                'net_total' => round((float) ($r->net_total ?? 0), 2),
                'tip' => round((float) ($tipByStaff[$sid] ?? 0), 2),
            ];
        })->sortByDesc('net_total')->values();
    }

    public function headings(): array
    {
        return [
            'Team member',
            'Sales (services count)',
            'Net total (LKR)',
            'Tip (LKR)',
        ];
    }

    public function map($row): array
    {
        return [
            $row->team_member ?? '—',
            (int) ($row->sales ?? 0),
            (float) ($row->net_total ?? 0),
            (float) ($row->tip ?? 0),
        ];
    }
}
