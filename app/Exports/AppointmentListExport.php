<?php

namespace App\Exports;

use App\Models\BookingService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AppointmentListExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected ?string $from,
        protected ?string $to,
        protected ?string $branchId
    ) {}

    public function collection(): Collection
    {
        $query = BookingService::query()
            ->with([
                'booking:id,branch_id,client_id,date,starts_at,total_price,status',
                'booking.branch:id,name',
             'booking.client:id,first_name,last_name,phone',

                'booking.sales:id,booking_id,total_paid,remaining,total_with_tip',
                'staff:id,name',
            ])
            ->whereHas('booking', function ($q) {
                $q->where('status', '!=', 'blocked_time');

                if ($this->from) $q->whereDate('date', '>=', $this->from);
                if ($this->to) $q->whereDate('date', '<=', $this->to);
                if ($this->branchId) $q->where('branch_id', $this->branchId);
            })
            ->orderBy('booking_id', 'asc')
            ->orderBy('starts_at', 'asc');

        $services = $query->get([
            'id',
            'booking_id',
            'staff_id',
            'label',
            'duration_minutes',
            'extra_minutes',
            'starts_at',
            'ends_at',
            'price',
            'final_price',
        ]);

        return $services->map(function ($svc) {
            $booking = $svc->booking;

            $client = $booking?->client;
            $clientName = $client?->name ?? ($client?->full_name ?? '—');
            $clientPhone = $client?->phone ?? ($client?->contact_no ?? ($client?->mobile ?? ''));

            $sale = $booking?->sales?->sortByDesc('id')->first();
            $paymentStatus = 'pending';
            if ($sale) {
                if ((float)($sale->remaining ?? 0) <= 0) $paymentStatus = 'completed';
                else if ((float)($sale->total_paid ?? 0) > 0) $paymentStatus = 'part_paid';
            }

            $duration = (int)($svc->duration_minutes ?? 0) + (int)($svc->extra_minutes ?? 0);

            return (object) [
                'booking_id' => $booking?->id ?? $svc->booking_id,
                'client_name' => $clientName,
                'client_phone' => $clientPhone,
                'staff_name' => $svc->staff?->name ?? '—',
                'payment_status' => $paymentStatus,
                'scheduled_date' => $booking?->date?->format('Y-m-d') ?? '',
                'scheduled_time' => $svc->starts_at?->format('H:i') ?? '',
                'service_label' => $svc->label ?? '',
                'duration_minutes' => $duration,
                'location' => $booking?->branch?->name ?? '—',
                'net_sales' => (float)($svc->final_price ?? $svc->price ?? 0),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Appt. ref',
            'Client',
            'Contact no.',
            'Team member',
            'Status',
            'Scheduled date',
            'Scheduled time',
            'Service label',
            'Duration (min)',
            'Location',
            'Net sales',
        ];
    }

    public function map($row): array
    {
        $status = $row->payment_status ?? 'pending';
        if ($status === 'completed') $status = 'Completed';
        else if ($status === 'part_paid') $status = 'Part paid';
        else $status = 'Pending';

        return [
            $row->booking_id,
            $row->client_name,
            $row->client_phone,
            $row->staff_name,
            $status,
            $row->scheduled_date,
            $row->scheduled_time,
            $row->service_label,
            (int) $row->duration_minutes,
            $row->location,
            round((float) $row->net_sales, 2),
        ];
    }
}
