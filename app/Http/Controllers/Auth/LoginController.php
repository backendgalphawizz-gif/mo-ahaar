<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Users;
use App\Models\Usersrole;
use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{

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
                case 2:
                    $redirect = redirect('/vendor/dashboard');
                    break;
                case 3:
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
            // If already logged in, redirect to appropriate dashboard
            if ((int) $userTypeId === 2) {
                return redirect('/vendor/dashboard');
            } elseif ((int) $userTypeId === 1) {
                return redirect('/admin/dashboard');
            }
        }
        return view('auth.vendor-login', [
            'loginTitle' => 'Vendor Dashboard Login',
            'formAction' => route('vendor.login.submit'),
        ]);
    }

    public function checkLogin(Request $request)
    {
        return $this->handleLogin($request, 'admin');
    }

    public function vendorLogin(Request $request)
    {
        return $this->handleLogin($request, 'vendor');
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

        if ($loginType === 'vendor' && $existingUser && (int) $existingUser->role_type === 2 && Hash::check($password, $existingUser->password)) {
            if ((string) $existingUser->status !== '1') {
                Session::flash('error', 'Your vendor account is pending admin approval. Please wait for activation.');
                return redirect('/vendor/login')->withInput($request->only('email'));
            }

            $vendorAccount = Vendor::where('user_id', $existingUser->user_id)->first();
            if ($vendorAccount && (string) $vendorAccount->status !== '1') {
                Session::flash('error', 'Your vendor account is pending admin approval. Please wait for activation.');
                return redirect('/vendor/login')->withInput($request->only('email'));
            }
        }

        $userData = Users::where('email', $email)
            ->where('status', '1')
            ->first();

        if (!$userData || !in_array((int) $userData->role_type, [1, 2], true) || !Hash::check($password, $userData->password)) {
            Session::flash('error', 'You entered wrong email or password!');
            
            // Redirect to the appropriate login form based on login type
            if ($loginType === 'vendor') {
                return redirect('/vendor/login')->withInput($request->only('email'));
            }
            return redirect('/')->withInput($request->only('email'));
        }

        // Additional check: if vendor login is attempted, ensure user is a vendor
        if ($loginType === 'vendor' && (int) $userData->role_type !== 2) {
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

        if ((int) $userData->role_type === 2) {
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
}
