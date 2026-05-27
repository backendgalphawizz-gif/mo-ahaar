<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('driver_profiles')) {
            return;
        }

        Schema::create('driver_profiles', function (Blueprint $table) {
            $table->id('profile_id');
            $table->unsignedBigInteger('driver_id')->unique();
            $table->string('vehicle_number', 20)->nullable();
            $table->string('driving_license', 255)->nullable();
            $table->string('aadhar_card', 255)->nullable();
            $table->timestamp('driving_license_uploaded_at')->nullable();
            $table->timestamp('aadhar_card_uploaded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_profiles');
    }
};
