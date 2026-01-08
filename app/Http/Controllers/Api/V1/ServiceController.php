<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Models\Service;
use App\Models\Branch;
use App\Models\User;
use App\Models\ServiceCategory;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;


class ServiceController extends Controller
{
    use ApiResponse;

    public function getStaffWithServicesWithCategories(Request $request)
    {
        try {
            $branch_id = $request->branch_id;
            $branch = Branch::find($branch_id);

            if (! $branch) {
                return $this->errorResponse('Branch not found', 404);
            }

            $staff_ids = $branch->user()->pluck('user_id');

            $staff = User::whereIn('id', $staff_ids)
                ->orderBy('sort_order')
                ->with([
                    'services' => function ($q) {
                        $q->where('status', 'active')->with('category');
                    }
                ])
                ->get()
                ->filter(function ($u) {
                    return $u->hasPermissionTo('calendar.staff');
                })
                ->values();

            return $this->successResponse($staff, 'Staff with Service with categories retrieved successfully');
        } catch (\Throwable $e) {
            Log::error('Get Service Categories Error: ' . $e->getMessage());
            return $this->errorResponse('Server Error', 500);
        }
    }

    public function getServiceCategories(Request $request)
    {
        try {
            $services = ServiceCategory::with('media')->where('status', 'active')->orderBy('sort_order')->get();

            return $this->successResponse($services, 'Service categories retrieved successfully');
        } catch (\Throwable $e) {
            Log::error('Get Service Categories Error: ' . $e->getMessage());
            return $this->errorResponse('Server Error', 500);
        }
    }
}
