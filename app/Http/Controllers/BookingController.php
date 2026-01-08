<?php

namespace App\Http\Controllers;

use App\Events\BookingCreated;
use App\Events\BookingUpdated;
use App\Models\Booking;
use App\Notifications\BookingSavedNotification;
use App\Models\BookingSale;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingReceiptMail;
use App\Models\Branch;
use App\Models\NotificationQueue;
use App\Models\User;
use App\Services\SmsGateway;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\URL;

class BookingController extends Controller
{

    public function index()
    {
        // Inertia page: resources/js/Pages/Bookings/Index.vue
        return Inertia::render('Bookings/Index', []);
    }

   public function getData(Request $request)
{
    $query = Booking::query()
        ->leftJoin('clients as c', 'bookings.client_id', '=', 'c.id')
        ->leftJoin('users as s', 'bookings.staff_id', '=', 's.id')
        ->leftJoin('users as pb', 'bookings.placed_by', '=', 'pb.id')
        ->select([
            'bookings.*',
            'c.first_name as client_first_name',
            'c.last_name  as client_last_name',
            'c.email      as client_email',
            's.name       as staff_name',
            'pb.name      as placed_by_name',
        ])
        
        ->withCount('services');


        

   
    if ($request->filled('status')) {
        $query->where('bookings.status', (string) $request->input('status'));
    }

    if ($request->filled('staff_id')) {
        $query->where('bookings.staff_id', (int) $request->input('staff_id'));
    }

    if ($request->filled('date_from')) {
        $query->whereDate('bookings.date', '>=', $request->input('date_from'));
    }

    if ($request->filled('date_to')) {
        $query->whereDate('bookings.date', '<=', $request->input('date_to'));
    }

    if ($request->filled('q')) {
        $q = trim((string) $request->input('q'));
        $like = "%{$q}%";

        $query->where(function ($qq) use ($q, $like) {
            if (is_numeric($q)) {
                $qq->orWhere('bookings.id', (int) $q);
            }

            $qq->orWhere('c.first_name', 'like', $like)
               ->orWhere('c.last_name', 'like', $like)
               ->orWhereRaw("CONCAT(COALESCE(c.first_name,''),' ',COALESCE(c.last_name,'')) like ?", [$like])
               ->orWhere('s.name', 'like', $like);
        });
    }

    if ($request->filled('branch_id')) {
        $query->where('bookings.branch_id', (int) $request->input('branch_id'));
    }

    $query->orderByDesc('bookings.date')
          ->orderByDesc('bookings.starts_at')
          ->orderByDesc('bookings.id');

    return DataTables::eloquent($query)

        ->addColumn('client_name', function ($row) {
            $name = trim(($row->client_first_name ?? '') . ' ' . ($row->client_last_name ?? ''));
            return $name !== '' ? $name : 'Walk-in';
        })
        ->editColumn('date', function ($row) {
            return $row->date ? Carbon::parse($row->date)->toDateString() : null;
        })
        ->addColumn('time', function ($row) {
            $start = $row->starts_at ? Carbon::parse($row->starts_at)->format('H:i') : '';
            $end   = $row->ends_at   ? Carbon::parse($row->ends_at)->format('H:i') : '';
            return trim($start . ' – ' . $end);
        })
        ->addColumn('services_count', function ($row) {
    return (int) ($row->services_count ?? 0);
})

->orderColumn('services_count', function ($q, $order) {
    $q->orderBy('services_count', $order);
})

        ->editColumn('total_price', fn ($row) => (float) ($row->total_price ?? 0))
        ->editColumn('status', fn ($row) => $row->status ?? 'scheduled')
        ->addColumn('action', fn () => '') 

        ->filterColumn('staff_name', function ($q, $keyword) {
            $kw = trim((string) $keyword);
            $q->where('s.name', 'like', "%{$kw}%");
        })
        ->filterColumn('client_name', function ($q, $keyword) {
            $kw = trim((string) $keyword);
            $q->whereRaw(
                "CONCAT(COALESCE(c.first_name,''),' ',COALESCE(c.last_name,'')) like ?",
                ["%{$kw}%"]
            );
        })
        ->filterColumn('time', function ($q, $keyword) {
            $kw = trim((string) $keyword);

            $q->where(function ($qq) use ($kw) {
                $qq->orWhereRaw("DATE_FORMAT(bookings.starts_at, '%H:%i') like ?", ["%{$kw}%"])
                   ->orWhereRaw("DATE_FORMAT(bookings.ends_at,   '%H:%i') like ?", ["%{$kw}%"]);
            });
        })

        ->orderColumn('staff_name', function ($q, $order) {
            $q->orderBy('s.name', $order);
        })
        ->orderColumn('client_name', function ($q, $order) {
            $q->orderBy('c.first_name', $order)->orderBy('c.last_name', $order);
        })
        ->orderColumn('time', function ($q, $order) {
            $q->orderBy('bookings.starts_at', $order);
        })

        ->make(true);
}



    // public function create()
    // {

    //     return inertia('Bookings/CreateUpdate', [

    //     ]);
    // }


 public function store(Request $request)
{
    $data = $request->validate([
        'client_id'  => ['nullable', 'integer'],
        'staff_id'   => ['nullable', 'integer'],
        'branch_id'  => ['nullable', 'integer'],
        'status'     => ['nullable', 'string', 'max:50'],
        'slot_start' => ['nullable', 'date'],
        'slot_end'   => ['nullable', 'date'],

        'services'                      => ['required', 'array', 'min:1'],
        'services.*.service_id'         => ['required', 'integer'],
        'services.*.service_variant_id' => ['nullable', 'integer'],
        'services.*.staff_id'           => ['nullable', 'integer'],
        'services.*.label'              => ['required', 'string', 'max:255'],
        'services.*.duration_minutes'   => ['required', 'integer', 'min:1'],
        'services.*.extra_minutes'      => ['nullable', 'integer', 'min:0'],
        'services.*.price'              => ['required', 'numeric', 'min:0'],
        'services.*.discount_type'      => ['nullable', 'in:none,percent,amount'],
        'services.*.discount_value'     => ['nullable', 'numeric', 'min:0'],
        'services.*.final_price'        => ['required', 'numeric', 'min:0'],
        'services.*.color_code'         => ['nullable', 'string', 'max:20'],

        'note'       => ['nullable', 'string', 'max:2000'],
    ]);

    try {
        DB::beginTransaction();

        $slotStart = !empty($data['slot_start'])
            ? Carbon::parse($data['slot_start'])
            : now();

        $cursor = $slotStart->copy();
        $total  = 0.0;
        $servicesPayload = [];

        foreach ($data['services'] as $svc) {
            $start = $cursor->copy();

            $duration = (int) ($svc['duration_minutes'] ?? 0)
                + (int) ($svc['extra_minutes'] ?? 0);

            $end    = $start->copy()->addMinutes(max(0, $duration));
            $cursor = $end->copy();

            $final  = (float) ($svc['final_price'] ?? 0);
            $total += $final;

            $servicesPayload[] = [
                'service_id'         => (int) $svc['service_id'],
                'service_variant_id' => $svc['service_variant_id'] ?? null,
                'staff_id'           => $svc['staff_id'] ?? ($data['staff_id'] ?? null),
                'label'              => (string) $svc['label'],
                'duration_minutes'   => (int) $svc['duration_minutes'],
                'extra_minutes'      => (int) ($svc['extra_minutes'] ?? 0),
                'starts_at'          => $start,
                'ends_at'            => $end,
                'price'              => (float) $svc['price'],
                'discount_type'      => $svc['discount_type'] ?? 'none',
                'discount_value'     => (float) ($svc['discount_value'] ?? 0),
                'final_price'        => $final,
                'color_code'         => $svc['color_code'] ?? null,
            ];
        }

        $booking = Booking::create([
            'client_id'   => $data['client_id'] ?? null,
            'staff_id'    => $data['staff_id'] ?? null,
            'branch_id'   => $data['branch_id'] ?? null,
            'date'        => $slotStart->toDateString(),
            'starts_at'   => $slotStart,
            'ends_at'     => $cursor,
            'total_price' => $total,
            'status'      => $data['status'] ?? 'scheduled',
            'notes'       => $data['note'] ?? null,
            'placed_by'   => Auth::id(),
        ]);

        // save services
        $booking->services()->createMany($servicesPayload);

        // queue correct "status" sms (scheduled/pending/rejected/cancel...) with the screenshot formatting
        $this->queueStatusSms($booking);

        // queue reminders (only if scheduled)
        $this->queueBookingReminders($booking);

        DB::commit();

        if ($admin = Auth::user()) {
            $admin->notify(new BookingSavedNotification($booking));
        }

        broadcast(new BookingCreated($booking))->toOthers();

        $dateStr = $booking->starts_at
            ? Carbon::parse($booking->starts_at)->format('Y-m-d')
            : (Carbon::parse($booking->date)->format('Y-m-d'));

        return redirect()
            ->route('calendar.index', [
                'date'       => $dateStr,
                'booking_id' => $booking->id,
                'branch_id'  => $booking->branch_id,
            ])
            ->with('success', 'Booking created successfully.');
    } catch (\Throwable $ex) {
        DB::rollBack();
        Log::error($ex);

        return redirect()
            ->route('calendar.index', ['date' => now()->format('Y-m-d')])
            ->with('error', 'Failed to create booking.');
    }
}



