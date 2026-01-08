<?php

namespace App\Exports;

use App\Models\Booking;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RevenueExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        protected ?string $from,
        protected ?string $to,
        protected ?string $branchId
    ) {}

    public function collection()
    {
        $query = Booking::query()
            ->with(['client', 'staff', 'services', 'staff.branches']);

        if ($this->from) {
            $query->whereDate('date', '>=', $this->from);
        }

        if ($this->to) {
            $query->whereDate('date', '<=', $this->to);
        }

        if ($this->branchId) {
            $query->whereHas('staff.branches', function ($q) {
                $q->where('branches.id', $this->branchId);
            });
        }

        // Only completed bookings for the Excel, same as your table
        $query->where('status', 'completed')
              ->orderBy('date', 'asc');

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Date',
            'Booking ID',
            'Client',
            'Staff',
            'Services',
            'Total',
        ];
    }

    public function map($booking): array
    {
        $clientName = trim(
            ($booking->client->first_name ?? '') . ' ' . ($booking->client->last_name ?? '')
        ) ?: '—';

        $staffName = $booking->staff->name ?? '—';

        $services = $booking->services
            ? $booking->services->pluck('label')->filter()->implode(', ')
            : '—';

        return [
            optional($booking->date)->format('Y-m-d') ?? $booking->date,
            '#' . $booking->id,
            $clientName,
            $staffName,
            $services,
            $booking->total_price,
        ];
    }
}
