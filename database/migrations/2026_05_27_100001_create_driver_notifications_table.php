<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('driver_notifications')) {
            return;
        }

        Schema::create('driver_notifications', function (Blueprint $table) {
            $table->id('notification_id');
            $table->unsignedBigInteger('driver_id');
            $table->string('title', 150);
            $table->text('message')->nullable();
            $table->string('type', 50)->default('general');
            $table->unsignedBigInteger('assignment_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['driver_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_notifications');
    }
};
