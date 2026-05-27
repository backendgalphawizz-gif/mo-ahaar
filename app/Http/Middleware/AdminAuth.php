<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Session;
use App;
class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $role_type = session('role_type');
        $user_id      = session('user_id');
        if(empty($user_id)){
            Session::flash('error', "Your session has expired");
            return redirect('/');
        }else if($role_type == 1 && !empty($user_id)){
            return $next($request);
        }else{
            Session::flash('error', "You don't have access this section");
            return redirect('/');
        }
    }
}
