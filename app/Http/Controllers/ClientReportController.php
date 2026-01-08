<?php

namespace App\Http\Controllers;

use App\Exports\ClientListExport;
use App\Models\Booking;
use App\Models\Client;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class ClientReportController extends Controller
{
    private function baseClientQuery(?string $from, ?string $to)
    {
        // ✅ First appointment (calculated WITHIN range when filters exist)
        $firstApptSub = Booking::query()
            ->selectRaw('MIN(bookings.date)')
            ->whereColumn('bookings.client_id', 'clients.id')
            ->where('bookings.status', '!=', 'blocked_time')
            ->when($from, fn ($q) => $q->whereDate('bookings.date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('bookings.date', '<=', $to));

        // ✅ Last appointment (calculated WITHIN range when filters exist)
        $lastApptSub = Booking::query()
            ->selectRaw('MAX(bookings.date)')
            ->whereColumn('bookings.client_id', 'clients.id')
            ->where('bookings.status', '!=', 'blocked_time')
            ->when($from, fn ($q) => $q->whereDate('bookings.date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('bookings.date', '<=', $to));

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

        // ✅ Filter list by appointment date range (bookings.date)
        // Client "added_on" (clients.created_at) is NOT used for filtering.
        if ($from || $to) {
            $q->whereExists(function ($sub) use ($from, $to) {
                $sub->selectRaw('1')
                    ->from('bookings')
                    ->whereColumn('bookings.client_id', 'clients.id')
                    ->where('bookings.status', '!=', 'blocked_time');

                if ($from) $sub->whereDate('bookings.date', '>=', $from);
                if ($to) $sub->whereDate('bookings.date', '<=', $to);
            });
        }

        return $q;
    }

    private function buildListData(Request $request): array
    {
        $from = $request->query('from');
        $to   = $request->query('to');

        $clients = $this->baseClientQuery($from, $to)
            ->orderBy('clients.created_at', 'desc')
            ->get();

        $rows = $clients->map(function ($c) {
            $first = trim((string)($c->first_name ?? ''));
            $last  = trim((string)($c->last_name ?? ''));
            $name  = trim($first . ' ' . $last);

            $phone = trim((string)($c->phone ?? ''));
            $code  = trim((string)($c->phone_code ?? ''));

            $contact = '—';
            if ($phone !== '') {
                $contact = ($code !== '' ? (str_starts_with($code, '+') ? $code : ('+' . $code)) . ' ' : '') . $phone;
            }

            return [
                'client_id'      => $c->id,
                'client_name'    => $name !== '' ? $name : '—',
                'contact_no'     => $contact,
                'email'          => !empty($c->email) ? $c->email : '—',
                'added_on'       => $c->created_at ? $c->created_at->format('Y-m-d') : '—',

                // ✅ Now these match the selected filter range
                'first_appt'     => !empty($c->first_appt) ? (string)$c->first_appt : '—',
                'last_appt'      => !empty($c->last_appt) ? (string)$c->last_appt : '—',

                'loyalty_points' => (int)($c->lifetime_points ?? 0),
                'loyalty_tier'   => $c->loyaltyTier?->name ?? '—',
            ];
        })->values()->all();

        return [
            'rows' => $rows,
            'filters' => [
                'from' => $from,
                'to'   => $to,
            ],
        ];
    }

    public function list(Request $request): Response
    {
        $data = $this->buildListData($request);
        return Inertia::render('Reports/ClientListReport', $data);
    }

    public function listPdf(Request $request)
    {
        $data = $this->buildListData($request);

        $pdf = Pdf::loadView('reports.clientlistreport-pdf', $data)
            ->setPaper('a4', 'landscape');

        $fileName = 'client-list-report-' . now()->format('Y-m-d') . '.pdf';
        return $pdf->download($fileName);
    }

    public function listExcel(Request $request)
    {
        $from = $request->query('from');
        $to   = $request->query('to');

        $fileName = 'client-list-report-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new ClientListExport($from, $to), $fileName);
    }
}
