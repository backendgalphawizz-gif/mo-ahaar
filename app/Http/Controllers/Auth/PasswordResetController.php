<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class PasswordResetController extends Controller
{
    /**
     * Show the forgot password form
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send OTP to email
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = Users::where('email', $request->email)
            ->where('status', '1')
            ->where('role_type', 1) // Only admin users
            ->first();

        if (!$user) {
            Session::flash('error', 'No admin account found with this email address.');
            return redirect()->route('forgot.password')->withInput($request->only('email'));
        }

        // Generate OTP (6 digits)
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP in database with expiration (10 minutes)
        $user->update([
            'password_reset_otp' => $otp,
            'password_reset_otp_expires_at' => now()->addMinutes(10),
        ]);

        // Send OTP via email
        try {
            Mail::raw("Your password reset OTP is: {$otp}\n\nThis OTP will expire in 10 minutes.", function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Password Reset OTP - E-Commerce Admin');
            });

            Session::flash('success', 'OTP has been sent to your email address.');
            Session::put('reset_email', $user->email); // Store email for next step
            return redirect()->route('verify.otp');
        } catch (\Throwable $e) {
            Log::error('Failed to send admin password reset OTP email.', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
            Session::flash('error', 'Failed to send OTP. Please try again later.');
            return redirect()->route('forgot.password')->withInput($request->only('email'));
        }
    }

    /**
     * Show OTP verification form
     */
    public function showVerifyOtpForm()
    {
        $reset_email = Session::get('reset_email');
        if (!$reset_email) {
            Session::flash('error', 'Please enter your email first.');
            return redirect()->route('forgot.password');
        }

        return view('auth.verify-otp', ['email' => $reset_email]);
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $reset_email = Session::get('reset_email');
        if (!$reset_email) {
            Session::flash('error', 'Session expired. Please try again.');
            return redirect()->route('forgot.password');
        }

        $user = Users::where('email', $reset_email)
            ->where('status', '1')
            ->where('role_type', 1)
            ->first();

        if (!$user) {
            Session::flash('error', 'User not found.');
            return redirect()->route('forgot.password');
        }

        // Check if OTP exists and is not expired
        if (!$user->password_reset_otp || $user->password_reset_otp !== $request->otp) {
            Session::flash('error', 'Invalid OTP. Please try again.');
            return redirect()->route('verify.otp');
        }

        if (now()->isAfter($user->password_reset_otp_expires_at)) {
            Session::flash('error', 'OTP has expired. Please request a new one.');
            return redirect()->route('forgot.password');
        }

        // OTP verified successfully
        Session::put('otp_verified', true);
        Session::put('verified_email', $reset_email);
        Session::flash('success', 'OTP verified successfully. Please enter your new password.');
        return redirect()->route('reset.password.form');
    }

    /**
     * Show reset password form
     */
    public function showResetPasswordForm()
    {
        if (!Session::get('otp_verified')) {
            Session::flash('error', 'Please verify OTP first.');
            return redirect()->route('forgot.password');
        }

        return view('auth.reset-password');
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        if (!Session::get('otp_verified')) {
            Session::flash('error', 'Session expired. Please try again.');
            return redirect()->route('forgot.password');
        }

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $verified_email = Session::get('verified_email');
        $user = Users::where('email', $verified_email)
            ->where('status', '1')
            ->where('role_type', 1)
            ->first();

        if (!$user) {
            Session::flash('error', 'User not found.');
            return redirect()->route('forgot.password');
        }

        // Update password and clear OTP fields
        $user->update([
            'password' => Hash::make($request->password),
            'password_reset_otp' => null,
            'password_reset_otp_expires_at' => null,
        ]);

        // Clear session data
        Session::forget(['reset_email', 'otp_verified', 'verified_email']);
        Session::flash('success', 'Password has been reset successfully. Please login with your new password.');

        return redirect('/');
    }

    /**
     * Resend OTP
     */
    public function resendOtp(Request $request)
    {
        $reset_email = Session::get('reset_email');
        if (!$reset_email) {
            Session::flash('error', 'Session expired. Please try again.');
            return redirect()->route('forgot.password');
        }

        $user = Users::where('email', $reset_email)
            ->where('status', '1')
            ->where('role_type', 1)
            ->first();

        if (!$user) {
            Session::flash('error', 'User not found.');
            return redirect()->route('forgot.password');
        }

        // Generate new OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP in database with expiration (10 minutes)
        $user->update([
            'password_reset_otp' => $otp,
            'password_reset_otp_expires_at' => now()->addMinutes(10),
        ]);

        // Send OTP via email
        try {
            Mail::raw("Your password reset OTP is: {$otp}\n\nThis OTP will expire in 10 minutes.", function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Password Reset OTP - E-Commerce Admin');
            });

            Session::flash('success', 'OTP has been resent to your email address.');
        } catch (\Throwable $e) {
            Log::error('Failed to resend admin password reset OTP email.', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
            Session::flash('error', 'Failed to resend OTP. Please try again later.');
        }

        return redirect()->route('verify.otp');
    }
}
