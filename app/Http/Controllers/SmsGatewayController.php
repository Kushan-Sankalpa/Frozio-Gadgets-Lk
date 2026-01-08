<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\SmsGatewaySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class SmsGatewayController extends Controller
{
    public function index()
    {
        $gateway = SmsGatewaySetting::first();
        $countries = Country::all();

        return Inertia::render('SmsGateway/Index', [
            'gateway' => $gateway,
            'countries' => $countries,
        ]);
    }

    public function storeOrUpdate(Request $request)
    {
        $request->validate([
            'sender_id' => 'required|string',
            'url' => 'required|url',
            'api_key' => 'required|string',
            'supported_countries' => 'nullable|array',
        ]);

        $gateway = SmsGatewaySetting::first();

        if ($gateway) {
            $gateway->update($request->all());
        } else {
            SmsGatewaySetting::create($request->all());
        }

        return redirect()->back()->with('success', 'SMS Gateway settings saved.');
    }

    public function testSMS(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string|max:160',
        ]);

        $gateway = SmsGatewaySetting::firstOrFail();


        $response = Http::withHeaders([
            'Authorization' =>  'Bearer ' . $gateway->api_key,
            'Content-Type' => 'application/json'
        ])->post($gateway->url, [
            'recipient' =>  $request->phone,
            'sender_id' => $gateway->sender_id,
            'type' => 'plain',
            'message' => $request->message,
        ]);


        // Log::info($response);
        if ($response->successful()) {
            return response()->json([
                'message' => 'SMS sent successfully.'
            ]);
        }

        return response()->json([
            'message' => 'Failed to send SMS: ' . $response->body()
        ], 422);
    }
}
