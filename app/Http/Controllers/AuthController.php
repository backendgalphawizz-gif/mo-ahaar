<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{


    public function index(){
        $user_id = session()->get('user_id');
        if($user_id){
            $userTypeId = session()->get('role_id');
            $redirect = redirect('/');
            switch ($userTypeId) {
                case 1:
                    $redirect = redirect('/admin/dashboard');
                    break;
                case 2:
                    $redirect = redirect('/storeAdmin/dashboard');
                    break;
                case 3:
                    $redirect = redirect('/storeManager/dashboard');
                    break;
                case 4:
                    $redirect = redirect('/storeExecutive/dashboard');
                    break;
                case 5:
                    $redirect = redirect('/sealsTeam/dashboard');
                    break;
                case 20:
                    $redirect = redirect('/superadmin/dashboard');
                    break;
            }
            return $redirect;
        }
        $data['title'] = 'Login';
        return view ('Auth.login');
        
    }





    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
     
             
        $credentials = $request->only('email','password');
        
        // if(Auth::attempt($credentials))
        // {
        //     return redirect('/dashboard');
        // }
        return redirect('/admin/dashboard');

        // return back()->with('error','Invalid Email or Password');
    }


    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

}