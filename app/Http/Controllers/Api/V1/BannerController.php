<?php

namespace App\Http\Controllers\Api\V1;

use App\Traits\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;

class BannerController extends Controller
{
    use ApiResponse;

    public function getBanners(Request $request)
    {
        try {
            $banners = Banner::where('status', true)->get();
            return $this->successResponse($banners, 'Banners retrieved successfully');
        } catch (\Throwable $e) {
            \Log::error('Get Banners Error: ' . $e->getMessage());
            return $this->errorResponse('Server Error', 500);
        }
    }
}
