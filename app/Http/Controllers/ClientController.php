<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Client;
use App\Models\Country;
use App\Models\LoyaltyPointLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;



class ClientController extends Controller
{
    public function index()
    {
        return Inertia::render('Clients/Index', []);
    }

    public function getdata(Request $request)
    {

        $query = Client::query()
            ->leftJoin('countries as c', 'clients.country_id', '=', 'c.id')
            ->select([
                'clients.*',
                'c.name as country_name',
            ]);

        return DataTables::of($query)

            ->addColumn('name', fn($row) => trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? '')) ?: '—')
            ->addColumn('avatar_url', function ($row) {

                return $row->getFirstMediaUrl('avatar') ?: null;
            })


            ->editColumn('email', fn($row) => $row->email ?? '—')
            ->addColumn('phone', function ($row) {
                $code = $row->phone_code ? '+' . ltrim($row->phone_code, '+') . ' ' : '';
                $num  = $row->phone ?? '';
                return trim($code . $num) ?: '—';
            })
            ->addColumn('country', function ($row) {

                return $row->country_name ?: ($row->address_country ?? '—');
            })


            ->editColumn('created_at', fn($row) => optional($row->created_at)->toDateString())


            ->addColumn('action', fn() => '')


            // ->filter(function ($q) use ($request) {
            //     $search = $request->input('search.value');
            //     if (!$search) return;

            //     $s = trim($search);

            //     $q->where(function ($qq) use ($s) {
            //         $qq->where('clients.first_name', 'like', "%{$s}%")
            //             ->orWhere('clients.last_name', 'like', "%{$s}%")
            //             ->orWhereRaw("CONCAT(clients.first_name,' ',clients.last_name) like ?", ["%{$s}%"])
            //             ->orWhere('clients.email', 'like', "%{$s}%")
            //             ->orWhere('clients.phone', 'like', "%{$s}%")
            //             ->orWhere('clients.phone_code', 'like', "%{$s}%")
            //             ->orWhere('c.name', 'like', "%{$s}%")
            //             ->orWhere('clients.address_country', 'like', "%{$s}%");
            //     });
            // }, true)

            // ->make(true);
                    ->filterColumn('name', function ($q, $keyword) {
            $kw = "%{$keyword}%";

            $q->where(function ($qq) use ($kw) {
                $qq->where('clients.first_name', 'like', $kw)
                    ->orWhere('clients.last_name', 'like', $kw)
                    ->orWhereRaw("CONCAT(clients.first_name,' ',clients.last_name) like ?", [$kw]);
            });
        })

        ->filterColumn('phone', function ($q, $keyword) {
            $kw = "%{$keyword}%";

            $q->where(function ($qq) use ($kw) {
                // matches raw phone
                $qq->where('clients.phone', 'like', $kw)
                    // matches just the dial code
                    ->orWhere('clients.phone_code', 'like', $kw)
                    // matches what the user actually sees: "+94 71 2345678"
                    ->orWhereRaw("CONCAT('+', clients.phone_code, ' ', clients.phone) like ?", [$kw]);
            });
        })

        ->filterColumn('country', function ($q, $keyword) {
            $kw = "%{$keyword}%";

            $q->where(function ($qq) use ($kw) {
                $qq->where('c.name', 'like', $kw)
                    ->orWhere('clients.address_country', 'like', $kw);
            });
        })

        ->make(true);

    }

    public function create()
    {
        $countries = Country::orderBy('name')->get(['id', 'name', 'code']);

        return Inertia::render('Clients/CreateUpdate', [
            'client'    => null,
            'countries' => $countries,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name'         => ['required', 'string', 'max:255'],
            'last_name'          => ['nullable', 'string', 'max:255'],
            'email'              => ['nullable', 'email', 'max:255', 'unique:clients,email'],
            'phone'              => ['nullable', 'string', 'max:25'],
            'phone_code'         => ['nullable', 'string', 'max:10'],
            'birthday_daymonth'  => ['nullable', 'regex:/^\d{2}-\d{2}$/'],
            'birthday_year'      => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
            'gender'             => ['nullable', Rule::in(['Female', 'Male', 'Non-binary', 'Prefer not to say', 'Other'])],
            'country_id'         => ['nullable', 'integer', 'exists:countries,id'],

            'addresses'              => ['nullable', 'array'],
            'addresses.0.type'       => ['nullable', Rule::in(['home', 'work', 'other'])],
            'addresses.0.address'    => ['nullable', 'string', 'max:255'],
            'addresses.0.district'   => ['nullable', 'string', 'max:120'],
            'addresses.0.city'       => ['nullable', 'string', 'max:120'],
            'addresses.0.postcode'   => ['nullable', 'string', 'max:40'],
            'addresses.0.country_id' => ['nullable', 'integer', 'exists:countries,id'],

            'avatar'   => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [], [
            'birthday_daymonth'  => 'birthday',
            'birthday_year'      => 'year',
            'phone_code'         => 'country code',
            'country_id'         => 'country',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $addr = (array) ($request->input('addresses.0') ?? []);
        $addrCountry = isset($addr['country_id']) ? Country::find((int) $addr['country_id']) : null;

        unset($data['addresses']);

        $payload = $data + [
            'address_type'        => $addr['type']      ?? null,
            'address'             => $addr['address']   ?? null,
            'district'            => $addr['district']  ?? null,
            'city'                => $addr['city']      ?? null,
            'postcode'            => $addr['postcode']  ?? null,
            'address_country_id'  => $addr['country_id'] ?? null,
            'address_country'     => $addrCountry?->name,
        ];

        $client = Client::create($payload);

        if ($request->hasFile('avatar')) {
            $client->addMediaFromRequest('avatar')->toMediaCollection('avatar');
        }


        if ($request->expectsJson()) {
            $name = trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? ''));

            $phonePretty = null;
            if ($client->phone_code || $client->phone) {
                $code = $client->phone_code ? '+' . ltrim($client->phone_code, '+') . ' ' : '';
                $phonePretty = trim($code . ($client->phone ?? ''));
            }

            return response()->json([
                'client' => [
                    'id'            => $client->id,
                    'name'          => $name ?: '—',
                    'email'         => $client->email,
                    'phone'         => $phonePretty,
                    'phone_e164'    => $client->phone_e164 ?? null,
                    'created_at'    => optional($client->created_at)->toDateTimeString(),
                    'no_show_count' => $client->no_show_count ?? 0,
                ],
            ]);
        }

        return redirect()->route('clients.index');
    }

    public function edit(int $id)
    {
        $client = Client::with('media')->findOrFail($id);
        $countries = Country::orderBy('name')->get(['id', 'name', 'code']);


        $client->setAttribute('addresses', [[
            'type'       => $client->address_type,
            'address'    => $client->address,
            'district'   => $client->district,
            'city'       => $client->city,
            'postcode'   => $client->postcode,
            'country_id' => $client->address_country_id,
        ]]);

        $avatar = $client->getFirstMedia('avatar');
        $client->setAttribute('avatar_url', $avatar ? $avatar->getFullUrl() : null);

        if (!$client->phone_e164 && ($client->phone_code || $client->phone)) {
            $code = preg_replace('/\D+/', '', (string) ($client->phone_code ?? ''));
            $num  = preg_replace('/\D+/', '', (string) ($client->phone ?? ''));
            $e164 = ($code || $num) ? '+' . $code . $num : null;
            $client->setAttribute('phone_e164', $e164);
        }

        return Inertia::render('Clients/CreateUpdate', [
            'client'    => $client,
            'countries' => $countries,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'id'                 => ['required', 'exists:clients,id'],
            'first_name'         => ['required', 'string', 'max:255'],
            'last_name'          => ['nullable', 'string', 'max:255'],
            'email'              => ['nullable', 'email', 'max:255', 'unique:clients,email,' . $request->id],
            'phone'              => ['nullable', 'string', 'max:25'],
            'phone_code'         => ['nullable', 'string', 'max:10'],
            'birthday_daymonth'  => ['nullable', 'regex:/^\d{2}-\d{2}$/'],
            'birthday_year'      => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
            'gender'             => ['nullable', Rule::in(['Female', 'Male', 'Non-binary', 'Prefer not to say', 'Other'])],
            'country_id'         => ['nullable', 'integer', 'exists:countries,id'],


            'addresses'              => ['nullable', 'array'],
            'addresses.0.type'       => ['nullable', Rule::in(['home', 'work', 'other'])],
            'addresses.0.address'    => ['nullable', 'string', 'max:255'],
            'addresses.0.district'   => ['nullable', 'string', 'max:120'],
            'addresses.0.city'       => ['nullable', 'string', 'max:120'],
            'addresses.0.postcode'   => ['nullable', 'string', 'max:40'],
            'addresses.0.country_id' => ['nullable', 'integer', 'exists:countries,id'],

            'avatar'             => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],

        ]);

        // Hash password only if user entered something
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']); // keep existing password
        }


        $client = Client::findOrFail($request->id);


        $addr = (array) ($request->input('addresses.0') ?? []);
        $addrCountry = isset($addr['country_id']) ? Country::find((int)$addr['country_id']) : null;

        unset($data['addresses']);

        $payload = $data + [
            'address_type'        => $addr['type']      ?? null,
            'address'             => $addr['address']   ?? null,
            'district'            => $addr['district']  ?? null,
            'city'                => $addr['city']      ?? null,
            'postcode'            => $addr['postcode']  ?? null,
            'address_country_id'  => $addr['country_id'] ?? null,
            'address_country'     => $addrCountry?->name,
        ];

       $client->fill($payload)->save();

