<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    protected $table = 'store_settings';

    protected $fillable = [
        'app_name',
        'site_title',
        'support_number',
        'support_email',
        'address',
        'logo',
        'favicon',
        'customer_home_sliders_enabled',
        'customer_home_offers_enabled',
        'customer_home_promotions_enabled',
        'customer_home_announcements_enabled',
        'customer_home_featured_products_enabled',
        'customer_registration_privacy_policy_enabled',
        'customer_registration_terms_enabled',
        'customer_registration_faq_enabled',
    ];

    protected $casts = [
        'customer_home_sliders_enabled' => 'boolean',
        'customer_home_offers_enabled' => 'boolean',
        'customer_home_promotions_enabled' => 'boolean',
        'customer_home_announcements_enabled' => 'boolean',
        'customer_home_featured_products_enabled' => 'boolean',
        'customer_registration_privacy_policy_enabled' => 'boolean',
        'customer_registration_terms_enabled' => 'boolean',
        'customer_registration_faq_enabled' => 'boolean',
    ];
}
