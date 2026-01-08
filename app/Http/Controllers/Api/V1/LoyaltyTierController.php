<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\LoyaltyTier;
use Illuminate\Support\Facades\Log;

class LoyaltyTierController extends Controller
{

    use ApiResponse;

    public function fetchTiers(Request $request){
        try{
            $tiers = LoyaltyTier::with('media')->get();

            return $this->successResponse($tiers, 'Tiers retrieved successfully');
        }catch(\Throwable $e){
            Log::alert($e);
            return $this->errorResponse('Server Error', 500);
        }

    }
}