if ($request->hasFile('avatar')) {
    $client->clearMediaCollection('avatar');
    $client->addMediaFromRequest('avatar')->toMediaCollection('avatar');
}

if ($request->expectsJson()) {
    $name = trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? ''));

    $phonePretty = null;
    if ($client->phone_code || $client->phone) {
        $code = $client->phone_code ? '+' . ltrim($client->phone_code, '+') . ' ' : '';
        $phonePretty = trim($code . ($client->phone ?? ''));
    }

    return response()->json([
        'client' => [
            'id'            => $client->id,
            'name'          => $name ?: '—',
            'email'         => $client->email,
            'phone'         => $phonePretty,
            'phone_e164'    => $client->phone_e164 ?? null,
            'created_at'    => optional($client->created_at)->toDateTimeString(),
            'no_show_count' => $client->no_show_count ?? 0,
        ],
    ]);
}

return redirect()->route('clients.index');

    }

    public function updatePassword(Request $request)
    {
        $authUser = Auth::user(); // the logged-in staff user
        $client = Client::findOrFail($request->id);

        // Verify the staff member's own password (like UsersController)
        $this->validatePassword($request, $authUser, 'changePassword');

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string', 'min:8'],
        ]);

        $client->password = Hash::make($request->password);
        $client->save();

        return back();
    }

    protected function validatePassword(Request $request, $user, $errorBag)
    {
        Validator::make($request->all(), [
            'confirm_password' => ['required', 'string'],
        ])->after(function ($validator) use ($request, $user) {
            if (!isset($request['confirm_password']) || !Hash::check($request['confirm_password'], $user->password)) {
                $validator->errors()->add(
                    'confirm_password',
                    __('The provided password does not match your current password.')
                );
            }
        })->validateWithBag($errorBag);
    }


    public function destroy(int $id)
    {
        $client = Client::findOrFail($id);

        $client->delete();

        return redirect()->route('clients.index');
    }

    public function show($id)
    {
        $client = Client::with('media', 'loyaltyTier')->findOrFail($id);
        $countries = Country::orderBy('name')->get(['id', 'name', 'code']);

        $bookings = Booking::where(['client_id' => $client->id])->with('client', 'services')->get();
        $client->setAttribute('addresses', [[
            'type'       => $client->address_type,
            'address'    => $client->address,
            'district'   => $client->district,
            'city'       => $client->city,
            'postcode'   => $client->postcode,
            'country_id' => $client->address_country_id,
        ]]);

        $avatar = $client->getFirstMedia('avatar');
        $client->setAttribute('avatar_url', $avatar ? $avatar->getFullUrl() : null);

        if (!$client->phone_e164 && ($client->phone_code || $client->phone)) {
            $code = preg_replace('/\D+/', '', (string) ($client->phone_code ?? ''));
            $num  = preg_replace('/\D+/', '', (string) ($client->phone ?? ''));
            $e164 = ($code || $num) ? '+' . $code . $num : null;
            $client->setAttribute('phone_e164', $e164);
        }

        return response()->json([
            'client'    => $client,
            'bookings' => $bookings,
            'logs' => $client->pointLogs()->with('admin')->get(),

        ]);
    }
    public function addPoints(Request $request, Client $client)
    {
        $request->validate([
            'points' => 'required|integer|min:1',
            'note' => 'nullable|string',
        ]);

        // Update client totals
        $client->current_points += $request->points;
        $client->lifetime_points += $request->points;
        $client->save();

        // Save log
        LoyaltyPointLog::create([
            'client_id' => $client->id,
            'admin_id' => auth()->id(),
            'points_added' => $request->points,
            'note' => $request->note,
        ]);

        return back()->with('success', 'Points added successfully');
    }
}
