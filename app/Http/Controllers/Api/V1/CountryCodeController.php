<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Models\Country;

class CountryCodeController extends Controller
{
    use ApiResponse;

    public function getCountryCodes(Request $request)
    {
        $countryCodes = Country::where('status', true)->get();

        return $this->successResponse($countryCodes, 'Country codes retrieved successfully');
    }
}
