<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Models\Discount;

class DiscountController extends Controller
{
    use ApiResponse;

    public function getDiscounts(Request $request)
    {
        try {
            $discount = Discount::where('status', true)->get();
            return $this->successResponse($discount, 'Discounts retrieved successfully');
        } catch (\Throwable $e) {
            \Log::error('Get Banners Error: ' . $e->getMessage());
            return $this->errorResponse('Server Error', 500);
        }
    }
}
