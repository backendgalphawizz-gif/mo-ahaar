<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('store_settings')) {
            return;
        }

        Schema::table('store_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('store_settings', 'customer_registration_privacy_policy_enabled')) {
                $table->boolean('customer_registration_privacy_policy_enabled')->default(true)->after('customer_home_featured_products_enabled');
            }
            if (!Schema::hasColumn('store_settings', 'customer_registration_terms_enabled')) {
                $table->boolean('customer_registration_terms_enabled')->default(true)->after('customer_registration_privacy_policy_enabled');
            }
            if (!Schema::hasColumn('store_settings', 'customer_registration_faq_enabled')) {
                $table->boolean('customer_registration_faq_enabled')->default(true)->after('customer_registration_terms_enabled');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('store_settings')) {
            return;
        }

        Schema::table('store_settings', function (Blueprint $table) {
            $drop = [];
            if (Schema::hasColumn('store_settings', 'customer_registration_privacy_policy_enabled')) {
                $drop[] = 'customer_registration_privacy_policy_enabled';
            }
            if (Schema::hasColumn('store_settings', 'customer_registration_terms_enabled')) {
                $drop[] = 'customer_registration_terms_enabled';
            }
            if (Schema::hasColumn('store_settings', 'customer_registration_faq_enabled')) {
                $drop[] = 'customer_registration_faq_enabled';
            }

            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
};
