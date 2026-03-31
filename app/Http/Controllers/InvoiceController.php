<?php

namespace App\Http\Controllers;

use App\Models\ColorOption;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\ShoeProduct;
use App\Models\StorageOption;
use App\Models\WarrantyOption;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    public function index()
    {
        return Inertia::render('Invoice/index', [
            'message' => 'Invoices',
        ]);
    }

    public function create()
    {
        return Inertia::render('Invoice/partials/CreateUpdate', [
            'mode' => 'create',
            'invoice' => null,
            'techProducts' => $this->techProductsPayload(),
            'shoeProducts' => $this->shoeProductsPayload(),
            'shop' => $this->shopInfo(),
            'nextInvoiceNo' => $this->nextInvoiceNumber(),
        ]);
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load('items');

        return Inertia::render('Invoice/partials/CreateUpdate', [
            'mode' => 'edit',
            'invoice' => [
                'id' => $invoice->id,
                'invoice_no' => $invoice->invoice_no,
                'invoice_date' => optional($invoice->invoice_date)->format('Y-m-d'),
                'customer_name' => $invoice->customer_name,
                'customer_contact_number' => $invoice->customer_contact_number,
                'customer_address' => $invoice->customer_address,
                'customer_email' => $invoice->customer_email,
                'sales_person' => $invoice->sales_person,
                'ship_date' => optional($invoice->ship_date)->format('Y-m-d'),
                'ship_via' => $invoice->ship_via,
                'delivery_enabled' => (bool) $invoice->delivery_enabled,
                'delivery_method' => $invoice->delivery_method,
                'delivery_payment_status' => $invoice->delivery_payment_status,
                'tracking_id' => $invoice->tracking_id,
                'delivery_agent' => $invoice->delivery_agent,
                'delivery_amount' => (float) ($invoice->delivery_amount ?? 0),
                'payment_type' => $invoice->payment_type,
                'cash_paid' => (float) $invoice->cash_paid,
                'card_paid' => (float) $invoice->card_paid,
                'advance_amount' => (float) $invoice->advance_amount,
                'paid_amount' => (float) $invoice->paid_amount,
                'subtotal' => (float) $invoice->subtotal,
                'total_discount' => (float) $invoice->total_discount,
                'tax_amount' => (float) $invoice->tax_amount,
                'grand_total' => (float) $invoice->grand_total,
                'balance_due' => (float) $invoice->balance_due,
                'notes' => $invoice->notes,
                'terms' => $invoice->terms,
                'status' => $invoice->status,
                'pdf_path' => $invoice->pdf_path,
                'pdf_url' => $invoice->pdf_url,
                'items' => $invoice->items->map(fn (InvoiceItem $item) => [
                    'id' => $item->id,
                    'item_no' => $item->item_no,
                    'product_type' => $item->product_type,
                    'product_id' => $item->product_id,
                    'model_name' => $item->model_name,
                    'storage' => $item->storage,
                    'color' => $item->color,
                    'size' => $item->size,
                    'imei_serial' => $item->imei_serial,
                    'warranty' => $item->warranty,
                    'is_preorder' => (bool) $item->is_preorder,
                    'description' => $item->description,
                    'qty' => (int) $item->qty,
                    'regular_price' => (float) $item->regular_price,
                    'discount_type' => $item->discount_type,
                    'discount_value' => $item->discount_value !== null ? (float) $item->discount_value : null,
                    'discount_percent_display' => $item->discount_percent_display !== null ? (float) $item->discount_percent_display : null,
                    'discounted_unit_price' => (float) $item->discounted_unit_price,
                    'line_total' => (float) $item->line_total,
                ])->values(),
            ],
            'techProducts' => $this->techProductsPayload(),
            'shoeProducts' => $this->shoeProductsPayload(),
            'shop' => $this->shopInfo(),
            'nextInvoiceNo' => $invoice->invoice_no,
        ]);
    }

    public function data(Request $request)
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = trim((string) $request->input('search.value', ''));

        $baseQuery = Invoice::query();

        $recordsTotal = (clone $baseQuery)->count();

        if ($searchValue !== '') {
            $baseQuery->where(function ($q) use ($searchValue) {
                $q->where('invoice_no', 'like', "%{$searchValue}%")
                    ->orWhere('customer_name', 'like', "%{$searchValue}%")
                    ->orWhere('customer_contact_number', 'like', "%{$searchValue}%")
                    ->orWhere('sales_person', 'like', "%{$searchValue}%")
                    ->orWhere('payment_type', 'like', "%{$searchValue}%")
                    ->orWhere('delivery_method', 'like', "%{$searchValue}%")
                    ->orWhere('tracking_id', 'like', "%{$searchValue}%")
                    ->orWhere('delivery_agent', 'like', "%{$searchValue}%")
                    ->orWhere('status', 'like', "%{$searchValue}%");
            });
        }

        $recordsFiltered = (clone $baseQuery)->count();

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

        $columns = [
            0 => 'id',
            1 => 'invoice_no',
            2 => 'invoice_date',
            3 => 'customer_name',
            4 => 'customer_contact_number',
            5 => 'sales_person',
            6 => 'payment_type',
            7 => 'grand_total',
            8 => 'paid_amount',
            9 => 'balance_due',
            10 => 'status',
        ];

        $orderBy = $columns[$orderColIndex] ?? 'id';

        $rows = $baseQuery
            ->orderBy($orderBy, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function (Invoice $invoice) {
            $statusBadge = match ($invoice->status) {
                'finalized' => '<span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Finalized</span>',
                'cancelled' => '<span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700">Cancelled</span>',
                default => '<span class="inline-flex items-center rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-700">Draft</span>',
            };

            $viewBtn = '
                <button
                    type="button"
                    data-action="view"
                    data-id="' . $invoice->id . '"
                    class="rounded-full border border-blue-200 px-3 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-50"
                >
                    View PDF
                </button>
            ';

            $downloadBtn = '
                <button
                    type="button"
                    data-action="download"
                    data-id="' . $invoice->id . '"
                    class="rounded-full border border-emerald-200 px-3 py-1.5 text-xs font-medium text-emerald-600 hover:bg-emerald-50"
                >
                    Download
                </button>
            ';

            $editBtn = '
                <button
                    type="button"
                    data-action="edit"
                    data-id="' . $invoice->id . '"
                    data-name="' . e($invoice->invoice_no) . '"
                    class="rounded-full border border-neutral-200 px-3 py-1.5 text-xs font-medium text-neutral-700 hover:bg-neutral-100"
                >
                    Edit
                </button>
            ';

            $deleteBtn = '
                <button
                    type="button"
                    data-action="delete"
                    data-id="' . $invoice->id . '"
                    data-name="' . e($invoice->invoice_no) . '"
                    class="rounded-full border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50"
                >
                    Delete
                </button>
            ';

            return [
                'id' => $invoice->id,
                'invoice_no' => e($invoice->invoice_no),
                'invoice_date' => optional($invoice->invoice_date)->format('Y-m-d'),
                'customer_name' => e($invoice->customer_name),
                'customer_contact_number' => e($invoice->customer_contact_number),
                'sales_person' => e($invoice->sales_person ?? '-'),
                'payment_type' => e(ucfirst(str_replace('_', ' ', $invoice->payment_type ?? 'unpaid'))),
                'grand_total' => number_format((float) $invoice->grand_total, 2),
                'paid_amount' => number_format((float) $invoice->paid_amount, 2),
                'balance_due' => number_format((float) $invoice->balance_due, 2),
                'status_badge' => $statusBadge,
                'actions' => '<div class="flex flex-wrap items-center gap-2">' . $viewBtn . $downloadBtn . $editBtn . $deleteBtn . '</div>',
            ];
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

   public function store(Request $request)
{
    $validated = $this->validateInvoice($request);
    $submitAction = $request->input('submit_action', 'draft');

    $invoice = DB::transaction(function () use ($validated, $submitAction) {
        $itemsPayload = $this->normalizeItems($validated['items'] ?? []);

        $cashPaid = round((float) ($validated['cash_paid'] ?? 0), 2);
        $cardPaid = round((float) ($validated['card_paid'] ?? 0), 2);
        $advanceAmount = round((float) ($validated['advance_amount'] ?? 0), 2);
        $taxAmount = round((float) ($validated['tax_amount'] ?? 0), 2);

        $deliveryEnabled = (bool) ($validated['delivery_enabled'] ?? false);
        $deliveryAmount = $deliveryEnabled
            ? round((float) ($validated['delivery_amount'] ?? 0), 2)
            : 0;

        $totals = $this->calculateTotals(
            $itemsPayload,
            $cashPaid,
            $cardPaid,
            $advanceAmount,
            $taxAmount,
            $deliveryAmount
        );

        $invoiceNo = $this->nextInvoiceNumberForUpdate();

        $invoice = Invoice::create([
            'invoice_no' => $invoiceNo,
            'invoice_date' => $validated['invoice_date'],
            'customer_name' => $validated['customer_name'],
            'customer_contact_number' => $validated['customer_contact_number'],
            'customer_address' => $validated['customer_address'] ?? null,
            'customer_email' => $validated['customer_email'] ?? null,
            'sales_person' => $validated['sales_person'] ?? null,
            'ship_date' => $validated['ship_date'] ?? null,
            'ship_via' => $validated['ship_via'] ?? null,
            'delivery_enabled' => $deliveryEnabled,
            'delivery_method' => $deliveryEnabled ? ($validated['delivery_method'] ?? null) : null,
            'delivery_payment_status' => $deliveryEnabled ? ($validated['delivery_payment_status'] ?? null) : null,
            'tracking_id' => $deliveryEnabled ? ($validated['tracking_id'] ?? null) : null,
            'delivery_agent' => $deliveryEnabled ? ($validated['delivery_agent'] ?? null) : null,
            'delivery_amount' => $deliveryEnabled ? $totals['delivery_amount'] : null,
            'payment_type' => $this->detectPaymentType($cashPaid, $cardPaid, $advanceAmount),
            'cash_paid' => $totals['cash_paid'],
            'card_paid' => $totals['card_paid'],
            'advance_amount' => $totals['advance_amount'],
            'paid_amount' => $totals['paid_amount'],
            'subtotal' => $totals['subtotal'],
            'total_discount' => $totals['total_discount'],
            'tax_amount' => $totals['tax_amount'],
            'grand_total' => $totals['grand_total'],
            'balance_due' => $totals['balance_due'],
            'notes' => $validated['notes'] ?? null,
            'terms' => $validated['terms'] ?? null,
            'status' => $submitAction === 'finalize' ? 'finalized' : ($validated['status'] ?? 'draft'),
        ]);

        foreach ($itemsPayload as $index => $item) {
            $invoice->items()->create([
                ...$item,
                'item_no' => $index + 1,
            ]);
        }

        if ($submitAction === 'finalize') {
            $path = $this->generateAndStorePdf($invoice->fresh('items'));
            $invoice->update(['pdf_path' => $path]);
        }

        return $invoice;
    });

    return redirect()
        ->route('invoices.edit', $invoice)
        ->with('success', $submitAction === 'finalize'
            ? 'Invoice created and PDF generated.'
            : 'Invoice saved as draft.');
}

    public function update(Request $request, Invoice $invoice)
    {
        $validated = $this->validateInvoice($request, $invoice->id);
        $submitAction = $request->input('submit_action', 'draft');

        DB::transaction(function () use ($validated, $invoice, $submitAction) {
            $itemsPayload = $this->normalizeItems($validated['items'] ?? []);

            $cashPaid = round((float) ($validated['cash_paid'] ?? 0), 2);
            $cardPaid = round((float) ($validated['card_paid'] ?? 0), 2);
            $advanceAmount = round((float) ($validated['advance_amount'] ?? 0), 2);
            $taxAmount = round((float) ($validated['tax_amount'] ?? 0), 2);

            $deliveryEnabled = (bool) ($validated['delivery_enabled'] ?? false);
            $deliveryAmount = $deliveryEnabled
                ? round((float) ($validated['delivery_amount'] ?? 0), 2)
                : 0;

            $totals = $this->calculateTotals(
                $itemsPayload,
                $cashPaid,
                $cardPaid,
                $advanceAmount,
                $taxAmount,
                $deliveryAmount
            );

            $invoice->update([
                'invoice_date' => $validated['invoice_date'],
                'customer_name' => $validated['customer_name'],
                'customer_contact_number' => $validated['customer_contact_number'],
                'customer_address' => $validated['customer_address'] ?? null,
                'customer_email' => $validated['customer_email'] ?? null,
                'sales_person' => $validated['sales_person'] ?? null,
                'ship_date' => $validated['ship_date'] ?? null,
                'ship_via' => $validated['ship_via'] ?? null,
                'delivery_enabled' => $deliveryEnabled,
                'delivery_method' => $deliveryEnabled ? ($validated['delivery_method'] ?? null) : null,
                'delivery_payment_status' => $deliveryEnabled ? ($validated['delivery_payment_status'] ?? null) : null,
                'tracking_id' => $deliveryEnabled ? ($validated['tracking_id'] ?? null) : null,
                'delivery_agent' => $deliveryEnabled ? ($validated['delivery_agent'] ?? null) : null,
                'delivery_amount' => $deliveryEnabled ? $totals['delivery_amount'] : null,
                'payment_type' => $this->detectPaymentType($cashPaid, $cardPaid, $advanceAmount),
                'cash_paid' => $totals['cash_paid'],
                'card_paid' => $totals['card_paid'],
                'advance_amount' => $totals['advance_amount'],
                'paid_amount' => $totals['paid_amount'],
                'subtotal' => $totals['subtotal'],
                'total_discount' => $totals['total_discount'],
                'tax_amount' => $totals['tax_amount'],
                'grand_total' => $totals['grand_total'],
                'balance_due' => $totals['balance_due'],
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
                'status' => $submitAction === 'finalize'
                    ? 'finalized'
                    : ($validated['status'] ?? $invoice->status ?? 'draft'),
            ]);

            $invoice->items()->delete();

            foreach ($itemsPayload as $index => $item) {
                $invoice->items()->create([
                    ...$item,
                    'item_no' => $index + 1,
                ]);
            }

            if ($submitAction === 'finalize') {
                if ($invoice->pdf_path && Storage::disk('public')->exists($invoice->pdf_path)) {
                    Storage::disk('public')->delete($invoice->pdf_path);
                }

                $path = $this->generateAndStorePdf($invoice->fresh('items'));
                $invoice->update(['pdf_path' => $path]);
            }
        });

        return redirect()
            ->route('invoices.edit', $invoice)
            ->with('success', $submitAction === 'finalize'
                ? 'Invoice updated and PDF regenerated.'
                : 'Invoice updated.');
    }

    public function destroy(Invoice $invoice)
    {
        if ($invoice->pdf_path && Storage::disk('public')->exists($invoice->pdf_path)) {
            Storage::disk('public')->delete($invoice->pdf_path);
        }

        $invoice->delete();

        return redirect()
            ->route('invoices.index')
            ->with('success', 'Invoice deleted.');
    }

    public function pdf(Invoice $invoice)
    {
        $invoice->load('items');

        if ($invoice->pdf_path && Storage::disk('public')->exists($invoice->pdf_path)) {
            Storage::disk('public')->delete($invoice->pdf_path);
        }

        $path = $this->generateAndStorePdf($invoice);
        $invoice->update(['pdf_path' => $path]);

        return response()
            ->file(storage_path('app/public/' . $path))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function download(Invoice $invoice)
    {
        $invoice->load('items');

        if ($invoice->pdf_path && Storage::disk('public')->exists($invoice->pdf_path)) {
            Storage::disk('public')->delete($invoice->pdf_path);
        }

        $path = $this->generateAndStorePdf($invoice);
        $invoice->update(['pdf_path' => $path]);

        return response()->download(
            storage_path('app/public/' . $path),
            $invoice->invoice_no . '.pdf'
        );
    }

    protected function validateInvoice(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'invoice_date' => ['required', 'date'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_contact_number' => ['required', 'string', 'max:255'],
            'customer_address' => ['nullable', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'sales_person' => ['nullable', 'string', 'max:255'],
            'ship_date' => ['nullable', 'date'],
            'ship_via' => ['nullable', 'string', 'max:255'],

            'delivery_enabled' => ['nullable', 'boolean'],
            'delivery_method' => ['nullable', 'in:cash_on_delivery,paid_delivery,pickme_flash,uber_flash'],
            'delivery_payment_status' => ['nullable', 'in:paid,non_paid'],
            'tracking_id' => ['nullable', 'string', 'max:255'],
            'delivery_agent' => ['nullable', 'in:domex,pickme'],
            'delivery_amount' => ['nullable', 'numeric', 'min:0'],

            'cash_paid' => ['nullable', 'numeric', 'min:0'],
            'card_paid' => ['nullable', 'numeric', 'min:0'],
            'advance_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
            'status' => ['nullable', 'in:draft,finalized,cancelled'],
            'submit_action' => ['nullable', 'in:draft,finalize'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_type' => ['required', 'in:tech,shoe'],
            'items.*.product_id' => ['nullable', 'integer'],
            'items.*.model_name' => ['nullable', 'string', 'max:255'],
            'items.*.storage' => ['nullable', 'string', 'max:255'],
            'items.*.color' => ['nullable', 'string', 'max:255'],
            'items.*.size' => ['nullable', 'string', 'max:255'],
            'items.*.imei_serial' => ['nullable', 'string', 'max:255'],
            'items.*.warranty' => ['nullable', 'string', 'max:255'],
            'items.*.is_preorder' => ['nullable', 'boolean'],
            'items.*.description' => ['required', 'string'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.regular_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_type' => ['nullable', 'in:percentage,fixed'],
            'items.*.discount_value' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_percent_display' => ['nullable', 'numeric', 'min:0'],
            'items.*.discounted_unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.line_total' => ['required', 'numeric', 'min:0'],
        ]);
    }

    protected function normalizeItems(array $items): array
    {
        return collect($items)
            ->values()
            ->map(function ($item) {
                return [
                    'product_type' => $item['product_type'],
                    'product_id' => !empty($item['product_id']) ? (int) $item['product_id'] : null,
                    'model_name' => $item['model_name'] ?? null,
                    'storage' => $item['storage'] ?? null,
                    'color' => $item['color'] ?? null,
                    'size' => $item['size'] ?? null,
                    'imei_serial' => $item['imei_serial'] ?? null,
                    'warranty' => $item['warranty'] ?? null,
                    'is_preorder' => (bool) ($item['is_preorder'] ?? false),
                    'description' => $item['description'],
                    'qty' => max(1, (int) ($item['qty'] ?? 1)),
                    'regular_price' => round((float) ($item['regular_price'] ?? 0), 2),
                    'discount_type' => $item['discount_type'] ?? null,
                    'discount_value' => isset($item['discount_value']) ? round((float) $item['discount_value'], 2) : null,
                    'discount_percent_display' => isset($item['discount_percent_display']) ? round((float) $item['discount_percent_display'], 2) : null,
                    'discounted_unit_price' => round((float) ($item['discounted_unit_price'] ?? 0), 2),
                    'line_total' => round((float) ($item['line_total'] ?? 0), 2),
                ];
            })
            ->all();
    }

    protected function calculateTotals(
        array $items,
        float $cashPaid = 0,
        float $cardPaid = 0,
        float $advanceAmount = 0,
        float $taxAmount = 0,
        float $deliveryAmount = 0
    ): array {
        $subtotal = 0;
        $grandTotalBeforeTax = 0;

        foreach ($items as $item) {
            $regular = (float) $item['regular_price'];
            $qty = max(1, (int) $item['qty']);
            $lineTotal = (float) $item['line_total'];

            $subtotal += ($regular * $qty);
            $grandTotalBeforeTax += $lineTotal;
        }

        $deliveryAmount = round(max(0, $deliveryAmount), 2);

        $subtotal = round($subtotal + $deliveryAmount, 2);
        $grandTotalBeforeTax = round($grandTotalBeforeTax + $deliveryAmount, 2);
        $taxAmount = round($taxAmount, 2);
        $cashPaid = round(max(0, $cashPaid), 2);
        $cardPaid = round(max(0, $cardPaid), 2);
        $advanceAmount = round(max(0, $advanceAmount), 2);

        $grandTotal = round($grandTotalBeforeTax + $taxAmount, 2);
        $totalDiscount = round($subtotal - $grandTotalBeforeTax, 2);
        $paidAmount = round($cashPaid + $cardPaid + $advanceAmount, 2);
        $balanceDue = round(max(0, $grandTotal - $paidAmount), 2);

        return [
            'subtotal' => $subtotal,
            'total_discount' => $totalDiscount,
            'tax_amount' => $taxAmount,
            'grand_total' => $grandTotal,
            'cash_paid' => $cashPaid,
            'card_paid' => $cardPaid,
            'advance_amount' => $advanceAmount,
            'paid_amount' => $paidAmount,
            'balance_due' => $balanceDue,
            'delivery_amount' => $deliveryAmount,
        ];
    }

    protected function detectPaymentType(float $cashPaid, float $cardPaid, float $advanceAmount): string
    {
        $hasCash = $cashPaid > 0;
        $hasCard = $cardPaid > 0;
        $hasAdvance = $advanceAmount > 0;

        $count = collect([$hasCash, $hasCard, $hasAdvance])->filter()->count();

        if ($count === 0) {
            return 'unpaid';
        }

        if ($count > 1) {
            return 'mixed';
        }

        if ($hasCash) {
            return 'cash';
        }

        if ($hasCard) {
            return 'card';
        }

        return 'advance';
    }

   protected function nextInvoiceNumber(): string
{
    $latestInvoiceNo = Invoice::query()
        ->latest('id')
        ->value('invoice_no');

    return $this->formatNextInvoiceNumber($latestInvoiceNo);
}
protected function nextInvoiceNumberForUpdate(): string
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

protected function formatNextInvoiceNumber(?string $latestInvoiceNo): string
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

    protected function generateAndStorePdf(Invoice $invoice): string
    {
        $invoice->loadMissing('items');

        $shop = $this->shopInfo();

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'shop' => $shop,
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

    protected function shopInfo(): array
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

    protected function techProductsPayload()
    {
        $storageMap = StorageOption::query()->get()->keyBy('id');
        $colorMap = ColorOption::query()->get()->keyBy('id');
        $warrantyMap = WarrantyOption::query()->get()->keyBy('id');

        return Product::query()
            ->with(['brand:id,name', 'category:id,name'])
            ->orderBy('model')
            ->get()
            ->map(function (Product $product) use ($storageMap, $colorMap, $warrantyMap) {
                $storageOptionIds = collect($product->storage_option_ids ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->values();

                $colorIds = collect($product->color_ids ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->values();

                return [
                    'id' => $product->id,
                    'label' => $product->model,
                    'model' => $product->model,
                    'brand' => $product->brand?->name,
                    'category' => $product->category?->name,
                    'price' => (float) $product->price_lkr,
                    'warranty' => $product->warranty_period
                        ?: ($product->warranty_option_id ? optional($warrantyMap->get($product->warranty_option_id))->name : null),
                    'storages' => $storageOptionIds->map(function ($id) use ($storageMap) {
                        $option = $storageMap->get($id);

                        return $option
                            ? trim($option->value . ' ' . $option->unit)
                            : null;
                    })->filter()->values(),
                    'colors' => $colorIds->map(function ($id) use ($colorMap) {
                        return optional($colorMap->get($id))->name;
                    })->filter()->values(),
                ];
            })
            ->values();
    }

    protected function shoeProductsPayload()
    {
        return ShoeProduct::query()
            ->with(['brand:id,name', 'category:id,name'])
            ->orderBy('name')
            ->get()
            ->map(function (ShoeProduct $product) {
                $sizes = collect($product->sizes_by_type ?? [])
                    ->flatMap(function ($entry) {
                        return collect($entry['sizes'] ?? []);
                    })
                    ->filter()
                    ->unique()
                    ->values();

                return [
                    'id' => $product->id,
                    'label' => $product->name,
                    'name' => $product->name,
                    'brand' => $product->brand?->name,
                    'category' => $product->category?->name,
                    'price' => (float) ($product->sale_price ?: $product->regular_price ?: 0),
                    'regular_price' => (float) ($product->regular_price ?: 0),
                    'sizes' => $sizes,
                ];
            })
            ->values();
    }
}