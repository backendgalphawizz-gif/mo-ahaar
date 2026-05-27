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
                'account_holder_name' => fn () => $table->string('account_holder_name', 150)->nullable(),
                'bank_name' => fn () => $table->string('bank_name', 150)->nullable(),
                'branch_name' => fn () => $table->string('branch_name', 150)->nullable(),
                'account_number' => fn () => $table->string('account_number', 30)->nullable(),
                'ifsc_code' => fn () => $table->string('ifsc_code', 20)->nullable(),
                'account_type' => fn () => $table->string('account_type', 30)->nullable(),
                'vehicle_type' => fn () => $table->string('vehicle_type', 50)->nullable(),
                'vehicle_model' => fn () => $table->string('vehicle_model', 100)->nullable(),
                'vehicle_color' => fn () => $table->string('vehicle_color', 50)->nullable(),
                'registration_year' => fn () => $table->unsignedSmallInteger('registration_year')->nullable(),
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
            $columns = [
                'account_holder_name',
                'bank_name',
                'branch_name',
                'account_number',
                'ifsc_code',
                'account_type',
                'vehicle_type',
                'vehicle_model',
                'vehicle_color',
                'registration_year',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('driver_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
