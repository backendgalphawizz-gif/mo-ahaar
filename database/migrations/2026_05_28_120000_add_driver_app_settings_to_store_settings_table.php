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
            if (!Schema::hasColumn('store_settings', 'driver_app_privacy_policy_enabled')) {
                $table->boolean('driver_app_privacy_policy_enabled')->default(true);
            }
            if (!Schema::hasColumn('store_settings', 'driver_app_terms_enabled')) {
                $table->boolean('driver_app_terms_enabled')->default(true);
            }
            if (!Schema::hasColumn('store_settings', 'driver_app_faq_enabled')) {
                $table->boolean('driver_app_faq_enabled')->default(true);
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('store_settings')) {
            return;
        }

        Schema::table('store_settings', function (Blueprint $table) {
            $columns = [
                'driver_app_privacy_policy_enabled',
                'driver_app_terms_enabled',
                'driver_app_faq_enabled',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('store_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
