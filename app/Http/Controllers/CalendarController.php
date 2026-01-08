<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingService;
use App\Models\Client;
use App\Models\Service;
use App\Models\User;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Country;
use Illuminate\Support\Facades\Auth;


class CalendarController extends Controller
{
   public function index(Request $request)
{
    $user = Auth::user();
$isSuperAdmin = $user && $user->hasRole('Super Admin');

$canViewCalendar  = $user && $user->hasPermissionTo('calendar.view');
    $isCalendarStaff  = $user && $user->hasPermissionTo('calendar.staff');

     $isViewOnlyUser   = $canViewCalendar && !$isCalendarStaff;
       $canSeeAllBookings = $isSuperAdmin || $isViewOnlyUser;

    $dateString = $request->query('date');
    $branchId   = $request->query('branch_id');
      $bookingId  = $request->query('booking_id');

    if (!$dateString || !$branchId) {
        if (!$dateString) {
            $dateString = now()->toDateString();
        }

        if (!$branchId) {
            $branchId = Branch::where('status', 1)
                ->orderBy('name')
                ->value('id');
        }

        return redirect()->route('calendar.index', [
            'date'      => $dateString,
            'branch_id' => $branchId,
            'booking_id' => $bookingId,
        ]);
    }

    $date = Carbon::createFromFormat('Y-m-d', $dateString)->startOfDay();

    $bookingId = $request->query('booking_id');
    $booking   = null;

    if ($bookingId) {
        $booking = Booking::with([
            'client',
            'staff',
            'services.service',
        ])->find($bookingId);
    }

       $branches = Branch::where('status', 1)
        ->orderBy('name')
        ->get(['id', 'name', 'opening_hours']);

    $staffQuery = User::with('media')->orderBy('sort_order');

    if ($branchId) {
        $staffQuery->whereHas('branches', function ($q) use ($branchId) {
            $q->where('branches.id', $branchId);
        });
    }

    if ($user) {
        if ($canSeeAllBookings) {
            // manager / view-only / super admin: see all staff (no extra filter)
        } else {
            // normal staff: only themselves
            $staffQuery->where('id', $user->id);
        }
    }

    $staff = $staffQuery->get()
        ->filter(function ($u) {
            // only users that can actually be booked
            return $u->hasPermissionTo('calendar.staff');
        })
        ->map(function ($u) {
            $mediaAvatar = $u->getFirstMedia('avatar');

            if ($mediaAvatar) {
                $avatar = $mediaAvatar->getFullUrl();
            } elseif (!empty($u->profile_photo_path)) {
                $avatar = asset('storage/' . ltrim($u->profile_photo_path, '/'));
            } else {
                $avatar = 'https://www.gravatar.com/avatar/' .
                    md5(strtolower(trim($u->email))) .
                    '?s=96&d=identicon';
            }

            return [
                'id'     => $u->id,
                'name'   => $u->name,
                'avatar' => $avatar,
            ];
        })
        ->values();


    // $services = Service::with([
    //     'category:id,name,color_code',
    //     'variants:id,service_id,name,description,price_type,price,duration_minutes',
    //     'user:id,name',
    //     'discounts' => function ($q) {
    //         $q->select(
    //             'discounts.id',
    //             'discounts.name',
    //             'discounts.discount_type',
    //             'discounts.discount_amount',
    //             'discounts.priority',
    //             'discounts.starts_at',
    //             'discounts.ends_at',
    //             'discounts.status'
    //         );
    //     },
    // ])
    //     ->where('status', 'active')
    //     ->whereHas('category', function ($q) {
    //         $q->where('status', 'active');
    //     })
    //     ->orderBy('service_category_id')
    //     ->orderBy('sort_order')
    //     ->orderBy('name')
    //     ->get();

    $clients = Client::orderBy('first_name')
        ->orderBy('last_name')
        ->limit(100)
        ->get()
        ->map(function ($c) {
            $name = trim(($c->first_name ?? '') . ' ' . ($c->last_name ?? ''));
            $phone = trim(
                ($c->phone_code ? '+' . ltrim($c->phone_code, '+') . ' ' : '') .
                ($c->phone ?? '')
            );

            return [
                'id'    => $c->id,
                'name'  => $name ?: '—',
                'email' => $c->email,
                'phone' => $phone ?: null,
            ];
        })
        ->values();

 $serviceSlotsQuery = BookingService::with([
    'booking.client.media',
    'service.category',
])
->whereDate('starts_at', $date->toDateString());
 if ($user && !$canSeeAllBookings) {
        $serviceSlotsQuery->where(function ($q) use ($user) {
            $q->where('staff_id', $user->id)
              ->orWhereHas('booking', function ($qb) use ($user) {
                  $qb->where('staff_id', $user->id);
              });
        });
    }

$serviceSlots = $serviceSlotsQuery
    ->get()
    ->map(function (BookingService $row) {
        if ($row->status === 'blocked_time') {
            return [
                'id'          => $row->id,
                'booking_id'  => $row->booking_id,
                'staff_id'    => $row->staff_id,
                'client_name' => 'Blocked Time',

                'label'       => $row->block_type === 'lunch'
                    ? 'Lunch Break'
                    : ($row->label ?: 'Blocked Time'),

                'starts_at'   => optional($row->starts_at)->toIso8601String(),
                'ends_at'     => optional($row->ends_at)->toIso8601String(),

                'color_code'  => '#4a5568',
                'status'      => 'blocked_time',
                'block_type'  => $row->block_type,
            ];
        }

        $booking = $row->booking;
        $client  = $booking?->client;
        
$clientAvatarUrl = null;
if ($client) {
    $clientAvatarUrl = $client->getFirstMediaUrl('avatar') ?: null;
}

        if ($client) {
            $clientName = trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? ''));
            if ($clientName === '') {
                $clientName = $client->email ?: 'Walk-in';
            }
        } else {
            $clientName = 'Walk-in';
        }

        $service  = $row->service;
        $category = $service?->category;

        $status      = $row->status ?: optional($booking)->status;
        $bookingStatus = strtolower((string) optional($booking)->status);

$paymentStatus = null;
if (in_array($bookingStatus, ['completed', 'done'], true)) {
    $paymentStatus = 'fully_paid';
} elseif ($bookingStatus === 'payment_pending') {
    $paymentStatus = 'part_paid';
}
        $basePrice   = (float) $row->price;
        $finalPrice  = (float) ($row->final_price ?? $basePrice);

        return [
            'id'           => $row->id,
            'booking_id'   => $row->booking_id,
            'staff_id'     => $row->staff_id ?: optional($booking)->staff_id,
            'client_name'  => $clientName,
            'label'        => $row->label ?: ($service?->name ?? 'Service'),
            'starts_at'    => optional($row->starts_at)->toIso8601String(),
            'ends_at'      => optional($row->ends_at)->toIso8601String(),
            'color_code'   => $category?->color_code ?? '#f97316',
            'status'       => $status,
            'block_type'   => $row->block_type,
            'price'        => $basePrice,
            'total'        => $finalPrice,
            'final_price'  => $finalPrice,
             'client_avatar_url' => $clientAvatarUrl,
             'payment_status' => $paymentStatus,

        ];
    })
    ->values();


   $blockedQuery = Booking::where('status', 'blocked_time')
    ->whereDate('starts_at', $date->toDateString());

