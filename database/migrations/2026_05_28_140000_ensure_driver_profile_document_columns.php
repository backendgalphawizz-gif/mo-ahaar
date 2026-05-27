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
                'document_type' => fn () => $table->string('document_type', 20)->nullable(),
                'pan_card' => fn () => $table->string('pan_card', 255)->nullable(),
                'pan_card_uploaded_at' => fn () => $table->timestamp('pan_card_uploaded_at')->nullable(),
                'aadhar_card_back' => fn () => $table->string('aadhar_card_back', 255)->nullable(),
                'aadhar_card_back_uploaded_at' => fn () => $table->timestamp('aadhar_card_back_uploaded_at')->nullable(),
                'rc_image' => fn () => $table->string('rc_image', 255)->nullable(),
                'rc_image_uploaded_at' => fn () => $table->timestamp('rc_image_uploaded_at')->nullable(),
                'puc_number' => fn () => $table->string('puc_number', 50)->nullable(),
                'puc_expiry_date' => fn () => $table->date('puc_expiry_date')->nullable(),
                'puc_image' => fn () => $table->string('puc_image', 255)->nullable(),
                'puc_image_uploaded_at' => fn () => $table->timestamp('puc_image_uploaded_at')->nullable(),
                'driver_code' => fn () => $table->string('driver_code', 20)->nullable(),
                'city' => fn () => $table->string('city', 100)->nullable(),
                'address' => fn () => $table->string('address', 500)->nullable(),
                'driving_license_number' => fn () => $table->string('driving_license_number', 50)->nullable(),
                'account_holder_name' => fn () => $table->string('account_holder_name', 150)->nullable(),
                'bank_name' => fn () => $table->string('bank_name', 150)->nullable(),
                'branch_name' => fn () => $table->string('branch_name', 150)->nullable(),
                'account_number' => fn () => $table->string('account_number', 30)->nullable(),
                'ifsc_code' => fn () => $table->string('ifsc_code', 20)->nullable(),
                'account_type' => fn () => $table->string('account_type', 30)->nullable(),
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
            foreach (['aadhar_card_back', 'aadhar_card_back_uploaded_at'] as $column) {
                if (Schema::hasColumn('driver_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