   public function storeSale(Request $request)
{
    $data = $request->validate([
        'booking_id'        => ['required', 'exists:bookings,id'],
        'branch_id'         => ['nullable', 'integer'],
        'client_id'         => ['nullable', 'integer', 'exists:clients,id'],
        'payment_method'    => ['nullable', 'string', 'max:50'],

        'base_amount'       => ['required', 'numeric', 'min:0'],
        'tax_amount'        => ['nullable', 'numeric', 'min:0'],
        'tip_amount'        => ['nullable', 'numeric', 'min:0'],
        'tip_staff_id'      => ['nullable', 'integer', 'exists:users,id'],
        'total_with_tip'    => ['required', 'numeric', 'min:0'],

        'total_paid'        => ['required', 'numeric', 'min:0'],
        'remaining'         => ['required', 'numeric', 'min:0'],

'tips_json'               => ['nullable', 'array', 'min:1'],
'tips_json.*.staff_id'    => ['required_with:tips_json', 'integer', 'exists:users,id'],
'tips_json.*.amount'      => ['required_with:tips_json', 'numeric', 'min:0'],



        'payments'          => ['nullable', 'array'],
        'payments.*.method' => ['required_with:payments', 'string', 'max:50'],
        'payments.*.amount' => ['required_with:payments', 'numeric', 'min:0'],

        'services'          => ['nullable', 'array'],
    ]);

    DB::beginTransaction();

    try {
        $booking = Booking::lockForUpdate()->findOrFail($data['booking_id']);

$allowedTipStaffIds = $booking->services()
    ->whereNotNull('staff_id')
    ->pluck('staff_id')
    ->unique()
    ->values()
    ->all();

// fallback if services have no staff_id
if (empty($allowedTipStaffIds) && $booking->staff_id) {
    $allowedTipStaffIds = [(int) $booking->staff_id];
}

$tipsJson = $data['tips_json'] ?? null;

if ($tipsJson) {
    // block tips for non-assigned staff
    foreach ($tipsJson as $t) {
        $sid = (int) ($t['staff_id'] ?? 0);
        if (!in_array($sid, $allowedTipStaffIds, true)) {
            throw new \Exception("Tip staff_id {$sid} is not assigned to this booking.");
        }
    }

    // normalize + compute tip_amount from tips_json
    $tipSum = collect($tipsJson)->sum(fn($t) => (float) ($t['amount'] ?? 0));
    $data['tip_amount'] = $tipSum;

    // if only one staff allowed, lock it
    if (count($allowedTipStaffIds) === 1) {
        $onlyId = $allowedTipStaffIds[0];
        $data['tip_staff_id'] = $onlyId;
        $data['tips_json'] = [[ 'staff_id' => $onlyId, 'amount' => $tipSum ]];
    } else {
        // multiple: no single owner (optional)
        $data['tip_staff_id'] = null;
    }
} else {
    // no tips_json sent → single recipient mode
    if (!empty($data['tip_staff_id']) && !in_array((int)$data['tip_staff_id'], $allowedTipStaffIds, true)) {
        throw new Exception("Tip staff_id is not assigned to this booking.");
    }
}


        if (array_key_exists('client_id', $data)) {
            $booking->client_id = $data['client_id'] ?: null;
        }




     $sale = BookingSale::create([
    'booking_id'     => $booking->id,
    'branch_id'      => $data['branch_id'] ?? optional(auth()->user())->branch_id,
    'payment_method' => $data['payment_method'] ?? null,

    'base_amount'    => $data['base_amount'],
    'tax_amount'     => $data['tax_amount'] ?? 0,
'tip_amount'     => $data['tip_amount'] ?? 0,      
  'tip_staff_id'   => $data['tip_staff_id'] ?? null,
    'total_with_tip' => $data['total_with_tip'],
'tips_json' => $data['tips_json'] ?? null,

    'total_paid'     => $data['total_paid'],
    'remaining'      => $data['remaining'],

    'payments_json'  => $data['payments'] ?? [],
    'services_json'  => $data['services'] ?? [],
]);


        if ($data['remaining'] > 0) {
            $booking->status = 'payment_pending';

            $this->cancelPendingReminders($booking->id);
        } else {
            $booking->status = 'completed';

            $this->cancelPendingReminders($booking->id);

            if ($booking->client_id) {
                $customer = Client::find($booking->client_id);
                if ($customer) {
                    $earnedPoints = floor(((float) $booking->total_price) / 100);
                    $customer->current_points  += $earnedPoints;
                    $customer->lifetime_points += $earnedPoints;
                    $customer->save();
                }
            }

            $this->queueCompletedSaleSmsWithInvoice($booking, $sale);
        }

        $booking->save();

        DB::commit();

        broadcast(new BookingUpdated($booking))->toOthers();

        return back()->with([
            'flash' => [
                'sale_status'     => $booking->status,
                'sale_booking_id' => $booking->id ?? null,
            ],
        ]);
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error($e);

        return response()->json([
            'success' => false,
            'message' => 'Failed to save booking sale',
        ], 500);
    }
}

protected function queueCompletedSaleSmsWithInvoice(Booking $booking, BookingSale $sale): void
{
    $client = $booking->client ?? Client::find($booking->client_id);
    $phone  = $this->buildClientPhone($client);

    if (! $client || ! $phone) return;

    [$date, $time] = $this->bookingDateTimeParts($booking);

    $invoiceUrl = URL::temporarySignedRoute(
        'public.invoice.pdf',
        now()->addDays(30),
        ['sale' => $sale->id]
    );

    $clientName = $this->buildClientName($client);

    $text = $this->smsSaleCompleted($clientName, (int) $sale->id, $date, $time, $invoiceUrl);

    NotificationQueue::create([
        'booking_id'   => $booking->id,
        'client_id'    => $client->id,
        'channel'      => 'sms',
        'type'         => 'sale_completed_invoice',
        'phone'        => $phone,
        'message'      => $text,
        'status'       => 'pending',
        'scheduled_at' => now()->addSeconds(10),
    ]);
}



