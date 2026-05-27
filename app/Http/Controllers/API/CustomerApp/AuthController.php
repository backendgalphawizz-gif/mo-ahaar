<?php

namespace App\Http\Controllers\API\CustomerApp;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\Customers;
use App\Models\StaticPage;
use App\Models\StoreSetting;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Customer auth.
 *
 * DB columns (different meanings — both are stored on `users`):
 * - `role_type` (int): application role for routing & permissions in this codebase
 *   (1 = Admin, 2 = Vendor, 3 = Customer / customer-app user). Not the same as retailer vs wholesaler.
 * - `user_type` (enum): business segment for customer accounts only — `Retailer` or `Wholesaler`
 *   (matches `users.user_type` in MySQL). Always set on signup from `user_role` / `user_type` in the request.
 *
 * Signup accepts segment as:
 * - `user_type`: `Retailer` / `Wholesaler` (preferred when both `user_role` and `user_type` are sent)
 * - `user_role` / `role` as strings: `retailer`, `wholesaler`, etc.
 * - `user_role` / `role` as integers: `1` = retailer, `2` = wholesaler
 */
class AuthController extends Controller
{
    private const SIGNUP_OTP_CACHE_PREFIX = 'customer_signup_otp_';

    private const SIGNUP_OTP_TTL_MINUTES = 5;

    /** Application role: customer-app API users (see also Vendor=2, Admin=1). */
    private const CUSTOMER_ROLE_TYPE = 2;

    /** Signup payload segment (lowercase); persisted as `users.user_type` enum. */
    private const CUSTOMER_TYPES = ['retailer', 'wholesaler'];

    /** Maps API segment → DB enum `users.user_type`. */
    private const USER_TYPE_BY_ROLE = [
        'retailer' => 'Retailer',
        'wholesaler' => 'Wholesaler',
    ];

    public function login(Request $request)
    {
        return $this->requestOtp($request);
    }

    /**
     * Public content for registration screen: privacy policy, terms, and FAQ.
     */
    public function registrationContent()
    {
        $visibility = $this->customerRegistrationPageVisibility();
        $slugMap = [
            'privacy_policy' => 'privacy-policy',
            'terms_and_conditions' => 'terms-and-conditions',
            'faq' => 'faqs',
        ];

        $rows = StaticPage::whereIn('slug', array_values($slugMap))
            ->where('status', 1)
            ->get()
            ->keyBy('slug');

        $content = [];
        foreach ($slugMap as $key => $slug) {
            if (!($visibility[$key] ?? true)) {
                continue;
            }

            $page = $rows->get($slug);
            if (!$page) {
                continue;
            }

            $content[$key] = [
                'slug' => $page->slug,
                'title' => $page->title,
                'content' => $page->content,
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Registration content retrieved successfully',
            'data' => $content,
        ], 200);
    }

