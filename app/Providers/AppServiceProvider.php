<?php

namespace App\Providers;

use App\Models\StoreSetting;
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

        // Manually push SecurityHeaders middleware to the middleware stack
        $kernel = $this->app->make(HttpKernelContract::class);
        $kernel->prependMiddleware(\App\Http\Middleware\SecurityHeaders::class);
    }
}