    public function syncServicesFromTip(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'slot_start' => ['nullable', 'date'],
            'slot_end'   => ['nullable', 'date'],

            'services'                      => ['required', 'array', 'min:1'],
            'services.*.service_id'         => ['required', 'integer'],
            'services.*.service_variant_id' => ['nullable', 'integer'],
            'services.*.label'              => ['required', 'string', 'max:255'],
            'services.*.duration_minutes'   => ['required', 'integer', 'min:1'],
            'services.*.extra_minutes'      => ['nullable', 'integer', 'min:0'],
            'services.*.price'              => ['required', 'numeric', 'min:0'],
            'services.*.discount_type'      => ['nullable', 'in:none,percent,amount'],
            'services.*.discount_value'     => ['nullable', 'numeric', 'min:0'],
            'services.*.final_price'        => ['required', 'numeric', 'min:0'],
            'services.*.starts_at'          => ['nullable', 'date'],
            'services.*.ends_at'            => ['nullable', 'date'],
            'services.*.staff_id' => ['nullable', 'integer', 'exists:users,id'],

        ]);

        DB::beginTransaction();

        try {

            $booking = Booking::lockForUpdate()->findOrFail($booking->id);

            $total     = 0;
            $minStart  = null;
            $maxEnd    = null;
            $payload   = [];

            foreach ($data['services'] as $svc) {

                if (!empty($svc['starts_at'])) {
                    $start = Carbon::parse($svc['starts_at']);
                } else {

                    $start = $booking->starts_at
                        ? $booking->starts_at->copy()
                        : now();
                }

                if (!empty($svc['ends_at'])) {
                    $end = Carbon::parse($svc['ends_at']);
                } else {
                    $duration = (int) ($svc['duration_minutes'] ?? 0)
                        + (int) ($svc['extra_minutes'] ?? 0);

                    $end = $start->copy()->addMinutes(max(0, $duration));
                }


                if (!$minStart || $start->lt($minStart)) {
                    $minStart = $start->copy();
                }
                if (!$maxEnd || $end->gt($maxEnd)) {
                    $maxEnd = $end->copy();
                }


                $final = (float) ($svc['final_price'] ?? $svc['price'] ?? 0);
                $total += $final;

                $payload[] = [
                    'service_id'         => $svc['service_id'],
                    'service_variant_id' => $svc['service_variant_id'] ?? null,
                   'staff_id' => $svc['staff_id'] ?? $booking->staff_id ?? null,

                    'label'              => $svc['label'],

                    'duration_minutes'   => (int) $svc['duration_minutes'],
                    'extra_minutes'      => (int) ($svc['extra_minutes'] ?? 0),

                    'starts_at'          => $start,
                    'ends_at'            => $end,

                    'price'              => (float) $svc['price'],
                    'discount_type'      => $svc['discount_type'] ?? 'none',
                    'discount_value'     => (float) ($svc['discount_value'] ?? 0),
                    'final_price'        => $final,

                    'color_code'         => $svc['color_code'] ?? null,

                ];
            }


            $booking->services()->delete();
            $booking->services()->createMany($payload);


            if ($minStart) {
                $booking->starts_at = $minStart;
                $booking->date      = $minStart->toDateString();
            }
            if ($maxEnd) {
                $booking->ends_at = $maxEnd;
            }

            $booking->total_price = $total;
            $booking->save();

            DB::commit();
$this->queueBookingReminders($booking);

            broadcast(new BookingUpdated($booking))->toOthers();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e);

            return response()->json([
                'success' => false,
                'message' => 'Failed to sync booking services from tip panel.',
            ], 500);
        }
    }

    public function showDetails(Booking $booking)
    {
        $booking->load([
            'client',
            'staff',
            'services.service.category',
             'services.staff',
            'sales',
            'placedBy',
            'approvedBy',
            'rejectedBy',
            'cancelledBy',
            'blockedBy',
        ]);

        // ---- Sales + summary (same as before) ---------------------------------
        $sales = $booking->sales()
            ->orderBy('created_at', 'asc')
            ->get();

        $salesData = $sales->map(function (BookingSale $sale) {
            return [
                'id'             => $sale->id,
                'payment_method' => $sale->payment_method,
                'base_amount'    => (float) $sale->base_amount,
                'tax_amount'     => (float) $sale->tax_amount,
                'tip_amount'     => (float) $sale->tip_amount,
                'total_with_tip' => (float) $sale->total_with_tip,
                'total_paid'     => (float) $sale->total_paid,
                'remaining'      => (float) $sale->remaining,
                'payments'       => $sale->payments_json ?? [],
                'services'       => $sale->services_json ?? [],
                'created_at'     => optional($sale->created_at)->toDateTimeString(),
                'updated_at'     => optional($sale->updated_at)->toDateTimeString(),
            ];
        });

        $latestSale = $salesData->last();

        $totalPaid = $salesData->sum('total_paid');
        $remaining = $latestSale['remaining']
            ?? max(0, (float) $booking->total_price - $totalPaid);
        $status    = $booking->status ?? 'scheduled';

        $summary = [
            'booking_id'  => $booking->id,
            'status'      => $status,
            'total_price' => (float) $booking->total_price,
            'total_paid'  => (float) $totalPaid,
            'remaining'   => (float) $remaining,
            'has_sales'   => $salesData->isNotEmpty(),
        ];

        $client = $booking->client;
        $placedByUser = $booking->placedBy;

        $approvedByUser  = $booking->approvedBy;
        $rejectedByUser  = $booking->rejectedBy;
        $cancelledByUser = $booking->cancelledBy;
        $blockedByUser   = $booking->blockedBy;

        $statusChangedUser = null;
        switch ((string) $booking->status) {
            case 'cancel':
            case 'cancelled':
                $statusChangedUser = $cancelledByUser ?: $approvedByUser ?: $placedByUser;
                break;

            case 'rejected':
                $statusChangedUser = $rejectedByUser ?: $approvedByUser ?: $placedByUser;
                break;

            case 'blocked_time':
                $statusChangedUser = $blockedByUser ?: $placedByUser;
                break;

            case 'scheduled':
            case 'arrived':
            case 'started':
            case 'completed':
                $statusChangedUser = $approvedByUser ?: $placedByUser;
                break;

            default:
                $statusChangedUser = $approvedByUser ?: $placedByUser;
                break;
        }

        if ($client) {
            $first = trim((string) $client->first_name);
            $last  = trim((string) $client->last_name);
            $clientName = trim($first . ' ' . $last);

            if ($clientName === '') {
                $clientName = $client->name ?: 'Walk-in';
            }
        } else {
            $clientName = 'Walk-in';
        }

        
        $bookingData = [
            'id'             => $booking->id,
            'date'           => optional($booking->date)->toDateString(),
            'date_formatted' => optional($booking->date)->format('Y-m-d'),
            'starts_at'      => optional($booking->starts_at)->toDateTimeString(),
            'ends_at'        => optional($booking->ends_at)->toDateTimeString(),
            'total_price'    => (float) $booking->total_price,
            'status'         => $booking->status,
            'notes'          => $booking->notes,
            'created_at'     => optional($booking->created_at)->toDateTimeString(),
 'cancel_reson'   => $booking->cancel_reson,
            'client_name'  => $clientName,
            'client_email' => $client->email ?? null,
            'client_phone' => $client->phone ?? null,
            'branch_id' => $booking->branch_id,


            'client' => $client ? [
                'id'         => $client->id,
                'first_name' => $client->first_name,
                'last_name'  => $client->last_name,
                'name'       => $clientName,
                'full_name'  => $clientName,
                'email'      => $client->email,
                'phone'      => $client->phone ?? null,
            ] : null,

            'staff' => $booking->staff ? [
                'id'   => $booking->staff->id,
                'name' => $booking->staff->name,
            ] : null,

            'placed_by_user' => $placedByUser ? [
                'id'   => $placedByUser->id,
                'name' => $placedByUser->name,
            ] : null,

            'approved_by_user' => $approvedByUser ? [
                'id'   => $approvedByUser->id,
                'name' => $approvedByUser->name,
            ] : null,

            'rejected_by_user' => $rejectedByUser ? [
                'id'   => $rejectedByUser->id,
                'name' => $rejectedByUser->name,
            ] : null,

            'cancelled_by_user' => $cancelledByUser ? [
                'id'   => $cancelledByUser->id,
                'name' => $cancelledByUser->name,
            ] : null,

            'blocked_by_user' => $blockedByUser ? [
                'id'   => $blockedByUser->id,
                'name' => $blockedByUser->name,
            ] : null,

            'status_changed_by' => $statusChangedUser ? [
                'id'   => $statusChangedUser->id,
                'name' => $statusChangedUser->name,
            ] : null,

            'placed_by'   => $booking->placed_by,

            'services' => $booking->services->map(function ($svc) {
                $service  = $svc->service;
                $category = $service?->category;

                return [
                    'id'                 => $svc->id,
                    'service_id'         => $svc->service_id,
                    'service_variant_id' => $svc->service_variant_id,
                    'label'              => $svc->label,
                    'duration_minutes'   => (int) $svc->duration_minutes,
                    'duration'           => (int) $svc->duration_minutes,
                    'extra_minutes'      => (int) $svc->extra_minutes,
                    'starts_at'          => optional($svc->starts_at)->toDateTimeString(),
                    'ends_at'            => optional($svc->ends_at)->toDateTimeString(),
                    'price'              => (float) $svc->price,
                    'discount_type'      => $svc->discount_type,
                    'discount_value'     => (float) $svc->discount_value,
                    'final_price'        => (float) $svc->final_price,
                    
                     'staff_id'           => $svc->staff_id,
        'staff'              => $svc->staff ? [
            'id'   => $svc->staff->id,
            'name' => $svc->staff->name,
        ] : null,
                    'color_code'         => $svc->color_code
                        ?? $category?->color_code
                        ?? '#f97316',
                ];
            })->values(),
        ];

        return response()->json([
            'booking'         => $bookingData,
            'sale'            => $latestSale,
            'sales'           => $salesData,
            'summary'         => $summary,
            'currency_symbol' => 'LKR',
        ]);
    }


    public function updateClient(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'client_id' => ['nullable', 'exists:clients,id'],
        ]);

        $booking->client_id = $data['client_id'] ?? null;
        $booking->save();

        $booking->load('client');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'booking' => $booking,
            ]);
        }

        return back()->with('success', 'Client updated.');
    }
    public function markPaymentPending(Request $request, Booking $booking)
    {
        if ($request->filled('client_id')) {
            $clientId = (int) $request->input('client_id');

            if ($clientId > 0 && Client::whereKey($clientId)->exists()) {
                $booking->client_id = $clientId;
            }
        }

        $booking->status = 'payment_pending';
        $booking->save();

        broadcast(new BookingUpdated($booking))->toOthers();

        if ($request->wantsJson()) {
            return response()->json([
                'success'   => true,
                'status'    => $booking->status,
                'client_id' => $booking->client_id,
            ]);
        }

        return back()->with('success', 'Booking marked as payment pending.');
    }

    public function storeBlockedTime(Request $request)
    {
        $data = $request->validate([
            'staff_id' => ['required', 'integer'],
            'date' => ['required', 'date'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date'],
            'block_type' => ['nullable', 'string', 'max:255'],
            'frequency' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'branch_id' => ['nullable', 'integer'],
        ]);

        try {
            DB::beginTransaction();

            $booking = Booking::create([
                'staff_id' => $data['staff_id'],
                'date' => $data['date'],
                'starts_at' => $data['starts_at'],
                'ends_at' => $data['ends_at'],
                'status' => 'blocked_time',
                'block_type' => $data['block_type'] ?? 'custom',
                'frequency' => $data['frequency'] ?? 'does_not_repeat',
                'description' => $data['description'] ?? null,
                'total_price' => 0,
                'client_id' => null,
                'blocked_by' => $request->user()->id,
            ]);

            DB::commit();

            broadcast(new BookingCreated($booking))->toOthers();

            return redirect()
                ->route('calendar.index', [
                    'date' => $booking->date->format('Y-m-d'),
                    'booking_id' => $booking->id,
                    'branch_id' => $request->input('branch_id'),
                ])
                ->with('success', 'Blocked time created successfully.');
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex);

            return redirect()
                ->route('calendar.index', ['date' => now()->format('Y-m-d')])
                ->with('error', 'Failed to create blocked time.');
        }
    }
    public function updateBlockedTime(Request $request, Booking $booking)
    {
        if ($booking->status !== 'blocked_time') {
            abort(404);
        }

        $data = $request->validate([
            'staff_id'    => ['required', 'integer'],
            'date'        => ['required', 'date'],
            'starts_at'   => ['required', 'date'],
            'ends_at'     => ['required', 'date'],
            'block_type'  => ['nullable', 'string', 'max:255'],
            'frequency'   => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'branch_id'   => ['nullable', 'integer'],
        ]);

        try {
            DB::beginTransaction();

            $booking->staff_id    = $data['staff_id'];
            $booking->date        = $data['date'];
            $booking->starts_at   = $data['starts_at'];
            $booking->ends_at     = $data['ends_at'];
            $booking->status      = 'blocked_time';
            $booking->block_type  = $data['block_type'] ?? $booking->block_type ?? 'custom';
            $booking->frequency   = $data['frequency'] ?? $booking->frequency ?? 'does_not_repeat';
            $booking->description = $data['description'] ?? null;
            $booking->total_price = 0;
            $booking->client_id   = null;

            $booking->blocked_by  = $request->user()->id;

            $booking->save();

            DB::commit();

            broadcast(new BookingUpdated($booking))->toOthers();

            return redirect()
                ->route('calendar.index', [
                    'date'      => $booking->date->format('Y-m-d'),
                    'booking_id' => $booking->id,
                    'branch_id' => $request->input('branch_id'),
                ])
                ->with('success', 'Blocked time updated successfully.');
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex);

            return redirect()
                ->route('calendar.index', ['date' => now()->format('Y-m-d')])
                ->with('error', 'Failed to update blocked time.');
        }
    }
    public function emailReceipt(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'email' => ['nullable', 'email'],
            'pdf'   => ['nullable', 'file', 'mimes:pdf', 'max:10240'], // up to 10MB
        ]);

        $client = $booking->client ?? null;
        $email  = $data['email'] ?? ($client->email ?? null);

        if (!$email) {
            return response()->json([
                'message' => 'No email address available for this client.',
            ], 422);
        }

        $sales = $booking->sales()
            ->orderBy('created_at', 'asc')
            ->get();

        $totalPaid  = (float) $sales->sum('total_paid');
        $latestSale = $sales->last();

        $remaining = $latestSale
            ? (float) $latestSale->remaining
            : max(0, (float) $booking->total_price - $totalPaid);

        $summary = (object) [
            'booking_id'  => $booking->id,
            'status'      => $booking->status ?? 'scheduled',
            'total_price' => (float) $booking->total_price,
            'total_paid'  => $totalPaid,
            'remaining'   => $remaining,
            'has_sales'   => $sales->isNotEmpty(),
        ];

        $currencySymbol = 'LKR';

        $salesData = $sales->map(function (BookingSale $sale) {
            return [
                'id'             => $sale->id,
                'payment_method' => $sale->payment_method,
                'base_amount'    => (float) $sale->base_amount,
                'tax_amount'     => (float) $sale->tax_amount,
                'tip_amount'     => (float) $sale->tip_amount,
                'total_with_tip' => (float) $sale->total_with_tip,
                'total_paid'     => (float) $sale->total_paid,
                'remaining'      => (float) $sale->remaining,
                'payments'       => $sale->payments_json ?? [],
                'services'       => $sale->services_json ?? [],
                'created_at'     => optional($sale->created_at)->toDateTimeString(),
                'updated_at'     => optional($sale->updated_at)->toDateTimeString(),
            ];
        })->toArray();

        $servicesData = $booking->services()
            ->get()
            ->map(function ($svc) {
                $service  = $svc->service;
                $category = $service?->category;

                return [
                    'id'            => $svc->id,
                    'label'         => $svc->label ?? $svc->name ?? 'Service',
                    'duration'      => (int) ($svc->duration ?? $svc->duration_minutes ?? 0),
                    'extra_minutes' => (int) ($svc->extra_minutes ?? 0),
                    'starts_at'     => optional($svc->starts_at)->toDateTimeString(),
                    'ends_at'       => optional($svc->ends_at)->toDateTimeString(),
                    'price'         => (float) $svc->price,
                    'final_price'   => (float) ($svc->final_price ?? $svc->price ?? 0),
                    'discount_type' => $svc->discount_type ?? null,
                    'discount_value' => (float) ($svc->discount_value ?? 0),
                    'color_code'    => $svc->color_code
                        ?? $category?->color_code
                        ?? '#f97316',
                ];
            })
            ->toArray();

        $uploadedPdf = $request->file('pdf');
        $pdfName     = $uploadedPdf
            ? ($uploadedPdf->getClientOriginalName() ?: 'booking-receipt.pdf')
            : null;

        $mail = new BookingReceiptMail($booking, [
            'client'         => $client,
            'summary'        => $summary,
            'sales'          => $salesData,
            'services'       => $servicesData,
            'currencySymbol' => $currencySymbol,
        ]);

        if ($uploadedPdf) {
            $mail->attach(
                $uploadedPdf->getRealPath(),
                [
                    'as'   => $pdfName,
                    'mime' => 'application/pdf',
                ]
            );
        }


        Mail::to($email)->send($mail);

        return response()->json([
            'message' => 'Receipt emailed successfully.',
        ]);
    }
    public function waitlistpending(Request $request)
    {
      $query = Booking::query()
    ->where('status', 'pending')
    ->with(['client']) 
    ->withCount([
        'services as unassigned_staff_count' => function ($q) {
            $q->whereNull('staff_id');
        }
    ]);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        $bookings = $query
            ->orderBy('date')
            ->orderBy('starts_at')
            ->get();

        $clientIds = $bookings->pluck('client_id')->unique()->filter();

        $stats = Booking::select(
            'client_id',
            DB::raw("SUM(CASE WHEN status = 'cancel' THEN 1 ELSE 0 END) AS cancel_count"),
            DB::raw("SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) AS no_show_count"),
            DB::raw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_count"),
            DB::raw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_count"),
            DB::raw("COUNT(*) AS total_bookings")
        )
            ->whereIn('client_id', $clientIds)
            ->groupBy('client_id')
            ->get()
            ->keyBy('client_id');


        $items = $bookings->map(function (Booking $b) use ($stats) {

            $clientStats = $stats->get($b->client_id);
            $riskScore = $clientStats
                ? $this->calculateRiskScore($clientStats)
                : 0;

            $cancelCount    = $clientStats->cancel_count ?? 0;
            $completedCount = $clientStats->completed_count ?? 0;
            $noShowCount    = $clientStats->no_show_count ?? 0;
            $totalBookings  = $clientStats->total_bookings ?? 0;

            $servicesCount   = $b->services->count();
            $durationMinutes = $b->services->sum(function ($svc) {
                return (int) $svc->duration_minutes + (int) $svc->extra_minutes;
            });

            return [
                'id'               => $b->id,
                'status'           => (string) $b->status,
                'date'             => optional($b->date)->toDateString(),
                'starts_at'        => optional($b->starts_at)->toDateTimeString(),
                'created_at'       => optional($b->created_at)->toDateTimeString(),
                 'unassigned_staff_count' => (int) ($b->unassigned_staff_count ?? 0),
    'needs_staff' => ((int) ($b->unassigned_staff_count ?? 0)) > 0,
                'total_price'      => (float) ($b->total_price ?? 0),
                'client_name'      => $b->client
                    ? trim($b->client->first_name . ' ' . $b->client->last_name)
                    : 'Walk-in',
                'staff_name'       => $b->staff_name
                    ?? optional($b->staff)->name,
                'services_count'   => $servicesCount ?: null,
                'duration_minutes' => $durationMinutes ?: null,

                'cancel_count'     => $cancelCount,
                'completed_count'  => $completedCount,
                'no_show_count'    => $noShowCount,
                'total_bookings'   => $totalBookings,
                'pending_count'    => $clientStats->pending_count ?? 0,
                'risk_score'       => $riskScore,
            ];
        })->values();

        // Log::info($items);
        return response()->json([
            'data' => $items,
        ]);
    }
    public function waitlistShow(Booking $booking)
    {
        if ($booking->status !== 'pending') {
            abort(404);
        }

        $booking->load(['client', 'staff', 'services.service']);

        $services = $booking->services->map(function ($svc) {
            return [
                'id'                 => $svc->id,
                'service_id'         => $svc->service_id,
                'service_variant_id' => $svc->service_variant_id,
                'label'              => $svc->label,
                'duration_minutes'   => (int) $svc->duration_minutes,
                'extra_minutes'      => (int) $svc->extra_minutes,
                'starts_at'          => optional($svc->starts_at)->toDateTimeString(),
                'ends_at'            => optional($svc->ends_at)->toDateTimeString(),
                'price'              => (float) $svc->price,
                'discount_type'      => $svc->discount_type,
                'discount_value'     => (float) $svc->discount_value,
                'final_price'        => (float) $svc->final_price,
                'color_code'         => $svc->color_code,
                'staff_id'           => $svc->staff_id,
            ];
        })->values();

        $startsAt = optional($booking->starts_at);

        return response()->json([
            'booking' => [
                'id'        => $booking->id,
                'branch_id' => $booking->branch_id,
                'date'      => optional($booking->date)->toDateString(),
                'time'      => $startsAt ? $startsAt->format('H:i') : null,
                'note'      => $booking->notes,
                'status'    => $booking->status,
                'services'  => $services,
            ],
        ]);
    }
    public function waitlistUpdate(Request $request, Booking $booking)
    {
        if ($booking->status !== 'pending') {
            abort(404);
        }

        $data = $request->validate([
            'branch_id'  => ['nullable', 'integer'],
            'date'       => ['required', 'date'],
            'time'       => ['required', 'date_format:H:i'],
            'note'       => ['nullable', 'string', 'max:2000'],

            'services'                      => ['required', 'array', 'min:1'],
            'services.*.id'                 => ['nullable', 'integer'], 
            'services.*.service_id'         => ['required', 'integer'],
            'services.*.service_variant_id' => ['nullable', 'integer'],
            'services.*.label'              => ['required', 'string', 'max:255'],
            'services.*.duration_minutes'   => ['required', 'integer', 'min:1'],
            'services.*.extra_minutes'      => ['nullable', 'integer', 'min:0'],
            'services.*.price'              => ['required', 'numeric', 'min:0'],
            'services.*.discount_type'      => ['nullable', 'in:none,percent,amount'],
            'services.*.discount_value'     => ['nullable', 'numeric', 'min:0'],
            'services.*.final_price'        => ['required', 'numeric', 'min:0'],
            'services.*.color_code'         => ['nullable', 'string', 'max:20'],
            'services.*.staff_id'           => ['nullable', 'integer'],
        ]);

        try {
            DB::beginTransaction();

            $slotStart = Carbon::parse($data['date'] . ' ' . $data['time']);
            $cursor    = $slotStart->copy();
            $total     = 0;
            $keepIds   = [];

            foreach ($data['services'] as $svc) {
                $start = $cursor->copy();

                $duration = (int) ($svc['duration_minutes'] ?? 0)
                    + (int) ($svc['extra_minutes'] ?? 0);

                $end    = $start->copy()->addMinutes($duration);
                $cursor = $end->copy();

                $final  = (float) $svc['final_price'];
                $total += $final;

                $payload = [
                    'service_id'         => $svc['service_id'],
                    'service_variant_id' => $svc['service_variant_id'] ?? null,
                    'staff_id'           => $svc['staff_id'] ?? null,
                    'label'              => $svc['label'],
                    'duration_minutes'   => (int) $svc['duration_minutes'],
                    'extra_minutes'      => (int) ($svc['extra_minutes'] ?? 0),
                    'starts_at'          => $start,
                    'ends_at'            => $end,
                    'price'              => (float) $svc['price'],
                    'discount_type'      => $svc['discount_type'] ?? 'none',
                    'discount_value'     => (float) ($svc['discount_value'] ?? 0),
                    'final_price'        => $final,
                    'color_code'         => $svc['color_code'] ?? null,
                ];

                if (!empty($svc['id'])) {
                    $svcModel = $booking->services()->where('id', $svc['id'])->first();
                    if ($svcModel) {
                        $svcModel->update($payload);
                        $keepIds[] = $svcModel->id;
                    } else {
                        $keepIds[] = $booking->services()->create($payload)->id;
                    }
                } else {
                    $keepIds[] = $booking->services()->create($payload)->id;
                }
            }

            if (!empty($keepIds)) {
                $booking->services()
                    ->whereNotIn('id', $keepIds)
                    ->delete();
            } else {
                $booking->services()->delete();
            }

            $booking->update([
                'branch_id'   => $data['branch_id'] ?? $booking->branch_id,
                'date'        => $slotStart->toDateString(),
                'starts_at'   => $slotStart,
                'ends_at'     => $cursor,
                'total_price' => $total,
                'notes'       => $data['note'] ?? $booking->notes,
            ]);

            DB::commit();
$this->queueBookingReminders($booking);

            broadcast(new BookingUpdated($booking))->toOthers();

            return response()->json([
                'success'     => true,
                'booking_id'  => $booking->id,
                'total_price' => $total,
            ]);
        } catch (\Throwable $ex) {
            DB::rollBack();
            Log::error($ex);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update booking.',
            ], 500);
        }
    }
    public function branchSelect()
    {
        $branches = Branch::orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'data' => $branches,
        ]);
    }

   public function employeeSelect(Request $request)
{
    $branchId = $request->input('branch_id');
    $includeSuperAdmin = $request->boolean('include_super_admin');

    // 1) Staff by branch (only bookable staff)
    $query = User::query()->orderBy('name');

    if ($branchId) {
        $query->whereHas('branches', function ($q) use ($branchId) {
            $q->where('branches.id', $branchId);
        });
    }

    $branchStaff = $query->get()
        ->filter(function (User $u) {
            return $u->hasPermissionTo('calendar.staff');
        })
        ->map(function (User $u) {
            return [
                'id' => (int) $u->id,
                'name' => (string) $u->name,
                'is_super_admin' => $u->hasRole('Super Admin'),
            ];
        })
        ->values();

    // 2) Optional: add Super Admin(s) even if not in branch
    if ($includeSuperAdmin) {
        $superAdmins = User::role('Super Admin')
            ->orderBy('name')
            ->get()
            ->map(function (User $u) {
                return [
                    'id' => (int) $u->id,
                    'name' => (string) $u->name . ' (Super Admin)',
                    'is_super_admin' => true,
                ];
            });

        $merged = $branchStaff
            ->concat($superAdmins)
            ->unique('id')
            ->values();

        // keep super admins at top, then name
        $merged = $merged
            ->sortBy('name')
            ->sortByDesc('is_super_admin')
            ->values();

        return response()->json(['data' => $merged]);
    }

    return response()->json(['data' => $branchStaff]);
}

    // public function edit($id)
    // {
    //     $data = Booking::with(['services', 'client', 'staff'])->findOrFail($id);



    //     return inertia('Bookings/CreateUpdate', [
    //         'data' => $data,

    //     ]);
    // }


    // public function update(Request $request)
    // {
    //     $data = $request->validate([
    //         'id'         => ['required', 'exists:bookings,id'],
    //         'client_id'  => ['nullable', 'integer'],
    //         'staff_id'   => ['nullable', 'integer'],
    //         'status'     => ['nullable', 'string', 'max:50'],
    //         'slot_start' => ['nullable', 'date'],
    //         'slot_end'   => ['nullable', 'date'],

    //         'services'                      => ['required', 'array', 'min:1'],
    //         'services.*.service_id'         => ['required', 'integer'],
    //         'services.*.service_variant_id' => ['nullable', 'integer'],
    //         'services.*.label'              => ['required', 'string', 'max:255'],
    //         'services.*.duration_minutes'   => ['required', 'integer', 'min:1'],
    //         'services.*.extra_minutes'      => ['nullable', 'integer', 'min:0'],
    //         'services.*.price'              => ['required', 'numeric', 'min:0'],
    //         'services.*.discount_type'      => ['nullable', 'in:none,percent,amount'],
    //         'services.*.discount_value'     => ['nullable', 'numeric', 'min:0'],
    //         'services.*.final_price'        => ['required', 'numeric', 'min:0'],
    //         'services.*.color_code'         => ['nullable', 'string', 'max:20'],
    //     ]);

    //     try {
    //         DB::beginTransaction();

    //         $booking = Booking::findOrFail($data['id']);

    //         $slotStart = $data['slot_start']
    //             ? Carbon::parse($data['slot_start'])
    //             : Carbon::parse($booking->starts_at);

    //         $cursor = $slotStart->copy();
    //         $total  = 0;
    //         $servicesPayload = [];

    //         foreach ($data['services'] as $svc) {
    //             $start = $cursor->copy();

    //             $duration = (int) ($svc['duration_minutes'] ?? 0)
    //                 + (int) ($svc['extra_minutes'] ?? 0);

    //             $end    = $start->copy()->addMinutes($duration);
    //             $cursor = $end->copy();

    //             $final  = (float) $svc['final_price'];
    //             $total += $final;

    //             $servicesPayload[] = [
    //                 'service_id'         => $svc['service_id'],
    //                 'service_variant_id' => $svc['service_variant_id'] ?? null,
    //                 'staff_id'           => $data['staff_id'] ?? null,
    //                 'label'              => $svc['label'],
    //                 'duration_minutes'   => (int) $svc['duration_minutes'],
    //                 'extra_minutes'      => (int) ($svc['extra_minutes'] ?? 0),
    //                 'starts_at'          => $start,
    //                 'ends_at'            => $end,
    //                 'price'              => (float) $svc['price'],
    //                 'discount_type'      => $svc['discount_type'] ?? 'none',
    //                 'discount_value'     => (float) ($svc['discount_value'] ?? 0),
    //                 'final_price'        => $final,
    //                 'color_code'         => $svc['color_code'] ?? null,
    //             ];
    //         }

    //         $booking->update([
    //             'client_id'   => $data['client_id'] ?? null,
    //             'staff_id'    => $data['staff_id'] ?? null,
    //             'date'        => $slotStart->toDateString(),
    //             'starts_at'   => $slotStart,
    //             'ends_at'     => $cursor,
    //             'total_price' => $total,
    //             'status'      => $data['status'] ?? $booking->status,
    //         ]);

    //         $booking->services()->delete();
    //         $booking->services()->createMany($servicesPayload);

    //         DB::commit();

    //         return redirect()->route('bookings.index');
    //     } catch (Exception $ex) {
    //         DB::rollBack();
    //         Log::error($ex);
    //         return redirect()->route('bookings.index');
    //     }
    // }


  public function updateStatus(Request $request, Booking $booking)
{
    $data = $request->validate([
        'status' => [
            'required',
            'string',
            'in:scheduled,arrived,started,no_show,cancel,payment_pending,completed,rejected',
        ],
        'cancel_reson' => [
            'nullable',
            'string',
            'max:255',
            'required_if:status,cancel',
        ],
    ]);

    $oldStatus = $booking->status;
    $newStatus = $data['status'];

    $booking->status = $newStatus;

    $userId = $request->user()->id;

    if ($oldStatus === 'pending' && $newStatus === 'scheduled') {
        $booking->approved_by = $userId;
    }
    if (in_array($newStatus, ['arrived', 'started', 'completed'], true)) {
        $booking->approved_by = $userId;
    }
    if ($newStatus === 'rejected') {
        $booking->rejected_by = $userId;
    }

    if ($newStatus === 'cancel') {
        $booking->cancelled_by = $userId;
        $booking->cancel_reson = $data['cancel_reson'] ?? null;
    } else {
        $booking->cancel_reson = null;
    }

    $booking->save();

   if ($oldStatus !== $booking->status) {

    $this->queueStatusSms($booking);

    if ($newStatus === 'scheduled') {
        $this->queueBookingReminders($booking);
    }

    if (in_array($newStatus, ['cancel', 'completed', 'no_show', 'rejected'], true)) {
        $this->cancelPendingReminders($booking->id);
    }
}


    broadcast(new BookingUpdated($booking))->toOthers();

    return response()->json([
        'success' => true,
        'status'  => $booking->status,
    ]);
}



    // public function destroy(Request $request)
    // {
    //     try {
    //         Booking::destroy($request->ids);
    //         return redirect()->route('bookings.index');
    //     } catch (Exception $ex) {
    //         Log::error($ex);
    //         return redirect()->route('bookings.index');
    //     }
    // }

    private function calculateRiskScore($s)
    {
        $cancel     = $s->cancel_count ?? 0;
        $noShow     = $s->no_show_count ?? 0;
        $completed  = $s->completed_count ?? 0;
        $pending    = $s->pending_count ?? 0;

        $pendingAbuse = $pending >= 3 ? 15 : 0;

        $score = ($cancel * 20)
            + ($noShow * 35)
            + $pendingAbuse
            - ($completed * 5);

        return max(0, min($score, 100));
    }

 protected function queueStatusSms(Booking $booking): void
{
    $client = $booking->client ?? Client::find($booking->client_id);
    $phone  = $this->buildClientPhone($client);

    if (! $client || ! $phone) return;

    [$date, $time] = $this->bookingDateTimeParts($booking);

    $status     = strtolower((string) ($booking->status ?? ''));
    $clientName = $this->buildClientName($client);
    $branchName = $this->buildBranchName($booking);
    $teamMember = $this->buildTeamMemberName($booking);

    $text = null;

    switch ($status) {
        case 'scheduled':
            $text = $this->smsBookingConfirmed($clientName, $branchName, $date, $time, $teamMember);
            break;

        case 'pending':
            $text = $this->smsBookingPending($clientName, $branchName, $date, $time);
            break;

        case 'rejected':
            $text = $this->smsPendingClosed($clientName, $date, $time);
            break;

        case 'cancel':
        case 'cancelled':
            $reason = trim((string) ($booking->cancel_reson ?? ''));

            $isLateArrival = $reason !== '' && (
                stripos($reason, 'late') !== false ||
                stripos($reason, 'late arrival') !== false
            );

            $text = $isLateArrival
                ? $this->smsCancelledLateArrival($clientName, (int) $booking->id)
                : $this->smsCancelledGeneric($clientName, (int) $booking->id, $reason ?: null);
            break;

        case 'arrived':
            $timePart = $time ? "Time: {$time}" : "Time: -";
            $text = trim("Hi {$clientName},\n\nWe've marked you as arrived for your appointment.\n\nDate: {$date}\n{$timePart}\n\nPlease take a seat - we'll be with you shortly.");
            break;

        case 'started':
            $text = trim("Hi {$clientName},\n\nYour service has now started.\n\nThank you for choosing us!");
            break;

        case 'no_show':
            $timePart = $time ? "Time: {$time}" : "Time: -";
            $text = trim("Hi {$clientName},\n\nYou missed your booking.\n\nDate: {$date}\n{$timePart}\n\nPlease contact us to reschedule.");
            break;

        case 'payment_pending':
            $timePart = $time ? "Time: {$time}" : "Time: -";
            $text = trim("Hi {$clientName},\n\nYour booking is awaiting payment.\n\nDate: {$date}\n{$timePart}");
            break;

        case 'completed':
            $text = trim("Hi {$clientName},\n\nYour appointment has been marked as completed.\n\nThank you for visiting.");
            break;

        default:
            $pretty = trim(str_replace('_', ' ', $status));
            // $text = trim("Hi {$clientName},\n\nYour booking status is now: {$pretty}.");
            break;
    }

    if (! $text) return;

    NotificationQueue::create([
        'booking_id'   => $booking->id,
        'client_id'    => $client->id,
        'channel'      => 'sms',
        'type'         => 'booking_status',
        'phone'        => $phone,
        'message'      => $text,
        'status'       => 'pending',
        'scheduled_at' => now()->addSeconds(10),
    ]);
}


    protected function buildClientPhone(?Client $client): ?string
    {
        if (! $client) {
            return null;
        }

        $code  = trim((string) ($client->phone_code ?? ''));
        $phone = trim((string) ($client->phone ?? ''));

        if ($code === '' && $phone === '') {
            return null;
        }

        if ($code !== '' && $code[0] !== '+') {
            $code = '+' . $code;
        }

        $phone = ltrim($phone, '0');

        return trim($code . ' ' . $phone);
    }

    protected function buildClientName(?Client $client): string
    {
        if (! $client) {
            return 'Customer';
        }

        $parts = [];

        if (! empty($client->first_name)) {
            $parts[] = trim($client->first_name);
        }

        if (! empty($client->last_name)) {
            $parts[] = trim($client->last_name);
        }

        $name = trim(implode(' ', $parts));

        if ($name === '' && ! empty($client->name)) {
            $name = trim($client->name);
        }

        return $name !== '' ? $name : 'Customer';
    }