if ($user && !$canSeeAllBookings) {
        $blockedQuery->where('staff_id', $user->id);
    }

$blockedSlots = $blockedQuery
    ->get()
    ->map(function (Booking $row) {
        return [
            'id'          => $row->id,
            'booking_id'  => $row->id,
            'staff_id'    => $row->staff_id,
            'client_name' => 'Blocked Time',

            'label'       => $row->block_type === 'lunch'
                ? 'Lunch Break'
                : ($row->description ?: 'Blocked Time'),

            'starts_at'   => optional($row->starts_at)->toIso8601String(),
            'ends_at'     => optional($row->ends_at)->toIso8601String(),

            'color_code'  => '#4a5568',
            'status'      => 'blocked_time',
            'block_type'  => $row->block_type,
        ];
    })
    ->values();


    $events = $serviceSlots->concat($blockedSlots)->values();

    $countries = Country::orderBy('name')->get(['id', 'name', 'code']);

    return Inertia::render('Calendar/Index', [
        'date'             => $dateString,
        'step'             => 15,
        'staff'            => $staff,
        // 'services'         => $services,
        'clients'          => $clients,
        'events'           => $events,
        'currency_symbol'  => 'LKR',
        'branches'         => $branches,
        'selectedBranchId' => $branchId,
        'booking'          => $booking,
        'countries'        => $countries,
    ]);
}
public function servicesdata(Request $request)
{
 

    $services = Service::with([
        'category:id,name,color_code',
        'variants:id,service_id,name,description,price_type,price,duration_minutes',
        'user:id,name',
        'discounts' => function ($q) {
            $q->select(
                'discounts.id',
                'discounts.name',
                'discounts.discount_type',
                'discounts.discount_amount',
                'discounts.priority',
                'discounts.starts_at',
                'discounts.ends_at',
                'discounts.status'
            );
        },
    ])
        ->where('status', 'active')
        ->whereHas('category', function ($q) {
            $q->where('status', 'active');
        })
        ->orderBy('service_category_id')
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();


    return response()->json([
        'services' => $services,
    ]);
}

}
