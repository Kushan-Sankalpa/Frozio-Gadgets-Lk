<?php

namespace App\Services;

use App\Models\SmsGatewaySetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsGateway
{
    protected static function normalizePhone(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone ?? '');

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '94') && strlen($digits) === 11) {
            return $digits;
        }

        if (strlen($digits) === 10 && $digits[0] === '0') {
            return '94' . substr($digits, 1);
        }

        if (strlen($digits) === 9 && $digits[0] === '7') {
            return '94' . $digits;
        }

        return null;
    }

   public static function sendWithResponse(string $phone, string $message): array
{
    $gateway = SmsGatewaySetting::first();

    if (! $gateway) {
        Log::warning('SMS not sent: gateway settings missing');

        return [
            'success'     => false,
            'status_code' => null,
            'body'        => 'Missing SMS gateway settings',
            'raw'         => null,
        ];
    }

    $normalized = self::normalizePhone($phone);

    if (! $normalized) {
        Log::warning('SMS not sent: invalid phone format', [
            'original' => $phone,
        ]);

        return [
            'success'     => false,
            'status_code' => null,
            'body'        => 'Invalid phone format',
            'raw'         => null,
        ];
    }

    $recipient = '+' . $normalized;

    try {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $gateway->api_key,
            'Content-Type'  => 'application/json',
        ])->post($gateway->url, [
            'recipient' => $recipient,
            'sender_id' => $gateway->sender_id,
            'type'      => 'plain',
            'message'   => $message,
        ]);

        return [
            'success'     => $response->successful(),
            'status_code' => $response->status(),
            'body'        => $response->json() ?? $response->body(),
            'raw'         => [
                'status'  => $response->status(),
                'headers' => $response->headers(),
                'body'    => $response->body(),
            ],
        ];
    } catch (\Throwable $e) {
        Log::error('SMS send exception', [
            'phone'   => $phone,
            'message' => $message,
            'error'   => $e->getMessage(),
        ]);

        return [
            'success'     => false,
            'status_code' => null,
            'body'        => $e->getMessage(),
            'raw'         => null,
        ];
    }
}

    public static function send(string $phone, string $message): bool
    {
        $res = self::sendWithResponse($phone, $message);
        return $res['success'];
    }

  
}