private const REMINDER_TYPES = [
    'booking_reminder_1d',
    'booking_reminder_1h',
];

protected function cancelPendingReminders(int $bookingId): void
{
    NotificationQueue::where('booking_id', $bookingId)
        ->where('channel', 'sms')
        ->whereIn('type', self::REMINDER_TYPES)
        ->where('status', 'pending')
        ->update([
            'status' => 'cancelled',
            'error_message' => 'Booking changed/cancelled before reminder was sent',
        ]);
}

protected function queueBookingReminders(Booking $booking): void
{
    $client = $booking->client ?? Client::find($booking->client_id);
    $phone  = $this->buildClientPhone($client);

    if (! $client || ! $phone) return;

    $status = strtolower((string) $booking->status);

    // Only for confirmed/scheduled bookings
    if ($status !== 'scheduled') {
        if (in_array($status, ['cancel', 'cancelled', 'completed', 'no_show', 'rejected', 'blocked_time'], true)) {
            $this->cancelPendingReminders($booking->id);
        }
        return;
    }

    $startsAt = $booking->starts_at ? Carbon::parse($booking->starts_at) : null;
    if (! $startsAt) return;

    // reschedule-safe
    $this->cancelPendingReminders($booking->id);

    $clientName = $this->buildClientName($client);
    $date = $startsAt->format('Y-m-d');
    $time = $startsAt->format('H:i');

    $items = [
        [
            'type'    => 'booking_reminder_1d',
            'send_at' => $startsAt->copy()->subDay(),
            'message' => $this->smsReminder1Day($clientName, $date, $time),
        ],
        [
            'type'    => 'booking_reminder_1h',
            'send_at' => $startsAt->copy()->subHour(),
            'message' => $this->smsReminder1Hour($clientName, $date, $time),
        ],
    ];

    foreach ($items as $it) {
        if ($it['send_at']->lte(now())) continue;

        NotificationQueue::create([
            'booking_id'   => $booking->id,
            'client_id'    => $client->id,
            'channel'      => 'sms',
            'type'         => $it['type'],
            'phone'        => $phone,
            'message'      => $it['message'],
            'status'       => 'pending',
            'scheduled_at' => $it['send_at'],
        ]);
    }
}


