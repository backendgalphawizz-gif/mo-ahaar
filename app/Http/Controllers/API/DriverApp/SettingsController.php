<?php

namespace App\Http\Controllers\API\DriverApp;

use App\Models\StaticPage;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SettingsController extends DriverAppController
{
    /**
     * GET /api/driver-app/settings/business
     * Privacy policy, terms & conditions, and FAQs for the driver app.
     */
    public function business(Request $request)
    {
        $slugMap = [
            'privacy_policy' => 'privacy-policy',
            'terms_and_conditions' => 'terms-and-conditions',
            'faq' => 'faqs',
        ];

        $visibility = $this->driverBusinessPageVisibility();

        $rows = StaticPage::whereIn('slug', array_values($slugMap))
            ->where('status', 1)
            ->get()
            ->keyBy('slug');

        $pages = [];
        foreach ($slugMap as $key => $slug) {
            $enabled = (bool) ($visibility[$key] ?? true);
            $page = $rows->get($slug);

            $pages[$key] = [
                'enabled' => $enabled,
                'slug' => $slug,
                'title' => $page?->title,
                'content' => $enabled && $page ? $page->content : null,
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Business settings retrieved successfully',
            'data' => [
                'pages' => $pages,
                'privacy_policy' => $pages['privacy_policy'],
                'terms_and_conditions' => $pages['terms_and_conditions'],
                'faq' => $pages['faq'],
                'support' => $this->driverSupportContact(),
            ],
        ], 200);
    }

    /**
     * @return array{privacy_policy: bool, terms_and_conditions: bool, faq: bool}
     */
    private function driverBusinessPageVisibility(): array
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
            'privacy_policy' => 'driver_app_privacy_policy_enabled',
            'terms_and_conditions' => 'driver_app_terms_enabled',
            'faq' => 'driver_app_faq_enabled',
        ];

        $fallbackMap = [
            'privacy_policy' => 'customer_registration_privacy_policy_enabled',
            'terms_and_conditions' => 'customer_registration_terms_enabled',
            'faq' => 'customer_registration_faq_enabled',
        ];

        $out = [];
        foreach ($columnMap as $key => $column) {
            if (Schema::hasColumn('store_settings', $column)) {
                $out[$key] = (bool) $setting->{$column};
            } elseif (Schema::hasColumn('store_settings', $fallbackMap[$key])) {
                $out[$key] = (bool) $setting->{$fallbackMap[$key]};
            } else {
                $out[$key] = $defaults[$key];
            }
        }

        return $out;
    }

    /**
     * @return array{app_name: string|null, support_email: string|null, support_number: string|null}
     */
    private function driverSupportContact(): array
    {
        if (!Schema::hasTable('store_settings')) {
            return [
                'app_name' => config('app.name'),
                'support_email' => null,
                'support_number' => null,
            ];
        }

        $setting = StoreSetting::first();

        return [
            'app_name' => $setting?->app_name ?? config('app.name'),
            'support_email' => $setting?->support_email ?? null,
            'support_number' => $setting?->support_number ?? null,
        ];
    }
}
