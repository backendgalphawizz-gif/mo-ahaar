<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class VendorAuth
{
    /**
     * Ensure only logged-in vendor users can access vendor panel routes.
     */
    public function handle($request, Closure $next)
    {
        $userId = session('user_id');
        $roleType = (int) session('role_type');
        $vendorId = session('vendor_id');

        if (empty($userId)) {
            Session::flash('error', 'Your session has expired');
            return redirect('/vendor/login');
        }

        if ($roleType !== 3 || empty($vendorId)) {
            Session::flash('error', "You don't have access this section");
            return redirect('/vendor/login');
        }

        return $next($request);
    }
}