public function editSale($sale)
{
    // temporary stub (replace later with real sale loading)
    return inertia('Sales/Edit', [
        'saleId' => $sale,
    ]);
}


public function updateSale(Request $request, BookingSale $sale)
{
    $data = $request->validate([
        'paid_by_id'       => ['nullable', 'integer', 'exists:users,id'],

        'services'               => ['nullable', 'array'],
        'services.*.id'          => ['required_with:services', 'integer'],
        'services.*.staff_id'    => ['nullable', 'integer', 'exists:users,id'],
    ]);

    DB::beginTransaction();

    try {
        $sale->load('booking');
        $booking = $sale->booking;

        if (! $booking) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Booking not found'], 404);
        }

        if (!empty($data['services']) && is_array($data['services'])) {
            foreach ($data['services'] as $row) {
                $svcId = (int) ($row['id'] ?? 0);
                $staffId = $row['staff_id'] ?? null;

                if ($svcId <= 0) continue;

                $booking->services()
                    ->where('id', $svcId)
                    ->update(['staff_id' => $staffId ?: null]);
            }
        }

        if (array_key_exists('paid_by_id', $data)) {
            $newUserId = $data['paid_by_id'] ?: null;

            $booking->placed_by = $newUserId;

            $booking->approved_by = $newUserId;

            $sale->tip_staff_id = $newUserId;
            $sale->save();
        }

        $booking->save();

        DB::commit();

        broadcast(new BookingUpdated($booking))->toOthers();

        return response()->json(['success' => true]);
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error($e);

        return response()->json([
            'success' => false,
            'message' => 'Failed to update sale details',
        ], 500);
    }
}

