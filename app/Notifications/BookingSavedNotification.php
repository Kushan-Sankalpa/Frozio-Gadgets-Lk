<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class BookingSavedNotification extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking) {}

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        return [
              'id'         => $this->id,
            'title'      => 'New booking created',
            'message'    => 'Booking created for ' . $this->getClientName(),
            'clientName' => $this->getClientName(),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function toArray($notifiable): array
    {
        return [
            'id'         => $this->id,
            'title'      => 'New booking created',
            'message'    => 'Booking created for ' . $this->getClientName(),
            'clientName' => $this->getClientName(),
            'read_at'    => null,
            'created_at' => now()->toIso8601String(),
        ];
    }

    private function getClientName(): string
    {
        if ($this->booking->client) {
            return $this->booking->client->first_name;
        }

        return $this->booking->walkin_name
            ?? $this->booking->customer_name
            ?? 'Walk-in Customer';
    }
}
