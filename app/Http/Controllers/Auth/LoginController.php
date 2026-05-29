<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Users;
use App\Models\Usersrole;
use App\Models\Vendor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    private const VENDOR_ROLE_TYPE = 3;

    private const VENDOR_OTP_EXPIRY_MINUTES = 5;

    private const VENDOR_OTP_RESEND_SECONDS = 60;

    public function index()
    {
        $user_id = session()->get('user_id');
        if ($user_id) {
            $userTypeId = session()->get('role_type');
            $redirect = redirect('/');
            switch ($userTypeId) {
                case 1:
                    $redirect = redirect('/admin/dashboard');
                    break;
                case 3:
                    $redirect = redirect('/vendor/dashboard');
                    break;
                case 2:
                    $redirect = redirect('/user/dashboard');
                    break;
            }
            return $redirect;
        }
        return view('auth.login', [
            'loginTitle' => 'Admin Login',
            'formAction' => url('/checkLogin'),
        ]);
    }

    public function vendorLoginForm()
    {
        $user_id = session()->get('user_id');
        if ($user_id) {
            $userTypeId = session()->get('role_type');
            if ((int) $userTypeId === self::VENDOR_ROLE_TYPE) {
                return redirect('/vendor/dashboard');
            }
            if ((int) $userTypeId === 1) {
                return redirect('/admin/dashboard');
            }
        }

        return view('auth.vendor-login');
    }

    public function sendVendorOtp(Request $request)
    {
        $validated = $request->validate([
            'mobile' => ['required', 'regex:/^[6-9][0-9]{9}$/'],
        ], [
            'mobile.required' => 'Please enter your mobile number.',
            'mobile.regex' => 'Please enter a valid 10-digit mobile number.',
        ]);

        $mobile = $validated['mobile'];

        $user = Users::where('mobile', $mobile)
            ->where('role_type', self::VENDOR_ROLE_TYPE)
            ->first();

        if (!$user) {
            return back()
                ->withInput()
                ->withErrors(['mobile' => 'No vendor account found for this mobile number. Please register first.']);
        }

        if ((string) $user->status !== '1') {
            return back()
                ->withInput()
                ->withErrors(['mobile' => 'Your vendor account is pending admin approval. Please wait for activation.']);
        }

        $vendorAccount = Vendor::where('user_id', $user->user_id)->first();
        if (!$vendorAccount) {
            return back()
                ->withInput()
                ->withErrors(['mobile' => 'Vendor profile not found. Please contact support.']);
        }

        if (strtolower((string) $vendorAccount->approval_status) !== 'approved') {
            return back()
                ->withInput()
                ->withErrors(['mobile' => 'Your vendor account is pending admin approval. Please wait for activation.']);
        }

        $lastSentKey = $this->vendorOtpLastSentCacheKey($mobile);
        $lastSent = Cache::get($lastSentKey);
        if ($lastSent && now()->diffInSeconds($lastSent) < self::VENDOR_OTP_RESEND_SECONDS) {
            $wait = self::VENDOR_OTP_RESEND_SECONDS - now()->diffInSeconds($lastSent);

            return redirect()
                ->route('vendor.login.verify')
                ->with('error', "Please wait {$wait} seconds before requesting a new OTP.");
        }

        $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $user->login_otp = $otp;
        $user->login_otp_expires_at = now()->addMinutes(self::VENDOR_OTP_EXPIRY_MINUTES);
        $user->save();

        Cache::put($lastSentKey, now(), now()->addMinutes(10));

        Log::info('Vendor login OTP generated', ['mobile' => $mobile, 'user_id' => $user->user_id]);

        session([
            'vendor_login_mobile_raw' => $mobile,
            'vendor_login_mobile' => $this->maskMobile($mobile),
        ]);

        $redirect = redirect()
            ->route('vendor.login.verify')
            ->with('success', 'OTP sent to your mobile number.');

        if (config('app.debug')) {
            $redirect->with('dev_otp', $otp);
        }

        return $redirect;
    }

    public function vendorVerifyOtpForm()
    {
        if (!session()->has('vendor_login_mobile_raw')) {
            return redirect()->route('vendor.login')->with('error', 'Please enter your mobile number first.');
        }

        $mobile = (string) session('vendor_login_mobile_raw');
        $lastSent = Cache::get($this->vendorOtpLastSentCacheKey($mobile));
        $resendAfter = 0;

        if ($lastSent) {
            $elapsed = now()->diffInSeconds($lastSent);
            if ($elapsed < self::VENDOR_OTP_RESEND_SECONDS) {
                $resendAfter = self::VENDOR_OTP_RESEND_SECONDS - $elapsed;
            }
        }

        return view('auth.vendor-verify-otp', [
            'maskedMobile' => session('vendor_login_mobile'),
            'resendAfterSeconds' => $resendAfter,
        ]);
    }

    public function verifyVendorOtp(Request $request)
    {
        $sessionMobile = (string) session('vendor_login_mobile_raw', '');
        if ($sessionMobile === '') {
            return redirect()->route('vendor.login')->with('error', 'Session expired. Please enter your mobile again.');
        }

        $validated = $request->validate([
            'mobile' => ['required', 'regex:/^[6-9][0-9]{9}$/'],
            'otp' => ['required', 'digits:4'],
        ], [
            'mobile.required' => 'Mobile number is required.',
            'mobile.regex' => 'Please enter a valid mobile number.',
            'otp.required' => 'Please enter the OTP.',
            'otp.digits' => 'OTP must be 4 digits.',
        ]);

        if ($validated['mobile'] !== $sessionMobile) {
            return back()->withErrors(['otp' => 'Mobile number mismatch. Please start again.']);
        }

        $user = Users::where('mobile', $sessionMobile)
            ->where('role_type', self::VENDOR_ROLE_TYPE)
            ->first();

        if (!$user || !$user->login_otp || !$user->login_otp_expires_at) {
            return back()->withErrors(['otp' => 'OTP not requested. Please resend OTP.']);
        }

        if (now()->gt($user->login_otp_expires_at)) {
            return back()->withErrors(['otp' => 'OTP has expired. Please resend OTP.']);
        }

        if (!hash_equals((string) $user->login_otp, (string) $validated['otp'])) {
            return back()->withErrors(['otp' => 'Invalid OTP. Please try again.']);
        }

        $user->login_otp = null;
        $user->login_otp_expires_at = null;
        $user->save();

        $vendor = Vendor::where('user_id', $user->user_id)->first();
        if (!$vendor) {
            return redirect()->route('vendor.login')->with('error', 'Vendor profile not found.');
        }

        session([
            'user_id' => $user->user_id,
            'role_type' => $user->role_type,
            'name' => $user->name,
            'profile_image' => $user->profile_image ?? null,
            'vendor_id' => $vendor->vendor_id,
            'vendor_profile_image' => $vendor->profile_image,
        ]);

        session()->forget(['vendor_login_mobile_raw', 'vendor_login_mobile', 'dev_otp']);

        return redirect('/vendor/dashboard');
    }

    public function resendVendorOtp(Request $request)
    {
        $mobile = (string) session('vendor_login_mobile_raw', '');
        if ($mobile === '') {
            return redirect()->route('vendor.login')->with('error', 'Please enter your mobile number first.');
        }

        $request->merge(['mobile' => $mobile]);

        return $this->sendVendorOtp($request);
    }

    public function checkLogin(Request $request)
    {
        return $this->handleLogin($request, 'admin');
    }

    protected function handleLogin(Request $request, $loginType = 'admin')
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $email = $request->email;
        $password = $request->password;

        $existingUser = Users::where('email', $email)->first();

        if ($loginType === 'vendor' && $existingUser && (int) $existingUser->role_type === 3 && Hash::check($password, $existingUser->password)) {
            if ((string) $existingUser->status !== '1') {
                Session::flash('error', 'Your vendor account is pending admin approval. Please wait for activation.');
                return redirect('/vendor/login')->withInput($request->only('email'));
            }

            $vendorAccount = Vendor::where('user_id', $existingUser->user_id)->first();
            if ($vendorAccount && strtolower((string) $vendorAccount->approval_status) !== 'approved') {
                Session::flash('error', 'Your vendor account is pending admin approval. Please wait for activation.');
                return redirect('/vendor/login')->withInput($request->only('email'));
            }
        }

        $userData = Users::where('email', $email)
            ->where('status', '1')
            ->first();

        if (!$userData || !in_array((int) $userData->role_type, [1, 3], true) || !Hash::check($password, $userData->password)) {
            Session::flash('error', 'You entered wrong email or password!');
            
            // Redirect to the appropriate login form based on login type
            if ($loginType === 'vendor') {
                return redirect('/vendor/login')->withInput($request->only('email'));
            }
            return redirect('/')->withInput($request->only('email'));
        }

        // Additional check: if vendor login is attempted, ensure user is a vendor
        if ($loginType === 'vendor' && (int) $userData->role_type !== 3) {
            Session::flash('error', 'Only vendors can access this portal. Please use admin login.');
            return redirect('/vendor/login')->withInput($request->only('email'));
        }

        // Additional check: if admin login is attempted, ensure user is an admin
        if ($loginType === 'admin' && (int) $userData->role_type !== 1) {
            Session::flash('error', 'This account is not authorized for admin access. Please use vendor login.');
            return redirect('/')->withInput($request->only('email'));
        }

        $sessData = [
            'user_id' => $userData->user_id,
            'role_type' => $userData->role_type,
            'name' => $userData->name,
            'profile_image' => $userData->profile_image ?? null,
        ];

        if ((int) $userData->role_type === 3) {
            $vendor = Vendor::where('user_id', $userData->user_id)->first();
            if (!$vendor) {
                Session::flash('error', 'Vendor profile not found.');
                if ($loginType === 'vendor') {
                    return redirect('/vendor/login');
                }
                return redirect('/');
            }

            $sessData['vendor_id'] = $vendor->vendor_id;
            $sessData['vendor_profile_image'] = $vendor->profile_image;
        }

        session($sessData);

        if ((int) $userData->role_type === 1) {
            return redirect('/admin/dashboard');
        }

        return redirect('/vendor/dashboard');
    }


    public function forgotPassword()
    {
        $data['title'] = 'Logout';
        return view('Auth.forgotPassword');
    }

    public function logout()
    {
        Auth::logout();
        Session::flush();
        return redirect('/');
    }

    private function vendorOtpLastSentCacheKey(string $mobile): string
    {
        return 'vendor_otp_last_sent_' . $mobile;
    }

    private function maskMobile(string $mobile): string
    {
        if (strlen($mobile) !== 10) {
            return $mobile;
        }

        return '+91 ' . substr($mobile, 0, 2) . '******' . substr($mobile, -2);
    }
}