public function updateNote(Request $request, Booking $booking)
{
    $data = $request->validate([
        'notes' => ['nullable', 'string', 'max:5000'],
    ]);

    $booking->notes = $data['notes'] ?? null;
    $booking->save();

    return response()->json([
        'booking' => $booking->fresh(),
    ]);
}

public function deleteNote(Booking $booking)
{
    $booking->notes = null;
    $booking->save();

    return response()->json([
        'booking' => $booking->fresh(),
    ]);
}
protected function bookingDateTimeParts(Booking $booking): array
{
    $startsAt = $booking->starts_at ? Carbon::parse($booking->starts_at) : null;

    $date = $startsAt
        ? $startsAt->format('Y-m-d')
        : (optional($booking->date)->format('Y-m-d') ?: now()->format('Y-m-d'));

    $time = $startsAt ? $startsAt->format('H:i') : null;

    return [$date, $time];
}

protected function buildBranchName(?Booking $booking): string
{
    if (! $booking) return '';

    // If relation exists, prefer it
    if (method_exists($booking, 'branch') && $booking->relationLoaded('branch') && $booking->branch) {
        return trim((string) $booking->branch->name);
    }

    if (! empty($booking->branch_id)) {
        $b = Branch::find($booking->branch_id);
        return $b ? trim((string) $b->name) : '';
    }

    return '';
}

