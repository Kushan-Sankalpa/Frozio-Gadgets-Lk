<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingReceiptMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Booking $booking;
    public ?object $client;
    public ?object $summary;
    public array $sales;
    public array $services;
    public string $currencySymbol;

    /**
     * @param  Booking  $booking
     * @param  array  $payload  e.g. ['summary' => ..., 'sales' => ..., 'services' => ..., 'currencySymbol' => 'LKR']
     */
    public function __construct(Booking $booking, array $payload = [])
    {
        $this->booking        = $booking;
        $this->client         = $payload['client']   ?? $booking->client ?? null;
        $this->summary        = $payload['summary']  ?? null;
        $this->sales          = $payload['sales']    ?? [];
        $this->services       = $payload['services'] ?? [];
        $this->currencySymbol = $payload['currencySymbol'] ?? 'LKR';
    }

    public function build()
    {
        return $this->subject('Your booking receipt #' . $this->booking->id)
            ->view('emails.booking_receipt')
            ->with([
                'booking'        => $this->booking,
                'client'         => $this->client,
                'summary'        => $this->summary,
                'sales'          => $this->sales,
                'services'       => $this->services,
                'currencySymbol' => $this->currencySymbol,
            ]);
    }
}
