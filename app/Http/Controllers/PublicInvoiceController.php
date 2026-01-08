<?php

namespace App\Http\Controllers;

use App\Models\BookingSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Barryvdh\DomPDF\Facade\Pdf;

class PublicInvoiceController extends Controller
{
    public function show(Request $request, BookingSale $sale)
    {
        $sale->loadMissing(['booking.client', 'booking.staff', 'booking.services', 'booking.sales']);

        $booking = $sale->booking;

        // build summary similar to showDetails()
        $sales = $booking->sales()->orderBy('created_at', 'asc')->get();
        $latestSale = $sales->last();

        $totalPaid = (float) $sales->sum('total_paid');
        $remaining = $latestSale ? (float) $latestSale->remaining : max(0, (float)$booking->total_price - $totalPaid);

        $summary = (object)[
            'booking_id'  => $booking->id,
            'status'      => $booking->status ?? 'scheduled',
            'total_price' => (float) $booking->total_price,
            'total_paid'  => $totalPaid,
            'remaining'   => $remaining,
            'has_sales'   => $sales->isNotEmpty(),
        ];

        $pdfUrl = URL::temporarySignedRoute(
            'public.invoice.pdf',
            now()->addDays(30),
            ['sale' => $sale->id]
        );

        return view('public.invoice', [
            'sale' => $sale,
            'booking' => $booking,
            'sales' => $sales,
            'summary' => $summary,
            'currencySymbol' => 'LKR',
            'pdfUrl' => $pdfUrl,
        ]);
    }

    public function pdf(Request $request, BookingSale $sale)
    {
        $sale->loadMissing(['booking.client', 'booking.staff', 'booking.services', 'booking.sales']);

        $pdf = Pdf::loadView('pdf.invoice', [
            'sale' => $sale,
            'booking' => $sale->booking,
            'currencySymbol' => 'LKR',
        ])->setPaper('a4', 'portrait');

        $filename = "Invoice-Sale-{$sale->id}.pdf";

        return $pdf->stream($filename);
    }
}
