<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;


class BranchController extends Controller
{
    use ApiResponse;

    public function getBranches(Request $request)
    {
        try {
            $branches = Branch::with('media')->where('status', true)->get();
            return $this->successResponse($branches, 'Branches retrieved successfully');
        } catch (\Throwable $e) {
            Log::error('Get Branches Error: ' . $e->getMessage());
            return $this->errorResponse([], 'Server Error', 500);
        }
    }

    public function getBranchTeam(Request $request){
        try{
            $branch_id = $request->branch_id;
            $staffIds = Branch::find($branch_id)->user()->pluck('user_id');
            $staff = User::whereIn('id',$staffIds)->get();

            return $this->successResponse($staff,'Staff retrieved successfully');
        } catch (\Throwable $e) {
            Log::error('Get Branch staff Error: ' . $e->getMessage());
            return $this->errorResponse([], 'Server Error', 500);
        }
    }

    public function getGalleryImage(Request $request){
        try{
            $branch_id = $request->branch_id;

        }catch(\Throwable $e){
            Log::error('Gallery Images Retrieving Error: ' . $e->getMessage());
            return $this->errorResponse([], 'Server Error', 500);
        }
    }
}
