<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\BookingCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Helpers;
use App\Models\Branch;
use App\Models\User;
use App\Notifications\BookingSavedNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;

class BookingsController extends Controller
{
    use ApiResponse;

    public function getBookings(Request $request)
    {
        try {
            $user = $request->user();
            $status = $request->query('status', $request->input('status'));
            $date   = $request->query('starts_at', $request->input('starts_at'));
            $query = Booking::where('client_id', $user->id);
if (! empty($status)) {
    $statuses = array_filter(array_map('trim', explode(',', $status)));

    if (count($statuses) === 1) {
        $single = strtolower($statuses[0]);

        if ($single === 'pending') {
            $query->whereIn('status', ['pending', 'scheduled']);

            if (! empty($date)) {
                $query->where('starts_at', $date);
            }
        } else {
            $query->where('status', $statuses[0]);
        }

    } else {
        $query->whereIn('status', $statuses);
    }
}

            $branchId = Booking::where('client_id', $user->id)->value('branch_id');
            if ($branchId) {
                $branch = Branch::find($branchId);
            }
            $bookings = $query->with(['services.service', 'staff', 'branch'])->get();
            $tz = config('app.timezone');

            $items = $bookings->map(function (Booking $b) use ($tz) {
                return $this->formatBooking($b, $tz);
            })->values();
            return $this->successResponse($items, 'Bookings retrieved successfully');
        } catch (\Throwable $e) {
            Log::info($e);
            return $this->errorResponse('Server Error', 500);
        }
    }

    private function formatBooking(Booking $b, string $tz): array
    {
        $durationMinutes = $b->services->sum(function ($svc) {
            return (int) $svc->duration_minutes + (int) ($svc->extra_minutes ?? 0);
        }) ?: null;

        return [
            'id' => $b->id,
            'status' => (string) $b->status,
            'date' => optional($b->date)->toDateString(),
            'starts_at' => $b->starts_at ? $b->starts_at->setTimezone($tz)->toDateTimeString() : null,
            'ends_at' => $b->ends_at ? $b->ends_at->setTimezone($tz)->toDateTimeString() : null,
            'created_at' => $b->created_at ? $b->created_at->setTimezone($tz)->toDateTimeString() : null,
            'total_price' => (float) ($b->total_price ?? 0),
            'client_name' => $b->client ? trim($b->client->first_name . ' ' . $b->client->last_name) : 'Walk-in',
            'staff_name' => $b->staff_name ?? optional($b->staff)->name,
            'services_count' => $b->services->count() ?: null,
            'notes'=>$b->notes,
            'duration_minutes' => $durationMinutes,
            'services' => $b->services->map(function ($svc) use ($tz) {
                return [
                    'id' => $svc->id,
                    'service_id' => $svc->service_id,
                    'label' => $svc->label,
                    'starts_at' => $svc->starts_at ? $svc->starts_at->setTimezone($tz)->toDateTimeString() : null,
                    'ends_at' => $svc->ends_at ? $svc->ends_at->setTimezone($tz)->toDateTimeString() : null,
                    'duration_minutes' => $svc->duration_minutes,
                    'price' => $svc->price,
                    'final_price' => $svc->final_price,
                ];
            })->values(),
            'branch' => $b->branch ? ['id' => $b->branch->id, 'name' => $b->branch->name] : null,
        ];
    }

    public function createBooking(Request $request)
    {
        Log::info($request->all());

        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'staff_id'  => 'nullable|integer',
            'date'      => 'required|date',
            'starts_at' => 'required|date',
            'ends_at'   => 'required|date',
            'status'    => 'nullable|string',
            'services'  => 'required|array|min:1',
            'services.*.service_id'        => 'required|exists:services,id',
            'services.*.service_variant_id' => 'nullable|exists:service_variants,id',
            'services.*.label'             => 'required|string',
            'services.*.duration_minutes'  => 'required|integer',
            'services.*.extra_minutes'     => 'nullable|integer',
            'services.*.starts_at'         => 'required|date',
            'services.*.ends_at'           => 'required|date',
            'services.*.price'             => 'required|numeric',
            'services.*.discount_type'     => 'nullable|string',
            'services.*.discount_value'    => 'nullable|numeric',
            'services.*.final_price'       => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 500);
        }

        DB::beginTransaction();

        try {
            $data = $request->all();

            $total = collect($data['services'])->sum(fn($svc) => $svc['final_price']);

            $slotStart = Carbon::parse($data['starts_at']);
            $slotEnd   = Carbon::parse($data['ends_at']);

            $booking = Booking::create([
                'client_id'   => $data['client_id'],
                'branch_id'   => $data['branch_id'] ?? null,
                'staff_id'    => $data['staff_id'] ?? null,
                'date'        => $slotStart->toDateString(),
                'starts_at'   => $slotStart,
                'ends_at'     => $slotEnd,
                'total_price' => $total,
                'status'      => 'pending',
                'notes'       => $data['notes'] ?? null,
            ]);

            $servicesPayload = [];
            $previousEnd = null;

            foreach ($data['services'] as $index => $svc) {
                if ($index === 0) {
                    $start = Carbon::parse($svc['starts_at']);
                } else {
                    $start = $previousEnd;
                }
                $end = (clone $start)->addMinutes(
                    (int) $svc['duration_minutes'] + (int) ($svc['extra_minutes'] ?? 0)
                );
                $servicesPayload[] = [
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
                    'final_price'        => (float) $svc['final_price'],
                    'color_code'         => $svc['color_code'] ?? null,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ];
                $previousEnd = $end;
            }

            $booking->services()->createMany($servicesPayload);

            DB::commit();

            $superAdmins = User::role('Super Admin')->get();
            Notification::send($superAdmins, new BookingSavedNotification($booking));

            broadcast(new BookingCreated($booking))->toOthers();

            $date     = $booking->starts_at ? $booking->starts_at->format('Y-m-d') : optional($booking->date)->format('Y-m-d');
            $time     = $booking->starts_at ? $booking->starts_at->format('H:i') : null;

            $message = "Your appointment on " . $date . " at " . $time . " has been received. We'll notify you once it's accepted.";
            $user = $request->user();

            Helpers::sendSMSAlerts($message, $user->phone);

            return $this->successResponse($booking, 'Bookings created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            return $this->errorResponse('Server Error', 500);
        }
    }

    public function updateBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:bookings,id',
            'status' => 'required|string|in:scheduled,cancelled,completed',
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 500);
        }
        try {
            $data = $request->all();
            $booking = Booking::find($data['id']);
            $booking->status = $data['status'];
            $booking->save();

            if ($data['status'] === 'cancelled') {
                $user = $request->user();
                $message = 'Dear ' . $user->first_name . ", Your appointment scheduled for " . $booking->starts_at . " has been cancelled. Please contact us for more information.";
                Helpers::sendSMSAlerts($message, $user->phone);
            }

            return $this->successResponse($booking, 'Booking status updated successfully');
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorResponse('Server Error', 500);
        }
    }
}