protected function buildTeamMemberName(Booking $booking): string
{
    if ($booking->relationLoaded('staff') && $booking->staff) {
        return trim((string) $booking->staff->name);
    }

    if (! empty($booking->staff_id)) {
        $u = User::find($booking->staff_id);
        if ($u) return trim((string) $u->name);
    }

    try {
        $svcStaffId = $booking->services()
            ->whereNotNull('staff_id')
            ->value('staff_id');

        if ($svcStaffId) {
            $u = User::find($svcStaffId);
            if ($u) return trim((string) $u->name);
        }
    } catch (\Throwable $e) {
        // ignore
    }

    return '';
}
protected function smsBookingConfirmed(string $clientName, string $branchName, string $date, ?string $time, string $teamMember): string
{
    $branchPhrase = $branchName !== '' ? "Drift Barber {$branchName} branch" : "Drift Barber";
    $timeLine = "Time: " . ($time ?: '-');
    $teamLine = "Team Member: " . (trim($teamMember) !== '' ? trim($teamMember) : '-');

    return trim(<<<SMS
Hi {$clientName},

Great news! Your appointment is confirmed, and we're excited to welcome you at the {$branchPhrase}.

Date: {$date}
{$timeLine}
{$teamLine}

See you soon.
SMS);
}

protected function smsBookingPending(string $clientName, string $branchName, string $date, ?string $time): string
{
    $branchPhrase = $branchName !== '' ? "Drift Barber {$branchName} branch" : "Drift Barber";
    $timeLine = "Time: " . ($time ?: '-');

    return trim(<<<SMS
Hi {$clientName},

We've received your appointment request at the {$branchPhrase} and it's currently pending confirmation.

Date: {$date}
{$timeLine}

Thank you for choosing our service. We'll update you shortly.
SMS);
}

