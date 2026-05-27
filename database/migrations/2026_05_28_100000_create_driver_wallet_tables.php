<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('driver_wallets')) {
            Schema::create('driver_wallets', function (Blueprint $table) {
                $table->id('wallet_id');
                $table->unsignedBigInteger('driver_id')->unique();
                $table->decimal('balance', 12, 2)->default(0);
                $table->decimal('pending_balance', 12, 2)->default(0);
                $table->string('currency', 3)->default('INR');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('driver_withdrawals')) {
            Schema::create('driver_withdrawals', function (Blueprint $table) {
                $table->id('withdrawal_id');
                $table->unsignedBigInteger('driver_id');
                $table->decimal('amount', 12, 2);
                $table->string('status', 20)->default('pending');
                $table->timestamps();

                $table->index(['driver_id', 'status']);
            });
        }

        if (!Schema::hasTable('driver_transactions')) {
            Schema::create('driver_transactions', function (Blueprint $table) {
                $table->id('transaction_id');
                $table->unsignedBigInteger('driver_id');
                $table->string('transaction_ref', 32)->unique();
                $table->string('type', 20);
                $table->string('status', 20)->default('completed');
                $table->decimal('amount', 12, 2);
                $table->decimal('balance_after', 12, 2)->nullable();
                $table->string('title', 150)->nullable();
                $table->string('subtitle', 150)->nullable();
                $table->unsignedBigInteger('order_id')->nullable();
                $table->unsignedBigInteger('assignment_id')->nullable();
                $table->unsignedBigInteger('withdrawal_id')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();

                $table->index(['driver_id', 'created_at']);
                $table->index(['driver_id', 'type', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_transactions');
        Schema::dropIfExists('driver_withdrawals');
        Schema::dropIfExists('driver_wallets');
    }
};
