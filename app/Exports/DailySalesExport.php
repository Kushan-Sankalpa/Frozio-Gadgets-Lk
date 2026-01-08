<?php

namespace App\Exports;

use App\Models\BookingService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DailySalesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected string $date,
        protected ?string $branchId
    ) {}

    public function collection(): Collection
    {
        $services = BookingService::query()
            ->with([
                'booking:id,client_id,branch_id,date,status',
                'booking.branch:id,name',
                'booking.client:id,first_name,last_name',
                'booking.sales:id,booking_id,branch_id,payment_method,base_amount,tax_amount,tip_amount,total_with_tip,total_paid,remaining,payments_json',
                'staff:id,name',
            ])
            ->whereHas('booking', function ($q) {
                $q->where('status', '!=', 'blocked_time')
                    ->whereDate('date', $this->date);

                if ($this->branchId) {
                    $q->where('branch_id', $this->branchId);
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

        // group by booking_id so we can allocate booking-level payments to service-level rows
        $grouped = $services->groupBy('booking_id');

        $rows = collect();
        $rowId = 1;

        foreach ($grouped as $bookingId => $svcs) {
            $first = $svcs->first();
            $booking = $first?->booking;

            $sale = $booking?->sales?->sortByDesc('id')->first();
            if (!$sale) continue;

            $amounts = $this->parseSaleAmounts($sale);

            $bookingNet = (float) $svcs->sum(function ($s) {
                return (float)($s->final_price ?? $s->price ?? 0);
            });

            $count = max(1, (int) $svcs->count());

            foreach ($svcs as $svc) {
                $svcNet = (float)($svc->final_price ?? $svc->price ?? 0);
                $ratio = $bookingNet > 0 ? ($svcNet / $bookingNet) : (1 / $count);

                $client = $booking?->client;
                $clientName = trim((string)($client?->first_name ?? '') . ' ' . (string)($client?->last_name ?? ''));
                if ($clientName === '') $clientName = 'Walk-in customer';

                $rows->push((object)[
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
                ]);
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Appt. id',
            'Team member',
            'Client',
            'Service label',
            'Payment method',
            'Cash amount',
            'Card amount',
            'Gift cards',
            'Tip',
            'Location',
            'Split total',
            'Total amount',
        ];
    }

    public function map($row): array
    {
        return [
            $row->booking_id,
            $row->staff_name,
            $row->client_name,
            $row->service_label,
            $row->payment_method,
            round((float) $row->cash_amount, 2),
            round((float) $row->card_amount, 2),
            round((float) $row->gift_cards, 2),
            round((float) $row->tip, 2),
            $row->location,
            round((float) $row->split_total, 2),
            round((float) $row->total_amount, 2),
        ];
    }

    private function parseSaleAmounts($sale): array
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

        // fallback when payments_json isn't populated
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
