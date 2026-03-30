<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class CheckoutPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Frontend/shop/checkout/index');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'size:9'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:40'],
            'delivery_note' => ['nullable', 'string', 'max:2000'],
            'shipping_method.code' => ['required', 'string', 'max:50'],
            'shipping_method.name' => ['required', 'string', 'max:255'],
            'shipping_method.fee' => ['required', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.key' => ['nullable', 'string', 'max:255'],
            'items.*.id' => ['nullable'],
            'items.*.productType' => ['nullable', 'string', 'max:50'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.sku' => ['nullable', 'string', 'max:255'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.image' => ['nullable', 'string'],
            'items.*.colorName' => ['nullable', 'string', 'max:255'],
            'items.*.storageLabel' => ['nullable', 'string', 'max:255'],
            'items.*.sizeLabel' => ['nullable', 'string', 'max:255'],
            'items.*.variantLabel' => ['nullable', 'string', 'max:255'],
        ]);

        $subtotal = collect($validated['items'])
            ->sum(fn (array $item) => round(((float) $item['price']) * ((int) $item['quantity']), 2));

        $shippingFee = round((float) data_get($validated, 'shipping_method.fee', 0), 2);
        $grandTotal = round($subtotal + $shippingFee, 2);

        $order = DB::transaction(function () use ($validated, $subtotal, $shippingFee, $grandTotal) {
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone' => '0' . preg_replace('/\D+/', '', $validated['phone']),
                'address_line_1' => $validated['address_line_1'],
                'address_line_2' => $validated['address_line_2'] ?? null,
                'city' => $validated['city'],
                'postal_code' => $validated['postal_code'] ?? null,
                'delivery_note' => $validated['delivery_note'] ?? null,
                'shipping_method_code' => data_get($validated, 'shipping_method.code'),
                'shipping_method_name' => data_get($validated, 'shipping_method.name'),
                'shipping_fee' => $shippingFee,
                'subtotal' => $subtotal,
                'grand_total' => $grandTotal,
                'currency' => 'LKR',
                'status' => 'confirmed',
                'meta' => [
                    'submitted_from' => 'frontend_checkout',
                ],
            ]);

            foreach ($validated['items'] as $item) {
                $quantity = (int) $item['quantity'];
                $unitPrice = round((float) $item['price'], 2);

                $order->items()->create([
                    'item_key' => $item['key'] ?? null,
                    'product_type' => $item['productType'] ?? null,
                    'product_id' => is_numeric($item['id'] ?? null) ? (int) $item['id'] : null,
                    'product_name' => $item['name'],
                    'sku' => $item['sku'] ?? null,
                    'variant_label' => $item['variantLabel'] ?? null,
                    'color_name' => $item['colorName'] ?? null,
                    'storage_label' => $item['storageLabel'] ?? null,
                    'size_label' => $item['sizeLabel'] ?? null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => round($unitPrice * $quantity, 2),
                    'image' => $item['image'] ?? null,
                    'meta' => $item,
                ]);
            }

            return $order->load('items');
        });

        try {
            Mail::to($order->email)->send(new OrderConfirmationMail($order));
            $order->forceFill([
                'email_sent_at' => now(),
            ])->save();
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'message' => 'Order saved, but the confirmation email could not be sent.',
                'order_number' => $order->order_number,
            ], 201);
        }

        return response()->json([
            'message' => 'Order placed successfully.',
            'order_number' => $order->order_number,
        ], 201);
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'FRZ-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
