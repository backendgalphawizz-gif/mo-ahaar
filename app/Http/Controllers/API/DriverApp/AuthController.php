<?php

namespace App\Http\Controllers\API\DriverApp;

use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends DriverAppController
{
    private const RESET_TOKEN_CACHE_PREFIX = 'driver_reset_token_';

    private const OTP_RESEND_SECONDS = 30;

    private const OTP_EXPIRY_MINUTES = 5;

    private const RESET_TOKEN_MINUTES = 15;

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:150'],
            'password' => ['required', 'string', 'min:8'],
            'fcm_id' => ['nullable', 'string', 'max:255'],
        ]);

        $user = Users::where('email', $validated['email'])
            ->where('role_type', self::DRIVER_ROLE_TYPE)
            ->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid email or password',
            ], 401);
        }

        if ($restriction = $user->apiAccessRestriction()) {
            return response()->json([
                'status' => false,
                'message' => $restriction['message'],
                'data' => [
                    'account_status' => $restriction['account_status'],
                    'force_logout' => $restriction['force_logout'],
                ],
            ], $restriction['http_status']);
        }

        if (!empty($validated['fcm_id'])) {
            $user->fcm_id = $validated['fcm_id'];
            $user->save();
        }

        $token = $user->createToken('driver-app-token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful! Welcome back',
            'token' => $token,
            'token_type' => 'Bearer',
            'data' => [
                'driver' => $this->driverProfilePayload($user),
            ],
        ], 200);
    }

    public function requestForgotPasswordOtp(Request $request)
    {
        return $this->sendPasswordResetOtp($request, false);
    }

    public function resendForgotPasswordOtp(Request $request)
    {
        return $this->sendPasswordResetOtp($request, true);
    }

    public function verifyForgotPasswordOtp(Request $request)
    {
        $validated = $request->validate([
            'mobile' => ['required', 'digits:10', 'regex:/^[6-9][0-9]{9}$/'],
            'otp' => ['required', 'digits:4'],
        ]);

        $user = $this->findDriverByMobile($validated['mobile']);
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'No driver account found for this mobile number.',
            ], 422);
        }

        if (!$this->otpMatches($user, $validated['otp'])) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or expired OTP. Please request a new OTP and try again',
                'errors' => ['otp' => ['Invalid or expired OTP.']],
            ], 422);
        }

        $user->password_reset_otp = null;
        $user->password_reset_otp_expires_at = null;
        $user->save();

        $resetToken = Str::random(64);
        Cache::put(
            self::RESET_TOKEN_CACHE_PREFIX . $resetToken,
            $user->user_id,
            now()->addMinutes(self::RESET_TOKEN_MINUTES)
        );

        return response()->json([
            'status' => true,
            'message' => 'OTP verified successfully! Redirecting to dashboard',
            'data' => [
                'reset_token' => $resetToken,
                'reset_token_expires_at' => now()->addMinutes(self::RESET_TOKEN_MINUTES)->toIso8601String(),
            ],
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'reset_token' => ['required', 'string'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        $userId = Cache::get(self::RESET_TOKEN_CACHE_PREFIX . $validated['reset_token']);
        if (!$userId) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or expired reset link. Please request a new password reset',
            ], 422);
        }

        $user = Users::where('user_id', $userId)
            ->where('role_type', self::DRIVER_ROLE_TYPE)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Driver account not found. Please contact support',
            ], 404);
        }

        $user->password = $validated['password'];
        $user->save();

        Cache::forget(self::RESET_TOKEN_CACHE_PREFIX . $validated['reset_token']);

        $token = $user->createToken('driver-app-token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Password created successfully! You can now log in with your new password',
            'token' => $token,
            'token_type' => 'Bearer',
            'data' => [
                'driver' => $this->driverProfilePayload($user),
            ],
        ], 200);
    }

    public function logout(Request $request)
    {
        $user = $this->resolveDriver($request);
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Session expired. Please log in again',
            ], 403);
        }

        $validated = $request->validate([
            'revoke_all' => ['sometimes', 'boolean'],
        ]);

        if ((bool) ($validated['revoke_all'] ?? false)) {
            $user->tokens()->delete();
        } else {
            $token = $user->currentAccessToken();
            if ($token) {
                $token->delete();
            } else {
                $user->tokens()->delete();
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'You have been logged out successfully',
        ], 200);
    }

    private function sendPasswordResetOtp(Request $request, bool $enforceResendCooldown)
    {
        $validated = $request->validate([
            'country_code' => ['nullable', 'regex:/^\+\d{1,4}$/'],
            'mobile' => ['required', 'digits:10', 'regex:/^[6-9][0-9]{9}$/'],
        ]);

        $mobile = $validated['mobile'];
        $rateKey = 'driver-otp:' . $mobile;

        if (RateLimiter::tooManyAttempts($rateKey, 3)) {
            $seconds = RateLimiter::availableIn($rateKey);

            return response()->json([
                'status' => false,
                'message' => 'Too many OTP requests. Please wait a few minutes before trying again',
            ], 429)->header('Retry-After', (string) $seconds);
        }

        if ($enforceResendCooldown) {
            $lastSentKey = 'driver_otp_last_sent_' . $mobile;
            $lastSent = Cache::get($lastSentKey);
            if ($lastSent && now()->diffInSeconds($lastSent) < self::OTP_RESEND_SECONDS) {
                $wait = self::OTP_RESEND_SECONDS - now()->diffInSeconds($lastSent);

                return response()->json([
                    'status' => false,
                    'message' => "Please wait {$wait} seconds before requesting a new OTP",
                    'data' => ['resend_after_seconds' => $wait],
                ], 429);
            }
        }

        $user = $this->findDriverByMobile($mobile);
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'No driver account found for this mobile number.',
            ], 422);
        }

        $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(self::OTP_EXPIRY_MINUTES);

        $user->password_reset_otp = $otp;
        $user->password_reset_otp_expires_at = $expiresAt;
        $user->save();

        RateLimiter::hit($rateKey, 600);
        Cache::put('driver_otp_last_sent_' . $mobile, now(), now()->addMinutes(10));

        $payload = [
            'status' => true,
            'message' => 'OTP sent successfully to your mobile number. Please check your messages',
            'data' => [
                'masked_mobile' => $this->maskMobile($mobile),
                'otp_expires_at' => $expiresAt->toIso8601String(),
                'resend_after_seconds' => self::OTP_RESEND_SECONDS,
            ],
        ];

        if (config('app.debug')) {
            $payload['data']['otp'] = $otp;
        }

        return response()->json($payload, 200);
    }

    private function findDriverByMobile(string $mobile): ?Users
    {
        return Users::where('mobile', $mobile)
            ->where('role_type', self::DRIVER_ROLE_TYPE)
            ->first();
    }

    private function otpMatches(Users $user, string $otp): bool
    {
        if (!$user->password_reset_otp || !$user->password_reset_otp_expires_at) {
            return false;
        }

        if (now()->isAfter($user->password_reset_otp_expires_at)) {
            return false;
        }

        return (string) $user->password_reset_otp === (string) $otp;
    }
}
