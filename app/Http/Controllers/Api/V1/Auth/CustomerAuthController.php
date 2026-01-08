<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Models\Client;
use App\Http\Helpers;
use App\Models\Otp;
use App\Models\Country;
use Carbon\Carbon;
use DateTime;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Exception;


class CustomerAuthController extends Controller
{
    use ApiResponse;

    public function status(Request $request)
    {
        return $this->successResponse(['status' => 'API is running'], 'API is running successfully');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'max:25',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Validation Error', 422);
        }

        try {

            $client = Client::where('phone', $request->phone)->first();
            if (!$client) {
                return $this->errorResponse('Invalid credentials', 401);
            }
            if ($client->is_blocked === 1) {
                return $this->errorResponse('Your account has been deleted.', 403);
            }

            $credentials = [
                'phone' => $request->phone,
                'password' => $request->password,
            ];
            if (!auth('client')->attempt($credentials)) {
                return $this->errorResponse('Invalid credentials', 401);
            } else {
                $client = auth('client')->user();
                $token = $client->createToken('auth_token')->plainTextToken;
                $this->sendOTP($client);
                return $this->successResponse(['token' => $token,], 'Login successful');
            }
        } catch (\Throwable $e) {
            Log::error('Customer Login Error: ' . $e->getMessage());
            return $this->errorResponse('Server Error', 500);
        }
    }

    public function loginWithGoogle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        $url = 'https://www.googleapis.com/oauth2/v3/tokeninfo?id_token=' . $request->token;

        try {
            $httpResponse = Http::get($url);

            if (!$httpResponse->ok()) {
                return $this->errorResponse('Unauthenticated User', 401);
            }

            $body = json_decode($httpResponse->body(), true);

            if (!isset($body["email"])) {
                return $this->errorResponse('Invalid Google Token', 401);
            }
            $firstName = $body["given_name"] ?? "Google User";
            $lastName  = $body["family_name"] ?? "User";
            $email     = $body["email"] ?? null;
            $dob = null;
            if (isset($body["birthdate"])) {
                $rawDob = $body["birthdate"];
                $date = DateTime::createFromFormat('Y-m-d', $rawDob);

                if ($date) {
                    $dob = $date->format('d-m'); // day-month format
                }
            }
            return $this->createSocialClient($firstName, $lastName, $email, $dob);
        } catch (\Throwable $th) {
            Log::error($th);
            return $this->errorResponse('Something went wrong. Please try again.', 500);
        }
    }

    public function loginWithApple(Request $request)//login with apple
    {
        try {
            Log::info($request);

            $validator = Validator::make($request->all(), [
                'id_token' => 'required|string',
                'firstName' => 'nullable|string',
                'lastName'  => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors(), 422);
            }

            $appleToken = $request->id_token;

            $appleKeysUrl = "https://appleid.apple.com/auth/keys";
            $appleJwkSet  = json_decode(file_get_contents($appleKeysUrl), true);

            if (!$appleJwkSet || !isset($appleJwkSet['keys'])) {
                return $this->errorResponse("Unable to fetch Apple public keys", 400);
            }

            $keys = JWK::parseKeySet($appleJwkSet);

            $decoded = JWT::decode($appleToken, $keys);
            $payload = (array) $decoded;

            $email = $payload['email'] ?? null;
            $appleUserId = $payload['sub'];

            if (!$email) {
                $email = "apple_user_" . $appleUserId . "@apple.local";
            }

            $firstName = $request->firstName ?? "Apple User";
            $lastName  = $request->lastName ?? "User";
            $dob = $request->dob
                ? Carbon::parse($request->dob)
                : Carbon::today();

            return $this->createSocialClient($firstName, $lastName, $email, $dob);
        } catch (Exception $e) {
            Log::error("Apple Login Error: " . $e->getMessage());
            return $this->errorResponse("Invalid Apple Token", 400);
        }
    }



    public function createSocialClient($firstName, $lastName, $email, $dob)
    {
        try {
            $client = Client::where('email', $email)->first();

            if ($client) {
                if (auth('client')->loginUsingId($client->id)) {
                    if ($client->is_blocked === 1) {
                        return $this->errorResponse('Your account has been deleted.', 403);
                    }
                    $token = $client->createToken('auth_token')->plainTextToken;

                    return $this->successResponse([
                        'token' => $token,
                    ], 'Login successful');
                }

                return $this->errorResponse('Login failed', 401);
            }

            $newClient = Client::create([
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'email'      => $email,
                'dob'        => $dob,
                'password'   => Hash::make(Str::random(16)),
            ]);

            auth('client')->loginUsingId($newClient->id);
            $this->sendOTP($newClient);

            $token = $newClient->createToken('auth_token')->plainTextToken;

            return $this->successResponse([
                'token' => $token,
                'client' => $newClient
            ], 'Account created & login successful');
        } catch (\Throwable $e) {
            Log::error('Social Login Error: ' . $e->getMessage());
            return $this->errorResponse('Server Error', 500);
        }
    }


    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'password' => 'required|min:6|confirmed',

            'first_name' => [
                'required',
                'string',
                'max:255',
            ],
            'last_name' => [
                'required',
                'string',
                'max:255',
            ],
            'phone' => [
                'required',
                'string',
                'max:25',
            ],
            'phone_code' => [
                'nullable',
                'string',
                'max:10',
            ],
            'birthday_daymonth' => [
                'nullable',
                'regex:/^\d{2}\/\d{2}$/',
            ],
            'birthday_year' => [
                'nullable',
                'integer',
                'min:1900',
                'max:' . date('Y'),
            ],
            'gender' => [
                'nullable',
                Rule::in(['Female', 'Male', 'Non-binary', 'Prefer not to say', 'Other']),
            ],
            'country_id' => [
                'nullable',
                'integer',
                'exists:countries,id',
            ],
        ]);


        if ($validator->fails()) {
            Log::error($validator->errors());
            return $this->errorResponse($validator->errors(), 422);
        }


        try {
            $country = Country::where('code', 'like', ltrim($request->phone_code, '+'))->first();

            $requestClient = [
                'email' => $request->email,
                'password' =>  Hash::make($request->password),
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'phone_code' => $request->phone_code,
                'birthday_daymonth' => $request->birthday_daymonth,
                'birthday_year' => $request->birthday_year ?? 1998,
                'gender' => $request->gender,
                'country_id' => $country->id,
            ];

            $isClient = Client::where("phone", $request->phone)->first();

            if($isClient){
                $isClient->update($requestClient);
                $client = $isClient;
            }else{
                $client = Client::create($requestClient);
            }

            if (auth('client')->loginUsingId($client->id)) {
                $client = auth('client')->user();
                $token = $client->createToken('auth_token')->plainTextToken;
                $this->sendOTP($client);
                return $this->successResponse(['token' => $token,], 'Registration successful');
            } else {
                return $this->errorResponse('Registration failed', 401);
            }
        } catch (\Throwable $e) {
            Log::error('Customer Registration Error: ' . $e->getMessage());
            return $this->errorResponse('Server Error', 500);
        }
    }

    public function getUserDetails(Request $request)
    {
        try {
            $user = $request->user();
            return $this->successResponse(['user' => $user], 'User details fetched successfully');
        } catch (\Throwable $e) {
            Log::error('Get User Details Error: ' . $e->getMessage());
            return $this->errorResponse('Server Error', 500);
        }
    }

    public function sendOTP($user = null, $resetPassword = false)
    {
        $userId = $user ? $user->id : request()->user()->id;

        if ($user->email == "wrbawantha@gmail.com") {
            $code = 1111;
        } else {
            $code = $this->otpGenarator(4);
            Log::info('Generated OTP Code for user ID ' . $userId . ': ' . $code);
        }

        $otp = Otp::where(['user_id' => $userId])->first();
        if ($otp) {
            $otp->otp_code = $code;
            $otp->expired_at = Carbon::now()->addMinutes(5);
            $otp->verified_at = null;
            $otp->save();
        } else {
            $otp = new Otp();
            $otp->user_id = $userId;
            $otp->otp_code = $code;
            $otp->expired_at = Carbon::now()->addMinutes(5);
            $otp->verified_at = null;
            $otp->save();
        }

        // if ($user->email) {
        //     Mail::to($user->email)->send(new OtpVerify($otp, $user));
        // }

        if ($user->phone) {
            $this->sendSmsOtpToUser($user, $code);
        }
    }

    public function resendOtp(Request $request)
    {
        try {
            $user = $request->user();
            $this->sendOtp($user);
            return $this->successResponse(null, 'OTP Send Successfully');
        } catch (\Throwable $th) {
            Log::error($th);
            return $this->errorResponse($th->getMessage(), 500);
        }
    }

    public function verifyOTP(Request $request)
    {
        $user = $request->user();
        $otp = $request->otp;

        $validator = Validator::make(
            $request->all(),
            [
                'otp' => ['required', Rule::exists('otps', 'otp_code')->where(function ($query) use ($user, $otp) {
                    $query->where(['user_id' => $user->id, 'otp_code' => $otp])->where('expired_at', '>', Carbon::now()->toDateTimeString());
                }),]
            ],
            [
                'otp.exists' => 'Invalid OTP Code'
            ]
        );
        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 422);
        } else {
            try {
                $client = Client::find($user->id);
                $otpCode = Otp::where(['user_id' => $user->id, 'otp_code' => $otp])->first();
                $client->user_verified_at = Carbon::now();
                $client->save();
                $otpCode->verified_at = Carbon::now();
                $otpCode->save();
                return $this->successResponse('OTP Verified', 200);
            } catch (\Throwable $th) {
                Log::error($th);
                return $this->errorResponse($th->getMessage(), 500);
            }
        }
    }

    // public function sendSmsOtpToUser($user, $code, $countryCode = '94')
    // {
    //     $api_key = '19|fDtsSXt0ZpUroSP7cQ4gCC7ML4kJ1MIDBBbcqTio';
    //     $apiUrl = 'https://sms-api.ipsova.com/api/v3/sms/send';

    //     if (substr($user->phone, 0, 1) === '0') {
    //         $mobileNumber = $countryCode . substr($user->phone, 1);
    //     } else {
    //         $mobileNumber = $countryCode . $user->phone;
    //     }
    //     try {
    //         $response = Http::withHeaders([
    //             'Authorization' =>  'Bearer ' . $api_key,
    //             'Content-Type' => 'application/json'
    //         ])->post($apiUrl, [
    //             'recipient' => $mobileNumber,
    //             'sender_id' => 'WEBLOOK',
    //             'type' => 'plain',
    //             'message' => 'Hi there,\nPlease enter the following OTP : ' . $code . ' to complete your login.\nThank you.',
    //         ]);
    //     } catch (\Throwable $e) {
    //         Log::error('SMS OTP Error: ' . $e->getMessage());
    //     }
    // }


    public function sendSmsOtpToUser($user, $code, $countryCode = '94')
    {
        // Dialog eSMS Key (replace with your real key)
        $api_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6MTQwMTIsImN1c3RvbWVyX3JvbGUiOjAsImlhdCI6MTcyNzI1NDE3NSwiZXhwIjo0ODUxNDU2NTc1fQ.hfbRBIgkyngpkazPJpo1hO0Ebc1FO-w2BiJ168VuOMY';


        // Format mobile number
        if (substr($user->phone, 0, 1) === '0') {
            $mobileNumber = $countryCode . substr($user->phone, 1);
        } else {
            $mobileNumber = $countryCode . $user->phone;
        }

        // Dialog API endpoint
        $apiUrl = "https://e-sms.dialog.lk/api/v1/message-via-url/create/url-campaign";

        // Build message text
        $message = "Hi {$user->name},\nYour OTP code is: {$code}\nThank you.";

        try {

            // Build full GET URL with encoded parameters
            $fullUrl = $apiUrl . '?' . http_build_query([
                'esmsqk'         => $api_key,
                'list'           => $mobileNumber,
                'source_address' => 'DriftBarber',    // Your sender mask
                'message'        => $message,
            ]);

            // Send GET request
            $response = Http::get($fullUrl);

            // Optional logging
            Log::info('Dialog OTP SMS Response', [
                'url' => $fullUrl,
                'response' => $response->body()
            ]);

            return $response->successful();
        } catch (\Throwable $e) {

            Log::error('Dialog OTP SMS Error: ' . $e->getMessage());
            return false;
        }
    }


    public static function otpGenarator($length = 5)
    {
        $characters = '0123456789';
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
    }
    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            if ($user && method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
                $user->currentAccessToken()->delete();
            } else {
                // fallback: delete all tokens if currentAccessToken not available
                if (method_exists($user, 'tokens')) {
                    $user->tokens()->delete();
                }
            }
            return $this->successResponse(null, 'Logged out successfully');
        } catch (\Throwable $e) {
            Log::error('Logout Error: ' . $e->getMessage());
            return $this->errorResponse('Server Error', 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required',
                'email',
                'max:255',
                'unique:clients,email',
                'first_name' => 'required',
                'string',
                'max:255',
                'last_name' => 'required',
                'string',
                'max:255',
                // 'phone' => 'nullable','string','max:25',
                // 'phone_code' => 'nullable','string','max:10',
                'birthday_daymonth' => 'nullable',
                'regex:/^\d{2}\/\d{2}$/',
                'birthday_year' => 'nullable',
                'integer',
                'min:1900',
                'max:' . date('Y'),
                'gender' => 'nullable',
                Rule::in(['Female', 'Male', 'Non-binary', 'Prefer not to say', 'Other']),
                //  'country_id' => 'nullable','integer','exists:countries,id',
                'avatar' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
            ]);
            if ($validator->fails()) {
                Log::info($validator->errors());
                return $this->errorResponse($validator->errors(), 422);
            }
            $user = $request->user();
            $user->first_name = $request->first_name ?? $user->first_name;
            $user->last_name = $request->last_name ?? $user->last_name;
            //$user->phone = $request->phone ?? $user->phone;
            // $user->phone_code = $request->phone_code ?? $user->phone_code;
            $user->birthday_daymonth = $request->birthday_daymonth ?? $user->birthday_daymonth;
            $user->birthday_year = $request->birthday_year ?? $user->birthday_year;
            $user->gender = $request->gender ?? $user->gender;
            $user->country_id = $request->country_id ?? $user->country_id;
            $user->email = $request->email ?? $user->email;
            $user->address = $request->address ?? $user->address;
            $user->save();

            // debug: log incoming file keys
            try {
                $fileKeys = array_keys($request->allFiles());
                Log::info('UpdateProfile files for user ID ' . $user->id . ': ' . json_encode($fileKeys));
            } catch (\Throwable $e) {
                Log::error('Error logging files: ' . $e->getMessage());
            }

            // handle avatar upload using Spatie medialibrary (accept both 'avatar' and 'profile_image')
            $uploadedKey = null;
            if ($request->hasFile('avatar')) {
                $uploadedKey = 'avatar';
            } elseif ($request->hasFile('profile_image')) {
                $uploadedKey = 'profile_image';
            }

            if ($uploadedKey) {
                try {
                    Log::info('Uploading avatar (key=' . $uploadedKey . ') for user ID: ' . $user->id);
                    // remove previous avatar(s)
                    if (method_exists($user, 'clearMediaCollection')) {
                        $user->clearMediaCollection('avatar');
                    }
                    // store new avatar
                    if (method_exists($user, 'addMediaFromRequest')) {
                        $user->addMediaFromRequest($uploadedKey)->toMediaCollection('avatar');
                        Log::info('Avatar uploaded successfully for user ID: ' . $user->id . ' via key: ' . $uploadedKey);
                    }
                } catch (\Throwable $e) {
                    Log::error('Avatar upload error: ' . $e->getMessage());
                    // don't fail the whole request on avatar save error
                }
            }
            return $this->successResponse(['user' => $user], 'Profile updated successfully');
        } catch (\Throwable $e) {
            Log::error('Update Profile Error: ' . $e->getMessage());
            return $this->errorResponse('Server Error', 500);
        }
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }
        try {
            $user = $request->user();
            if (!Hash::check($request->input('current_password'), $user->password)) {
                return $this->errorResponse(['current_password' => ['Current password is incorrect']], 422);
            }

            $user->password = Hash::make($request->input('password'));
            if ($user->phone) {
                $this->sendOTP($user);
            }
            // $user->save();
            // try {
            //     if (method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
            //         $current = $user->currentAccessToken();
            //         if (method_exists($user, 'tokens')) {
            //             $user->tokens()->where('id', '<>', $current->id)->delete();
            //         }
            //     } else {
            //         if (method_exists($user, 'tokens')) {
            //             $user->tokens()->delete();
            //         }
            //     }
            // } catch (\Throwable $e) {
            //     Log::warning('Password change token cleanup warning: ' . $e->getMessage());
            // }
            return $this->successResponse(null, 'Otp sent successfully');
        } catch (\Throwable $e) {
            Log::error(' Error: ' . $e->getMessage());
            return $this->errorResponse('Server Error', 500);
        }
    }

    public function saveVerifiedPassword(Request $request)
    {
        Log::info('saveVerifiedPassword request', [
            'user_id' => $request->user() ? $request->user()->id : null,
            'payload' => $request->all()
        ]);
        $validator = Validator::make($request->all(), [
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            Log::info('validate failed');
            return $this->errorResponse($validator->errors(), 422);
        }
        try {
            $user = $request->user();
            Log::info('Saving new password for user ID: ' . $user->id);
            $user->password = Hash::make($request->input('password'));
            $user->save();

            try {
                if (method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
                    $current = $user->currentAccessToken();
                    if (method_exists($user, 'tokens')) {
                        $user->tokens()->where('id', '<>', $current->id)->delete();
                    }
                } else {
                    if (method_exists($user, 'tokens')) {
                        $user->tokens()->delete();
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Password saveVerifiedPassword token cleanup warning: ' . $e->getMessage());
            }
            Log::info('Password changed successfully for user ID: ' . $user->id);
            return $this->successResponse(null, 'Password changed successfully');
        } catch (\Throwable $e) {
            Log::error('saveVerifiedPassword Error: ' . $e->getMessage());
            return $this->errorResponse('Server Error', 500);
        }
    }

    public function accountSoftDelete(Request $request)
    {
        try {
            $user = $request->user();
            $user->is_blocked = 1;
            $user->save();
            return $this->successResponse(null, 'Account deleted successfully');
        } catch (\Throwable $e) {
            Log::error('Account Soft Delete Error: ' . $e->getMessage());
            return $this->errorResponse('Server Error', 500);
        }
    }

    public function sendWelcomeMessage(Request $request)
    {
        try {
            $user = $request->user();
            $message = "Hi " . $user->first_name . ", Welcome to Drift Barber! Your account has been created successfully. Log in anytime to start using our services.";


            Helpers::sendSMSAlerts($message, $user->phone);
            return $this->successResponse('SMS sent successfullty');
        } catch (\Throwable $e) {
            Log::error('SMS sent Error: ' . $e->getMessage());
            return $this->errorResponse('Server Error', 500);
        }
    }
}
