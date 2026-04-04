<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\InvoiceOrderStatusMail;
use App\Mail\NewOrderAdminNotificationMail;
use App\Models\Invoice;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
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
        $normalizedPhone = '0' . preg_replace('/\D+/', '', $validated['phone']);

        $result = DB::transaction(function () use ($validated, $subtotal, $shippingFee, $grandTotal, $normalizedPhone) {
            $invoiceNo = $this->nextInvoiceNumberForUpdate();

            $order = Order::create([
                'order_number' => $invoiceNo,
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone' => $normalizedPhone,
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
                    'invoice_no' => $invoiceNo,
                ],
            ]);

            $invoiceItemsPayload = [];

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

                $invoiceItemsPayload[] = $this->mapCheckoutItemToInvoiceItem($item);
            }

            $invoice = $this->createInvoiceFromWebOrder(
                validated: $validated,
                invoiceNo: $invoiceNo,
                phone: $normalizedPhone,
                itemsPayload: $invoiceItemsPayload,
                shippingFee: $shippingFee
            );

            foreach ($invoiceItemsPayload as $index => $itemPayload) {
                $invoice->items()->create([
                    ...$itemPayload,
                    'item_no' => $index + 1,
                ]);
            }

            try {
                $pdfPath = $this->generateAndStoreInvoicePdf($invoice->fresh('items'));
                $invoice->update(['pdf_path' => $pdfPath]);
            } catch (Throwable $throwable) {
                report($throwable);
            }

            $order->forceFill([
                'meta' => array_merge($order->meta ?? [], [
                    'invoice_id' => $invoice->id,
                    'invoice_no' => $invoice->invoice_no,
                    'invoice_pdf_path' => $invoice->pdf_path,
                ]),
            ])->save();

            return [
                'order' => $order->fresh('items'),
                'invoice' => $invoice->fresh('items'),
            ];
        });

        /** @var \App\Models\Order $order */
        $order = $result['order'];

        /** @var \App\Models\Invoice $invoice */
        $invoice = $result['invoice'];

        $customerMailSent = false;
        $adminMailSent = false;
        $adminNotificationEmail = env('ORDER_NOTIFICATION_EMAIL', config('mail.from.address'));

        try {
            if (!empty($invoice->customer_email)) {
                $customerMail = new InvoiceOrderStatusMail($invoice, 'confirmed');

                if ($invoice->pdf_path && Storage::disk('public')->exists($invoice->pdf_path)) {
                    $customerMail->attach(Storage::disk('public')->path($invoice->pdf_path), [
                        'as' => $invoice->invoice_no . '.pdf',
                        'mime' => 'application/pdf',
                    ]);
                }

                Mail::to($invoice->customer_email)->send($customerMail);
                $customerMailSent = true;
            }
        } catch (Throwable $throwable) {
            report($throwable);
        }

        try {
            if (!empty($adminNotificationEmail)) {
                $adminMail = new NewOrderAdminNotificationMail($order);

                if ($invoice->pdf_path && Storage::disk('public')->exists($invoice->pdf_path)) {
                    $adminMail->attach(Storage::disk('public')->path($invoice->pdf_path), [
                        'as' => $invoice->invoice_no . '.pdf',
                        'mime' => 'application/pdf',
                    ]);
                }

                Mail::to($adminNotificationEmail)->send($adminMail);
                $adminMailSent = true;
            }
        } catch (Throwable $throwable) {
            report($throwable);
        }

        $order->forceFill([
            'email_sent_at' => $customerMailSent ? now() : null,
            'admin_email_sent_at' => $adminMailSent ? now() : null,
        ])->save();

        if (!$customerMailSent && !$adminMailSent) {
            return response()->json([
                'message' => 'Order and invoice saved, but both emails could not be sent.',
                'order_number' => $order->order_number,
                'invoice_no' => $invoice->invoice_no,
            ], 201);
        }

        if (!$customerMailSent) {
            return response()->json([
                'message' => 'Order and invoice saved. Admin email sent, but customer email could not be sent.',
                'order_number' => $order->order_number,
                'invoice_no' => $invoice->invoice_no,
            ], 201);
        }

        if (!$adminMailSent) {
            return response()->json([
                'message' => 'Order and invoice saved. Customer email sent, but admin order email could not be sent.',
                'order_number' => $order->order_number,
                'invoice_no' => $invoice->invoice_no,
            ], 201);
        }

        return response()->json([
            'message' => 'Order placed successfully.',
            'order_number' => $order->order_number,
            'invoice_no' => $invoice->invoice_no,
        ], 201);
    }

    private function createInvoiceFromWebOrder(
        array $validated,
        string $invoiceNo,
        string $phone,
        array $itemsPayload,
        float $shippingFee
    ): Invoice {
        [$deliveryEnabled, $deliveryMethod, $deliveryPaymentStatus, $shipVia] = $this->resolveInvoiceDeliveryFields($validated);

        $deliveryAmount = $deliveryEnabled ? $shippingFee : 0;
        $totals = $this->calculateInvoiceTotals($itemsPayload, $deliveryAmount);

        $customerAddress = collect([
            $validated['address_line_1'] ?? null,
            $validated['address_line_2'] ?? null,
            $validated['city'] ?? null,
            $validated['postal_code'] ?? null,
        ])->filter(fn ($value) => filled($value))->implode(', ');

        $notes = collect([
            'Generated automatically from website checkout.',
            !empty($validated['delivery_note']) ? 'Delivery Note: ' . $validated['delivery_note'] : null,
        ])->filter()->implode(PHP_EOL);

        return Invoice::create([
            'invoice_no' => $invoiceNo,
            'invoice_date' => now()->toDateString(),
            'customer_name' => $validated['full_name'],
            'customer_contact_number' => $phone,
            'customer_address' => $customerAddress ?: null,
            'customer_email' => $validated['email'] ?? null,
            'sales_person' => 'Website',
            'ship_date' => null,
            'ship_via' => $shipVia,
            'delivery_enabled' => $deliveryEnabled,
            'delivery_method' => $deliveryEnabled ? $deliveryMethod : null,
            'delivery_payment_status' => $deliveryEnabled ? $deliveryPaymentStatus : null,
            'tracking_id' => null,
            'delivery_agent' => null,
            'delivery_amount' => $deliveryEnabled ? $totals['delivery_amount'] : null,
            'payment_type' => 'unpaid',
            'cash_paid' => 0,
            'card_paid' => 0,
            'advance_amount' => 0,
            'paid_amount' => 0,
            'subtotal' => $totals['subtotal'],
            'total_discount' => $totals['total_discount'],
            'tax_amount' => 0,
            'grand_total' => $totals['grand_total'],
            'balance_due' => $totals['balance_due'],
            'notes' => $notes ?: null,
            'terms' => 'Auto-created from web order. Remaining invoice details can be updated from admin.',
            'status' => 'draft',
            'order_status' => 'confirmed',
        ]);
    }

    private function mapCheckoutItemToInvoiceItem(array $item): array
    {
        $qty = max(1, (int) ($item['quantity'] ?? 1));
        $unitPrice = round((float) ($item['price'] ?? 0), 2);

        $productType = in_array(($item['productType'] ?? null), ['tech', 'shoe'], true)
            ? $item['productType']
            : 'tech';

        return [
            'product_type' => $productType,
            'product_id' => is_numeric($item['id'] ?? null) ? (int) $item['id'] : null,
            'model_name' => $item['name'] ?? null,
            'storage' => $item['storageLabel'] ?? null,
            'color' => $item['colorName'] ?? null,
            'size' => $item['sizeLabel'] ?? null,
            'imei_serial' => null,
            'warranty' => null,
            'is_preorder' => false,
            'description' => $this->buildInvoiceItemDescription($item),
            'qty' => $qty,
            'regular_price' => $unitPrice,
            'discount_type' => null,
            'discount_value' => null,
            'discount_percent_display' => null,
            'discounted_unit_price' => $unitPrice,
            'line_total' => round($unitPrice * $qty, 2),
        ];
    }

    private function buildInvoiceItemDescription(array $item): string
    {
        $parts = array_filter([
            $item['name'] ?? null,
            $item['variantLabel'] ?? null,
            $item['colorName'] ?? null,
            $item['storageLabel'] ?? null,
            $item['sizeLabel'] ?? null,
            !empty($item['sku']) ? 'SKU: ' . $item['sku'] : null,
        ]);

        return implode(' / ', $parts);
    }

    private function resolveInvoiceDeliveryFields(array $validated): array
    {
        $shippingCode = (string) data_get($validated, 'shipping_method.code', '');
        $shippingName = (string) data_get($validated, 'shipping_method.name', '');

        if ($shippingCode === 'store_pickup') {
            return [false, null, null, $shippingName ?: 'Store Pickup'];
        }

        if ($shippingCode === 'cash_on_delivery') {
            return [true, 'cash_on_delivery', 'non_paid', $shippingName ?: 'Cash on Delivery'];
        }

        return [true, 'paid_delivery', 'paid', $shippingName ?: 'Delivery'];
    }

    private function calculateInvoiceTotals(array $items, float $deliveryAmount = 0): array
    {
        $subtotal = 0;
        $grandTotalBeforeTax = 0;

        foreach ($items as $item) {
            $regularPrice = (float) ($item['regular_price'] ?? 0);
            $qty = max(1, (int) ($item['qty'] ?? 1));
            $lineTotal = (float) ($item['line_total'] ?? 0);

            $subtotal += ($regularPrice * $qty);
            $grandTotalBeforeTax += $lineTotal;
        }

        $deliveryAmount = round(max(0, $deliveryAmount), 2);
        $subtotal = round($subtotal + $deliveryAmount, 2);
        $grandTotalBeforeTax = round($grandTotalBeforeTax + $deliveryAmount, 2);
        $grandTotal = $grandTotalBeforeTax;
        $paidAmount = 0;
        $balanceDue = $grandTotal;

        return [
            'subtotal' => $subtotal,
            'total_discount' => round($subtotal - $grandTotalBeforeTax, 2),
            'tax_amount' => 0,
            'grand_total' => $grandTotal,
            'cash_paid' => 0,
            'card_paid' => 0,
            'advance_amount' => 0,
            'paid_amount' => $paidAmount,
            'balance_due' => $balanceDue,
            'delivery_amount' => $deliveryAmount,
        ];
    }

    private function nextInvoiceNumberForUpdate(): string
    {
        $latestInvoiceNo = optional(
            Invoice::query()
                ->select('id', 'invoice_no')
                ->lockForUpdate()
                ->latest('id')
                ->first()
        )->invoice_no;

        return $this->formatNextInvoiceNumber($latestInvoiceNo);
    }

    private function formatNextInvoiceNumber(?string $latestInvoiceNo): string
    {
        if (!$latestInvoiceNo) {
            return 'INV-001';
        }

        if (preg_match('/(\d+)$/', $latestInvoiceNo, $matches)) {
            $next = ((int) $matches[1]) + 1;

            return 'INV-' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
        }

        return 'INV-001';
    }

    private function generateAndStoreInvoicePdf(Invoice $invoice): string
    {
        $invoice->loadMissing('items');

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'shop' => $this->shopInfo(),
        ])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'dpi' => 120,
            ]);

        $path = 'invoices/' . $invoice->invoice_no . '-' . now()->format('YmdHis') . '.pdf';

        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    private function shopInfo(): array
    {
        $logoFile = public_path('assets/images/froziohub-logo.png');
        $logoBase64 = null;

        if (file_exists($logoFile)) {
            $extension = strtolower(pathinfo($logoFile, PATHINFO_EXTENSION));

            $mime = match ($extension) {
                'jpg', 'jpeg' => 'image/jpeg',
                'svg' => 'image/svg+xml',
                default => 'image/png',
            };

            $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoFile));
        }

        return [
            'name' => 'Frozio Hub',
            'address_lines' => [
                '522/c, Dhramapala mawatha,',
                'Aggona junction, Koswatta',
            ],
            'phone' => '0765807548',
            'website' => 'www.froziohub.com',
            'logo_url' => asset('assets/images/froziohub-logo.png'),
            'logo_path' => $logoFile,
            'logo_base64' => $logoBase64,
        ];
    }
}
//kuh