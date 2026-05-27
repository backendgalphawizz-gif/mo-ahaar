<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('delivery_assignments')) {
            return;
        }

        Schema::create('delivery_assignments', function (Blueprint $table) {
            $table->id('assignment_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->string('status', 32)->default('new');
            $table->decimal('payout_amount', 12, 2)->default(0);
            $table->string('pickup_address', 500)->nullable();
            $table->string('delivery_address', 500)->nullable();
            $table->string('store_name', 150)->nullable();
            $table->string('store_image', 255)->nullable();
            $table->string('store_location_summary', 255)->nullable();
            $table->text('reject_reason')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'driver_id']);
            $table->index('order_id');
        });

        if (Schema::hasTable('delivery_assignment_rejections')) {
            return;
        }

        Schema::create('delivery_assignment_rejections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assignment_id');
            $table->unsignedBigInteger('driver_id');
            $table->string('reason', 500)->nullable();
            $table->timestamps();

            $table->unique(['assignment_id', 'driver_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_assignment_rejections');
        Schema::dropIfExists('delivery_assignments');
    }
};
