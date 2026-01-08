<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingService;
use App\Models\Branch;
use App\Models\Client;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $users = User::all();
        // dd($users);
        $auth_user = Auth::user();
        // dd($auth_user);
        if ($auth_user->roles[0]->dashboard_type == 'super-admin') {

            // ✅ FIX: Count ALL bookings happening today (all statuses), using starts_at
            $today_bookings = Booking::whereDate('starts_at', today())->count();

            $locations = Branch::count();
            $customers = Client::count();
            $employees = User::count();
            // dd($today_bookings);

            $weekly = BookingService::selectRaw('DAYNAME(starts_at) AS day, COUNT(DISTINCT booking_id) AS total')
                ->where('starts_at', '>=', now()->subDays(6))
                ->groupBy('day')
                ->orderByRaw("FIELD(day, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')")
                ->get();

            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

            $weekLabels = $days;

            $weekData = collect($days)->map(
                fn($day) =>
                $weekly->firstWhere('day', $day)->total ?? 0
            );

            $baseQuery = Booking::whereHas('services');

            // Booking status counts
            $scheduledCount = (clone $baseQuery)->where('status', 'scheduled')->count();
            $completedCount = (clone $baseQuery)->where('status', 'completed')->count();
            $noShowCount    = (clone $baseQuery)->where('status', 'no_show')->count();
            $cancelledCount = (clone $baseQuery)->where('status', 'cancelled')->count();

            $latestBookings = Booking::whereHas('services')
                ->with(['services', 'client'])
                ->orderBy('starts_at', 'DESC')
                ->limit(5)
                ->get();


            return Inertia::render('Dashboard/Index', [
                'today_bookings' => $today_bookings,
                'locations' => $locations,
                'customers' => $customers,
                'employees' => $employees,
                'week_labels' => $weekLabels,
                'week_data' => $weekData,
                'status_labels' => ['Scheduled', 'Completed', 'No Show', 'Cancelled'],
                'status_data'   => [
                    $scheduledCount,
                    $completedCount,
                    $noShowCount,
                    $cancelledCount
                ],
                'latest_bookings' => $latestBookings,
            ]);

        } else if ($auth_user->roles[0]->dashboard_type == 'system-admin') {

            // ✅ FIX: Return the same props so KPI cards can show values
            $today_bookings = Booking::whereDate('starts_at', today())->count();
            $locations = Branch::count();
            $customers = Client::count();
            $employees = User::count();

            $weekly = BookingService::selectRaw('DAYNAME(starts_at) AS day, COUNT(DISTINCT booking_id) AS total')
                ->where('starts_at', '>=', now()->subDays(6))
                ->groupBy('day')
                ->orderByRaw("FIELD(day, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')")
                ->get();

            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            $weekLabels = $days;

            $weekData = collect($days)->map(
                fn($day) =>
                $weekly->firstWhere('day', $day)->total ?? 0
            );

            $baseQuery = Booking::whereHas('services');

            $scheduledCount = (clone $baseQuery)->where('status', 'scheduled')->count();
            $completedCount = (clone $baseQuery)->where('status', 'completed')->count();
            $noShowCount    = (clone $baseQuery)->where('status', 'no_show')->count();
            $cancelledCount = (clone $baseQuery)->where('status', 'cancelled')->count();

            $latestBookings = Booking::whereHas('services')
                ->with(['services', 'client'])
                ->orderBy('starts_at', 'DESC')
                ->limit(5)
                ->get();

            return Inertia::render('Dashboard/Index', [
                'today_bookings' => $today_bookings,
                'locations' => $locations,
                'customers' => $customers,
                'employees' => $employees,
                'week_labels' => $weekLabels,
                'week_data' => $weekData,
                'status_labels' => ['Scheduled', 'Completed', 'No Show', 'Cancelled'],
                'status_data'   => [
                    $scheduledCount,
                    $completedCount,
                    $noShowCount,
                    $cancelledCount
                ],
                'latest_bookings' => $latestBookings,
            ]);

        } else if ($auth_user->roles[0]->dashboard_type == 'owner') {

            // ✅ FIX: Return the same props so KPI cards can show values
            $today_bookings = Booking::whereDate('starts_at', today())->count();
            $locations = Branch::count();
            $customers = Client::count();
            $employees = User::count();

            $weekly = BookingService::selectRaw('DAYNAME(starts_at) AS day, COUNT(DISTINCT booking_id) AS total')
                ->where('starts_at', '>=', now()->subDays(6))
                ->groupBy('day')
                ->orderByRaw("FIELD(day, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')")
                ->get();

            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            $weekLabels = $days;

            $weekData = collect($days)->map(
                fn($day) =>
                $weekly->firstWhere('day', $day)->total ?? 0
            );

            $baseQuery = Booking::whereHas('services');

            $scheduledCount = (clone $baseQuery)->where('status', 'scheduled')->count();
            $completedCount = (clone $baseQuery)->where('status', 'completed')->count();
            $noShowCount    = (clone $baseQuery)->where('status', 'no_show')->count();
            $cancelledCount = (clone $baseQuery)->where('status', 'cancelled')->count();

            $latestBookings = Booking::whereHas('services')
                ->with(['services', 'client'])
                ->orderBy('starts_at', 'DESC')
                ->limit(5)
                ->get();

            return Inertia::render('Dashboard/Index', [
                'today_bookings' => $today_bookings,
                'locations' => $locations,
                'customers' => $customers,
                'employees' => $employees,
                'week_labels' => $weekLabels,
                'week_data' => $weekData,
                'status_labels' => ['Scheduled', 'Completed', 'No Show', 'Cancelled'],
                'status_data'   => [
                    $scheduledCount,
                    $completedCount,
                    $noShowCount,
                    $cancelledCount
                ],
                'latest_bookings' => $latestBookings,
            ]);

        } else if ($auth_user->roles[0]->dashboard_type == 'manager') {

            // ✅ FIX: Return the same props so KPI cards can show values
            $today_bookings = Booking::whereDate('starts_at', today())->count();
            $locations = Branch::count();
            $customers = Client::count();
            $employees = User::count();

            $weekly = BookingService::selectRaw('DAYNAME(starts_at) AS day, COUNT(DISTINCT booking_id) AS total')
                ->where('starts_at', '>=', now()->subDays(6))
                ->groupBy('day')
                ->orderByRaw("FIELD(day, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')")
                ->get();

            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            $weekLabels = $days;

            $weekData = collect($days)->map(
                fn($day) =>
                $weekly->firstWhere('day', $day)->total ?? 0
            );

            $baseQuery = Booking::whereHas('services');

            $scheduledCount = (clone $baseQuery)->where('status', 'scheduled')->count();
            $completedCount = (clone $baseQuery)->where('status', 'completed')->count();
            $noShowCount    = (clone $baseQuery)->where('status', 'no_show')->count();
            $cancelledCount = (clone $baseQuery)->where('status', 'cancelled')->count();

            $latestBookings = Booking::whereHas('services')
                ->with(['services', 'client'])
                ->orderBy('starts_at', 'DESC')
                ->limit(5)
                ->get();

            return Inertia::render('Dashboard/Index', [
                'today_bookings' => $today_bookings,
                'locations' => $locations,
                'customers' => $customers,
                'employees' => $employees,
                'week_labels' => $weekLabels,
                'week_data' => $weekData,
                'status_labels' => ['Scheduled', 'Completed', 'No Show', 'Cancelled'],
                'status_data'   => [
                    $scheduledCount,
                    $completedCount,
                    $noShowCount,
                    $cancelledCount
                ],
                'latest_bookings' => $latestBookings,
            ]);

        } else if ($auth_user->roles[0]->dashboard_type == 'staff') {

            $staffId = auth()->id();

            // Staff-specific today bookings
            // ✅ FIX: Count ALL bookings happening today for this staff (all statuses), using starts_at
            $today_bookings = Booking::whereDate('starts_at', today())
                ->whereHas('services', function ($q) use ($staffId) {
                    $q->where('staff_id', $staffId);
                })
                ->with(['services' => function ($q) use ($staffId) {
                    $q->where('staff_id', $staffId);
                }])
                ->count();

            // Stats
            $locations = Branch::count();
            $customers = Client::count();
            $employees = User::count();


            $weekly = BookingService::selectRaw('DAYNAME(starts_at) AS day, COUNT(DISTINCT booking_id) AS total')
                ->where('staff_id', $staffId)
                ->where('starts_at', '>=', now()->subDays(6))
                ->groupBy('day')
                ->orderByRaw("FIELD(day, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')")
                ->get();

            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

            $weekLabels = $days;

            $weekData = collect($days)->map(
                fn($day) =>
                $weekly->firstWhere('day', $day)->total ?? 0
            );
            $baseQuery = Booking::whereHas('services', function ($q) use ($staffId) {
                $q->where('staff_id', $staffId);
            });

            // Booking status counts
            $scheduledCount = (clone $baseQuery)->where('status', 'scheduled')->count();
            $completedCount = (clone $baseQuery)->where('status', 'completed')->count();
            $noShowCount    = (clone $baseQuery)->where('status', 'no_show')->count();
            $cancelledCount = (clone $baseQuery)->where('status', 'cancelled')->count();

            $latestBookings = Booking::whereHas('services', function ($q) use ($staffId) {
                $q->where('staff_id', $staffId);
            })
                ->with(['services' => function ($q) use ($staffId) {
                    $q->where('staff_id', $staffId);
                }, 'client'])
                ->orderBy('starts_at', 'DESC')
                ->limit(5)
                ->get();

            return Inertia::render('StaffDashboard/Index', [
                'today_bookings' => $today_bookings,
                'locations' => $locations,
                'customers' => $customers,
                'employees' => $employees,
                'week_labels' => $weekLabels,
                'week_data' => $weekData,
                'status_labels' => ['Scheduled', 'Completed', 'No Show', 'Cancelled'],
                'status_data'   => [
                    $scheduledCount,
                    $completedCount,
                    $noShowCount,
                    $cancelledCount
                ],
                'latest_bookings' => $latestBookings,
            ]);
        } else {
            return Inertia::render('Dashboard/Index');
        }
    }
}
