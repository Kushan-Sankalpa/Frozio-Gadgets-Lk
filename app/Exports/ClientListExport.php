<?php

namespace App\Exports;

use App\Models\Booking;
use App\Models\Client;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ClientListExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        private ?string $from,
        private ?string $to
    ) {}

    public function collection(): Collection
    {
        $firstApptSub = Booking::query()
            ->selectRaw('MIN(bookings.date)')
            ->whereColumn('bookings.client_id', 'clients.id')
            ->where('bookings.status', '!=', 'blocked_time')
            ->when($this->from, fn ($q) => $q->whereDate('bookings.date', '>=', $this->from))
            ->when($this->to, fn ($q) => $q->whereDate('bookings.date', '<=', $this->to));

        $lastApptSub = Booking::query()
            ->selectRaw('MAX(bookings.date)')
            ->whereColumn('bookings.client_id', 'clients.id')
            ->where('bookings.status', '!=', 'blocked_time')
            ->when($this->from, fn ($q) => $q->whereDate('bookings.date', '>=', $this->from))
            ->when($this->to, fn ($q) => $q->whereDate('bookings.date', '<=', $this->to));

        $q = Client::query()
            ->with(['loyaltyTier:id,name'])
            ->select([
                'clients.id',
                'clients.first_name',
                'clients.last_name',
                'clients.phone',
                'clients.phone_code',
                'clients.email',
                'clients.created_at',
                'clients.loyalty_tier_id',
                'clients.lifetime_points',
            ])
            ->selectSub($firstApptSub, 'first_appt')
            ->selectSub($lastApptSub, 'last_appt');

        if ($this->from || $this->to) {
            $q->whereExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('bookings')
                    ->whereColumn('bookings.client_id', 'clients.id')
                    ->where('bookings.status', '!=', 'blocked_time');

                if ($this->from) $sub->whereDate('bookings.date', '>=', $this->from);
                if ($this->to) $sub->whereDate('bookings.date', '<=', $this->to);
            });
        }

        return $q->orderBy('clients.created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Client',
            'Contact no.',
            'Email',
            'Added on',
            'First appt.',
            'Last appt.',
            'Loyalty points',
            'Loyalty tier',
        ];
    }

    public function map($c): array
    {
        $first = trim((string)($c->first_name ?? ''));
        $last  = trim((string)($c->last_name ?? ''));
        $name  = trim($first . ' ' . $last);
        $name  = $name !== '' ? $name : '—';

        $phone = trim((string)($c->phone ?? ''));
        $code  = trim((string)($c->phone_code ?? ''));

        $contact = '—';
        if ($phone !== '') {
            $contact = ($code !== '' ? (str_starts_with($code, '+') ? $code : ('+' . $code)) . ' ' : '') . $phone;
        }

        return [
            $name,
            $contact,
            !empty($c->email) ? $c->email : '—',
            $c->created_at ? $c->created_at->format('Y-m-d') : '—',
            !empty($c->first_appt) ? (string)$c->first_appt : '—',
            !empty($c->last_appt) ? (string)$c->last_appt : '—',
            (int)($c->lifetime_points ?? 0),
            $c->loyaltyTier?->name ?? '—',
        ];
    }
}
