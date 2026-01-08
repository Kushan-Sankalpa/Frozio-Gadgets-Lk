<?php

namespace App\Http\Controllers;

use App\Exports\AppointmentListExport;
use App\Exports\AppointmentSummaryExport;
use App\Models\Booking;
use App\Models\BookingService;
use App\Models\Branch;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class AppointmentReportsController extends Controller
{
    private function bookingBaseQuery(?string $from, ?string $to, ?string $branchId)
    {
        $query = Booking::query()
            ->where('status', '!=', 'blocked_time')
            ->with(['branch:id,name'])
            ->withCount('services');

        if ($from) $query->whereDate('date', '>=', $from);
        if ($to) $query->whereDate('date', '<=', $to);
        if ($branchId) $query->where('branch_id', $branchId);

        return $query;
    }

    private function buildSummaryData(Request $request): array
    {
        $from     = $request->query('from');
        $to       = $request->query('to');
        $branchId = $request->query('branch_id');

        $bookings = $this->bookingBaseQuery($from, $to, $branchId)
            ->orderBy('date', 'asc')
            ->get(['id', 'branch_id', 'client_id', 'total_price', 'status', 'date']);

        $totalAppointments = $bookings->count();
        $totalServices     = (int) $bookings->sum('services_count');
        $totalClients      = (int) $bookings->pluck('client_id')->filter()->unique()->count();
        $totalValue        = (float) $bookings->sum('total_price');

        $completedAppointments = (int) $bookings->where('status', 'completed')->count();
        $avgValue = $totalAppointments ? (($completedAppointments / $totalAppointments) * 100) : 0;

        $rows = $bookings
            ->groupBy('branch_id')
            ->map(function ($items) {
                $branchName = $items->first()?->branch?->name ?? '—';
                $appointments = $items->count();
                $services = (int) $items->sum('services_count');
                $clients = (int) $items->pluck('client_id')->filter()->unique()->count();
                $value = (float) $items->sum('total_price');

                $completed = (int) $items->where('status', 'completed')->count();
                $avg = $appointments ? (($completed / $appointments) * 100) : 0;

                return [
                    'branch_id'               => $items->first()->branch_id,
                    'location'                => $branchName,
                    'appointments'            => $appointments,
                    'services'                => $services,
                    'total_clients'           => $clients,
                    'total_appointment_value' => $value,
                    'avg_appointment_value'   => $avg,
                ];
            })
            ->values()
            ->all();

        $branches = Branch::orderBy('name')->get(['id', 'name']);

        return [
            'rows' => $rows,
            'summary' => [
                'totalAppointments'     => $totalAppointments,
                'totalServices'         => $totalServices,
                'totalClients'          => $totalClients,
                'totalValue'            => $totalValue,
                'avgValue'              => $avgValue,
                'completedAppointments' => $completedAppointments,
            ],
            'filters' => [
                'from'      => $from,
                'to'        => $to,
                'branch_id' => $branchId,
            ],
            'branches' => $branches,
        ];
    }

    private function buildListData(Request $request): array
    {
        $from     = $request->query('from');
        $to       = $request->query('to');
        $branchId = $request->query('branch_id');

        // Bookings (for summary cards)
        $bookings = $this->bookingBaseQuery($from, $to, $branchId)
            ->with([
                'client:id,first_name,last_name,phone',

                'sales:id,booking_id,total_paid,remaining,total_with_tip',
            ])
            ->orderBy('date', 'asc')
            ->get(['id', 'branch_id', 'client_id', 'total_price', 'status', 'date', 'starts_at']);

        $totalAppointments = $bookings->count();
        $totalServices     = (int) $bookings->sum('services_count');
        $totalClients      = (int) $bookings->pluck('client_id')->filter()->unique()->count();
        $totalValue        = (float) $bookings->sum('total_price');

        $completedAppointments = (int) $bookings->where('status', 'completed')->count();
        $avgValue = $totalAppointments ? (($completedAppointments / $totalAppointments) * 100) : 0;

        $bookingIds = $bookings->pluck('id')->values()->all();

        // Services list (one row per booking_services record)
        $services = BookingService::query()
            ->with([
                'booking:id,branch_id,client_id,date,starts_at,status,total_price',
                'booking.branch:id,name',
               'booking.client:id,first_name,last_name,phone',

                'booking.sales:id,booking_id,total_paid,remaining,total_with_tip',
                'staff:id,name',
            ])
            ->whereIn('booking_id', $bookingIds)
            ->orderBy('booking_id', 'asc')
            ->orderBy('starts_at', 'asc')
            ->get([
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

        $rows = $services->map(function ($svc) {
            $booking = $svc->booking;

           $client = $booking?->client;

$first = trim((string) ($client?->first_name ?? ''));
$last  = trim((string) ($client?->last_name ?? ''));

$isWalkIn = !$client || ($first === '' && $last === '');

$clientName = $isWalkIn
    ? 'Walk-in customer'
    : trim($first . ' ' . $last);

$clientPhone = (!$client || empty($client?->phone))
    ? '—'
    : $client->phone;



            $sale = $booking?->sales?->sortByDesc('id')->first();
            $paymentStatus = 'pending';
            if ($sale) {
                if ((float)($sale->remaining ?? 0) <= 0) $paymentStatus = 'completed';
                else if ((float)($sale->total_paid ?? 0) > 0) $paymentStatus = 'part_paid';
            }

            $duration = (int)($svc->duration_minutes ?? 0) + (int)($svc->extra_minutes ?? 0);

            return [
                'row_id' => $svc->id,
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
        })->values()->all();

        $branches = Branch::orderBy('name')->get(['id', 'name']);

        return [
            'rows' => $rows,
            'summary' => [
                'totalAppointments'     => $totalAppointments,
                'totalServices'         => $totalServices,
                'totalClients'          => $totalClients,
                'totalValue'            => $totalValue,
                'avgValue'              => $avgValue,
                'completedAppointments' => $completedAppointments,
            ],
            'filters' => [
                'from'      => $from,
                'to'        => $to,
                'branch_id' => $branchId,
            ],
            'branches' => $branches,
        ];
    }

    public function summary(Request $request): Response
    {
        $data = $this->buildSummaryData($request);
        return Inertia::render('Reports/AppointmentSummaryReport', $data);
    }

    public function summaryPdf(Request $request)
    {
        $data = $this->buildSummaryData($request);

        $pdf = Pdf::loadView('reports.appointmentsummary-pdf', $data)
            ->setPaper('a4', 'portrait');

        $fileName = 'appointment-summary-report-' . now()->format('Y-m-d') . '.pdf';
        return $pdf->download($fileName);
    }

    public function summaryExcel(Request $request)
    {
        $from     = $request->query('from');
        $to       = $request->query('to');
        $branchId = $request->query('branch_id');

        $fileName = 'appointment-summary-report-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new AppointmentSummaryExport($from, $to, $branchId),
            $fileName
        );
    }

    // =========================
    // Appointment List Report
    // =========================

    public function list(Request $request): Response
    {
        $data = $this->buildListData($request);
        return Inertia::render('Reports/AppointmentListReport', $data);
    }

    public function listPdf(Request $request)
    {
        $data = $this->buildListData($request);

        // Many columns -> landscape
        $pdf = Pdf::loadView('reports.appointmentlistreport-pdf', $data)
            ->setPaper('a4', 'landscape');

        $fileName = 'appointment-list-report-' . now()->format('Y-m-d') . '.pdf';
        return $pdf->download($fileName);
    }

    public function listExcel(Request $request)
    {
        $from     = $request->query('from');
        $to       = $request->query('to');
        $branchId = $request->query('branch_id');

        $fileName = 'appointment-list-report-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new AppointmentListExport($from, $to, $branchId),
            $fileName
        );
    }
}
