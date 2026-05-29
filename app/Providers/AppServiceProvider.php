<?php

namespace App\Providers;

use App\Models\StoreSetting;
use App\Models\VendorNotification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Contracts\Http\Kernel as HttpKernelContract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            static $storeSettingLoaded = false;
            static $storeSetting = null;

            if (!$storeSettingLoaded) {
                $storeSettingLoaded = true;

                if (Schema::hasTable('store_settings')) {
                    $storeSetting = StoreSetting::query()->first();
                }
            }

            $view->with('globalStoreSetting', $storeSetting);
        });

        View::composer('layouts.app', function ($view) {
            $vendorUnread = 0;
            if ((int) session('role_type') === 3 && session('vendor_id') && Schema::hasTable('vendor_notifications')) {
                $vendorUnread = VendorNotification::query()
                    ->where('vendor_id', (int) session('vendor_id'))
                    ->where('is_read', false)
                    ->count();
            }
            $view->with('vendorUnreadNotifications', $vendorUnread);
        });

        // Manually push SecurityHeaders middleware to the middleware stack
        $kernel = $this->app->make(HttpKernelContract::class);
        $kernel->prependMiddleware(\App\Http\Middleware\SecurityHeaders::class);
    }
}