    /**
     * POST /api/customer-app/auth/signup
     * Register a customer account (retailer/wholesaler).
     */
    public function signup(Request $request)
    {
        $this->prepareCustomerSignupPayload($request);

        $validated = $request->validate([
            // 'user_role' => ['required', 'string', Rule::in(self::CUSTOMER_TYPES)],
            'name' => ['required', 'string', 'max:100'],
            'mobile' => [
                'required',
                'digits:10',
                'regex:/^[6-9][0-9]{9}$/',
                // Rule::unique('users', 'mobile')->where(fn ($query) => $query->where('role_type', self::CUSTOMER_ROLE_TYPE)),
            ],
            'email' => [
                'nullable',
                'email',
                'max:150',
                // Rule::unique('users', 'email')->where(fn ($query) => $query->where('role_type', self::CUSTOMER_ROLE_TYPE))->whereNotNull('email'),
            ],
            'address' => ['nullable', 'string', 'max:65535'],
            // 'gst_number' => [
            //     'nullable',
            //     'string',
            //     'max:30',
            //     Rule::requiredIf(fn () => $request->input('user_role') === 'wholesaler'),
            // ],
            // 'company_name' => [
            //     'nullable',
            //     'string',
            //     'max:255',
            //     Rule::requiredIf(fn () => $request->input('user_role') === 'wholesaler'
            //         && Schema::hasColumn('users', 'company_name')),
            // ],
            'gender' => ['nullable', Rule::in(['Male', 'Female', 'Other'])],
            // 'accept_terms' => ['required', 'accepted'],
            'fcm_id' => ['nullable', 'string', 'max:255'],
        ], [
            'mobile.regex' => 'Enter a valid 10-digit mobile number starting with 6-9.',
            // 'user_role.required' => 'User role is required (retailer or wholesaler).',
            // 'user_role.in' => 'User role must be retailer or wholesaler.',
            // 'gst_number.required' => 'GST number is mandatory for wholesaler accounts.',
            // 'company_name.required' => 'Company name is mandatory for wholesaler accounts.',
            // 'accept_terms.required' => 'You must accept the terms and conditions.',
            // 'accept_terms.accepted' => 'You must accept the terms and conditions.',
        ]);

        // $userType = self::USER_TYPE_BY_ROLE[$validated['user_role']] ?? null;
        // if (!$userType || !Schema::hasColumn('users', 'user_type')) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'User account schema is missing user_type. Run migrations or update the database.',
        //     ], 500);
        // }

        try {
            DB::beginTransaction();

            // $gstNormalized = null;
            // if (!empty($validated['gst_number']) && is_string($validated['gst_number'])) {
            //     $t = trim($validated['gst_number']);
            //     $gstNormalized = $t !== '' ? strtoupper($t) : null;
            // }

            // Retailers are auto-approved; wholesalers require admin approval.
            // $isRetailer = $validated['user_role'] === 'retailer';

            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'mobile' => $validated['mobile'],
                'password' => Hash::make(Str::random(16)),
                // System role (customer app); do not use this field for Retailer vs Wholesaler.
                // 'role_type' => self::CUSTOMER_ROLE_TYPE,
                // Segment: Retailer | Wholesaler — separate from role_type.
                // 'user_type' => $userType,
                // Retailers are active immediately; wholesalers remain inactive until admin approves.
                'status' => 1,
            ];

            // if (Schema::hasColumn('users', 'approval_status')) {
            //     $userData['approval_status'] = $isRetailer ? 'approved' : 'pending';
            // }

            // if (Schema::hasColumn('users', 'accept_terms')) {
            //     $userData['accept_terms'] = true;
            //     $userData['terms_accepted_at'] = now();
            // }

            // if (Schema::hasColumn('users', 'gst_number')) {
            //     $userData['gst_number'] = $gstNormalized;
            // }

            // if (Schema::hasColumn('users', 'company_name')) {
            //     $cn = $validated['company_name'] ?? null;
            //     $userData['company_name'] = is_string($cn) && trim($cn) !== '' ? trim($cn) : null;
            // }

            if (Schema::hasColumn('users', 'fcm_id') && !empty($validated['fcm_id'])) {
                $userData['fcm_id'] = $validated['fcm_id'];
            }

            $user = Users::create($userData);

            // customers.gender is NOT NULL in DB — default when omitted
            $gender = $validated['gender'] ?? 'Other';

            $customer = Customers::create([
                'user_id' => $user->user_id,
                'customer_address' => $validated['address'],
                'gender' => $gender,
            ]);

            $customer->addresses()->create([
                'contact_name' => $user->name,
                'mobile' => $user->mobile,
                'address_line' => $validated['address'],
                'address_type' => 'other',
                'is_default' => true,
            ]);

            DB::commit();

            $token = $user->createToken('customer-app-token')->plainTextToken;

            // $gstStored = Schema::hasColumn('users', 'gst_number')
            //     ? $user->gst_number
            //     : $gstNormalized;

            return response()->json([
                'status' => true,
                'message' => 'Registration successful. Please log in to continue.',
                // 'message' => $isRetailer
                //     ? 'Registration successful. Your account is active.'
                //     : 'Registration submitted. Your account is pending admin approval.',
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'user_id' => $user->user_id,
                    'customer_id' => $customer->customer_id,
                    'name' => $user->name,
                    'mobile' => $user->mobile,
                    'email' => $user->email ?? null,
                    // user_type = DB enum (Retailer/Wholesaler); role_type = customer app role constant.
                    // 'user_type' => $user->user_type,
                    // 'user_role' => $validated['user_role'],
                    // 'role_type' => $user->role_type,
                    'account_status' => $user->customerAccountApprovalLabel(),
                    'can_place_orders' => $user->canPlaceOrdersAsCustomer(),
                    'gender' => $customer->gender,
                    'address' => $customer->customer_address,
                    'default_shipping_address' => $this->transformAddress($customer->defaultAddress()->first()),
                    // 'gst_number' => $gstStored,
                    // 'company_name' => Schema::hasColumn('users', 'company_name')
                    //     ? $user->company_name
                    //     : null,
                    'accept_terms' => (bool) $user->accept_terms,
                    'terms_accepted_at' => $user->terms_accepted_at?->toISOString(),
                ],
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Unable to create customer account right now. Please try again.',
            ], 500);
        }
    }

    /**
     * Accept common client field names and shapes (case, plurals, numeric codes, alternate keys).
     */
    private function prepareCustomerSignupPayload(Request $request): void
    {
        $normalizedRole = null;

        // Prefer explicit user_type (e.g. "Retailer") so it wins over a numeric user_role code when both are sent.
        if ($request->filled('user_type')) {
            $normalizedRole = $this->normalizeSignupUserRole($request->input('user_type'));
        }

        if ($normalizedRole === null) {
            foreach (['user_role', 'role', 'customer_type'] as $key) {
                $val = $request->input($key);
                if ($val === null || $val === '') {
                    continue;
                }
                $normalizedRole = $this->normalizeSignupUserRole($val);
                if ($normalizedRole !== null) {
                    break;
                }
            }
        }

        if ($normalizedRole !== null) {
            $request->merge(['user_role' => $normalizedRole]);
        }

        if (!$request->filled('gst_number')) {
            foreach (['gst', 'gst_no', 'GSTNumber', 'gstNumber'] as $key) {
                if ($request->filled($key)) {
                    $request->merge(['gst_number' => trim((string) $request->input($key))]);
                    break;
                }
            }
        } elseif (is_string($request->input('gst_number'))) {
            $request->merge(['gst_number' => trim($request->input('gst_number'))]);
        }

        if (!$request->filled('company_name')) {
            foreach (['companyName', 'business_name', 'company'] as $key) {
                if ($request->filled($key)) {
                    $request->merge(['company_name' => trim((string) $request->input($key))]);
                    break;
                }
            }
        } elseif (is_string($request->input('company_name'))) {
            $request->merge(['company_name' => trim($request->input('company_name'))]);
        }
    }

    private function normalizeSignupUserRole(mixed $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        // Integer / numeric string codes from mobile clients: 1 = retailer, 2 = wholesaler
        if (is_numeric($raw)) {
            $n = (int) $raw;
            if ($n === 1) {
                return 'retailer';
            }
            if ($n === 2) {
                return 'wholesaler';
            }

            return null;
        }

        $s = strtolower(trim((string) $raw));
        $s = preg_replace('/\s+/', ' ', $s) ?? $s;
        $s = rtrim($s, 's');

        if (in_array($s, ['retailer', 'retail'], true)) {
            return 'retailer';
        }

        if (in_array($s, ['wholesaler', 'wholesale'], true)) {
            return 'wholesaler';
        }

        return null;
    }

    /**
     * @return array{privacy_policy: bool, terms_and_conditions: bool, faq: bool}
     */
    private function customerRegistrationPageVisibility(): array
    {
        $defaults = [
            'privacy_policy' => true,
            'terms_and_conditions' => true,
            'faq' => true,
        ];

        if (!Schema::hasTable('store_settings')) {
            return $defaults;
        }

        $setting = StoreSetting::first();
        if (!$setting) {
            return $defaults;
        }

        $columnMap = [
            'privacy_policy' => 'customer_registration_privacy_policy_enabled',
            'terms_and_conditions' => 'customer_registration_terms_enabled',
            'faq' => 'customer_registration_faq_enabled',
        ];

        $out = [];
        foreach ($columnMap as $key => $column) {
            if (!Schema::hasColumn('store_settings', $column)) {
                $out[$key] = true;
            } else {
                $out[$key] = (bool) $setting->{$column};
            }
        }

        return $out;
    }

    public function verify(Request $request)
    {
        return $this->verifyOtp($request);
    }

    /**
     * Request OTP for new-customer signup mobile verification.
     */
    public function requestSignupOtp(Request $request)
    {
        $validated = $request->validate([
            'mobile' => ['required', 'digits:10', 'regex:/^[6-9][0-9]{9}$/'],
        ]);

        $mobile = $validated['mobile'];

        if (Users::where('mobile', $mobile)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'This mobile number is already registered.',
            ], 422);
        }

        $otp = (string) random_int(1000, 9999);
        $otpData = [
            'otp' => $otp,
            'expires_at' => now()->addMinutes(self::SIGNUP_OTP_TTL_MINUTES)->toISOString(),
        ];

        Cache::put($this->signupOtpCacheKey($mobile), $otpData, now()->addMinutes(self::SIGNUP_OTP_TTL_MINUTES));

        return response()->json([
            'status' => true,
            'message' => 'Signup OTP generated successfully',
            'otp' => $otp,
            'otp_expires_at' => $otpData['expires_at'],
        ], 200);
    }

    /**
     * Verify OTP for new-customer signup mobile verification.
     */
    public function verifySignupOtp(Request $request)
    {
        $validated = $request->validate([
            'mobile' => ['required', 'digits:10', 'regex:/^[6-9][0-9]{9}$/'],
            'otp' => ['required', 'digits:4'],
        ]);

        $mobile = $validated['mobile'];
        $otp = $validated['otp'];

        if (!$this->isValidSignupOtp($mobile, $otp)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or expired OTP. Please request a new OTP.',
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'Mobile number verified successfully',
        ], 200);
    }

    public function requestOtp(Request $request)
    {
        $validated = $request->validate([
            'mobile' => ['required', 'regex:/^[0-9]{10}$/'],
        ]);

        $mobile = $validated['mobile'];

        if (Users::where('mobile', $mobile)->where('role_type', '!=', self::CUSTOMER_ROLE_TYPE)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'This mobile number is not registered for the customer app.',
            ], 422);
        }

        $user = Users::where('mobile', $mobile)
            ->where('role_type', self::CUSTOMER_ROLE_TYPE)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'No customer account found for this mobile. Please complete registration first.',
            ], 422);
        }

        if (Schema::hasTable('customers')) {
            Customers::firstOrCreate(
                ['user_id' => $user->user_id],
                [
                    'customer_address' => null,
                    'gender' => 'Other',
                ]
            );
        }

        $otp = (string) random_int(1000, 9999);

        $user->login_otp = $otp;
        $user->login_otp_expires_at = now()->addMinutes(5);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'OTP generated successfully',
            'otp' => $otp,
            'otp_expires_at' => $user->login_otp_expires_at,
        ], 200);
    }

    public function logout(Request $request)
    {
        /** @var Users $user */
        $user = $request->user();

        if (!$user || (int) $user->role_type !== self::CUSTOMER_ROLE_TYPE) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized customer access',
            ], 403);
        }

        $validated = $request->validate([
            'revoke_all' => ['sometimes', 'boolean'],
        ]);

        $revokeAll = (bool) ($validated['revoke_all'] ?? false);

        if ($revokeAll) {
            $user->tokens()->delete();

            return response()->json([
                'status'  => true,
                'message' => 'Logged out from all devices successfully',
            ], 200);
        }

        $token = $user->currentAccessToken();

        if ($token) {
            $token->delete();
        } else {
            // Fallback safety for requests authenticated without a current token object.
            $user->tokens()->delete();
        }

        return response()->json([
            'status'  => true,
            'message' => 'Logged out successfully',
        ], 200);
    }

    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'mobile' => ['required', 'regex:/^[0-9]{10}$/'],
            'otp' => ['required', 'digits:4'],
            'fcm_id' => ['nullable', 'string', 'max:255'],
        ]);

        $user = Users::where('mobile', $validated['mobile'])
            ->where('role_type', self::CUSTOMER_ROLE_TYPE)
            ->first();

        if (!$user || !$user->login_otp || !$user->login_otp_expires_at) {
            return response()->json([
                'status' => false,
                'message' => 'OTP not requested for this mobile number',
            ], 422);
        }

        if (now()->gt($user->login_otp_expires_at)) {
            return response()->json([
                'status' => false,
                'message' => 'OTP has expired. Please request a new OTP',
            ], 422);
        }

        if ($user->login_otp !== $validated['otp']) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid OTP',
            ], 422);
        }

        $user->login_otp = null;
        $user->login_otp_expires_at = null;

        if (Schema::hasColumn('users', 'fcm_id') && !empty($validated['fcm_id'])) {
            $user->fcm_id = $validated['fcm_id'];
        }

        $user->save();

        $token = $user->createToken('customer-app-token')->plainTextToken;

        $customer = Customers::with([
            'addresses' => function ($query) {
                $query->orderByDesc('is_default')->orderByDesc('updated_at')->orderByDesc('customer_address_id');
            },
            'defaultAddress',
        ])->where('user_id', $user->user_id)->first();

        $profileImageUrl = null;
        if (!empty($user->profile_image)) {
            $profileImageUrl = url('public/uploads/customers/' . $user->profile_image);
        }

        $isWholesaler = Schema::hasColumn('users', 'user_type')
            && strcasecmp((string) ($user->user_type ?? ''), 'Wholesaler') === 0;

        $personalInformation = [
            'user_id'            => $user->user_id,
            'name'               => $user->name,
            'role_type'          => $user->role_type,
            'user_type'          => $user->user_type,
            'gst_number'         => Schema::hasColumn('users', 'gst_number') ? $user->gst_number : null,
            'account_status'     => $user->customerAccountApprovalLabel(),
            'can_place_orders'   => $user->canPlaceOrdersAsCustomer(),
            'preferred_language' => $user->preferred_language ?: config('app.locale'),
            'dob'                => $customer?->dob,
            'gender'             => $customer?->gender,
            'profile_photo'      => $user->profile_image,
            'profile_photo_url'  => $profileImageUrl,
            'fcm_id'             => Schema::hasColumn('users', 'fcm_id') ? $user->fcm_id : null,
        ];

        if ($isWholesaler && Schema::hasColumn('users', 'company_name')) {
            $personalInformation['company_name'] = $user->company_name;
        }

        return response()->json([
            'status'     => true,
            'message'    => 'Login successful',
            'token'      => $token,
            'token_type' => 'Bearer',
            'profile'    => [
                'personal_information' => $personalInformation,
                'contact_details'      => [
                    'email'            => $user->email,
                    'mobile'           => $user->mobile,
                    'customer_address' => $customer?->customer_address,
                    'default_shipping_address' => $customer?->defaultAddress
                        ? $this->transformAddress($customer->defaultAddress)
                        : null,
                    'shipping_addresses' => $customer?->addresses
                        ? $customer->addresses->map(fn (CustomerAddress $address) => $this->transformAddress($address))->values()->all()
                        : [],
                ],
            ],
        ], 200);
    }

    private function transformAddress(?CustomerAddress $address): ?array
    {
        if (!$address) {
            return null;
        }

        return [
            'customer_address_id' => $address->customer_address_id,
            'contact_name' => $address->contact_name,
            'mobile' => $address->mobile,
            'address_line' => $address->address_line,
            'landmark' => $address->landmark,
            'city' => $address->city,
            'state' => $address->state,
            'country' => $address->country,
            'pincode' => $address->pincode,
            'address_type' => $address->address_type,
            'is_default' => (bool) $address->is_default,
            'formatted_address' => $address->formattedAddress(),
        ];
    }

    private function signupOtpCacheKey(string $mobile): string
    {
        return self::SIGNUP_OTP_CACHE_PREFIX . $mobile;
    }

    private function isValidSignupOtp(string $mobile, string $otp): bool
    {
        if ($mobile === '' || $otp === '') {
            return false;
        }

        $otpData = Cache::get($this->signupOtpCacheKey($mobile));
        if (!is_array($otpData) || !isset($otpData['otp'])) {
            return false;
        }

        return hash_equals((string) $otpData['otp'], $otp);
    }

    private function clearSignupOtp(string $mobile): void
    {
        Cache::forget($this->signupOtpCacheKey($mobile));
    }
}
