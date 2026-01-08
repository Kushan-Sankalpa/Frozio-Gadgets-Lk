<?php

namespace App\Exports;

use App\Models\Booking;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AppointmentSummaryExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected ?string $from,
        protected ?string $to,
        protected ?string $branchId
    ) {}

    public function collection(): Collection
    {
        $query = Booking::query()
            ->where('status', '!=', 'blocked_time')
            ->with(['branch:id,name'])
            ->withCount('services');

        if ($this->from) $query->whereDate('date', '>=', $this->from);
        if ($this->to) $query->whereDate('date', '<=', $this->to);
        if ($this->branchId) $query->where('branch_id', $this->branchId);

        $bookings = $query->get(['id', 'branch_id', 'client_id', 'total_price']);

        return $bookings
            ->groupBy('branch_id')
            ->map(function ($items) {
                $location = $items->first()?->branch?->name ?? '—';
                $appointments = $items->count();
                $services = (int) $items->sum('services_count');
                $clients = (int) $items->pluck('client_id')->filter()->unique()->count();
                $value = (float) $items->sum('total_price');
                $avg = $appointments ? ($value / $appointments) : 0;

                return (object)[
                    'location' => $location,
                    'appointments' => $appointments,
                    'services' => $services,
                    'total_clients' => $clients,
                    'total_value' => $value,
                    'avg_value' => $avg,
                ];
            })
            ->values();
    }

    public function headings(): array
    {
        return [
            'Locations',
            'Appointments',
            'Services',
            'Total clients',
            'Total appt. value',
            'Average appt. value',
        ];
    }

    public function map($row): array
    {
        return [
            $row->location,
            (int) $row->appointments,
            (int) $row->services,
            (int) $row->total_clients,
            round((float) $row->total_value, 2),
            round((float) $row->avg_value, 2),
        ];
    }
}