protected function smsPendingClosed(string $clientName, string $date, ?string $time): string
{
    $timeLine = "Time: " . ($time ?: '-');

    return trim(<<<SMS
Hi {$clientName},

Thank you for your patience. Unfortunately, we were unable to confirm your appointment scheduled for:

Date: {$date}
{$timeLine}

The booking has now been closed. Please feel free to make another booking at a time that suits you - we'd be happy to assist.
SMS);
}

protected function smsReminder1Day(string $clientName, string $date, string $time): string
{
    return trim(<<<SMS
Hi {$clientName},

Your appointment is coming up tomorrow, and we're looking forward to seeing you.

Date: {$date}
Time: {$time}

We're all set and ready for you.
SMS);
}

protected function smsReminder1Hour(string $clientName, string $date, string $time): string
{
    return trim(<<<SMS
Hi {$clientName},

Just a heads-up - your appointment starts in 1 hour.

Date: {$date}
Time: {$time}

We'll see you shortly.
SMS);
}

protected function smsSaleCompleted(string $clientName, int $saleId, string $date, ?string $time, string $invoiceUrl): string
{
    $timePart = $time ? " at {$time}" : "";

    return trim(<<<SMS
Hi {$clientName},

Your appointment has been successfully completed, and we hope you had a great experience.

Sale ID: {$saleId}
Appointment: {$date}{$timePart}
Invoice (PDF): {$invoiceUrl}

Thank you for choosing Drift Barber. We look forward to welcoming you again soon.
SMS);
}

protected function smsCancelledLateArrival(string $clientName, int $bookingId): string
{
    return trim(<<<SMS
Hi {$clientName},

Your booking {$bookingId} has been cancelled due to late arrival.

If you'd like to book again, we'd be happy to assist you.
SMS);
}

protected function smsCancelledGeneric(string $clientName, int $bookingId, ?string $reason = null): string
{
    $reasonLine = $reason ? "\nReason: {$reason}" : "";

    return trim(<<<SMS
Hi {$clientName},

Your booking {$bookingId} has been cancelled.{$reasonLine}

If you'd like to book again, we'd be happy to assist you.
SMS);
}

}
