<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('driver_profiles')) {
            return;
        }

        Schema::table('driver_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('driver_profiles', 'driving_license_back')) {
                $table->string('driving_license_back', 255)->nullable()->after('driving_license');
            }
            if (!Schema::hasColumn('driver_profiles', 'driving_license_back_uploaded_at')) {
                $table->timestamp('driving_license_back_uploaded_at')->nullable()->after('driving_license_uploaded_at');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('driver_profiles')) {
            return;
        }

        Schema::table('driver_profiles', function (Blueprint $table) {
            foreach (['driving_license_back', 'driving_license_back_uploaded_at'] as $column) {
                if (Schema::hasColumn('driver_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
