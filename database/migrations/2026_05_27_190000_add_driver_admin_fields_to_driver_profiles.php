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
            $columns = [
                'driver_code' => fn () => $table->string('driver_code', 20)->nullable()->unique(),
                'city' => fn () => $table->string('city', 100)->nullable(),
                'address' => fn () => $table->string('address', 500)->nullable(),
                'driving_license_number' => fn () => $table->string('driving_license_number', 50)->nullable(),
            ];

            foreach ($columns as $column => $callback) {
                if (!Schema::hasColumn('driver_profiles', $column)) {
                    $callback();
                }
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('driver_profiles')) {
            return;
        }

        Schema::table('driver_profiles', function (Blueprint $table) {
            foreach (['driver_code', 'city', 'address', 'driving_license_number'] as $column) {
                if (Schema::hasColumn('driver_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
