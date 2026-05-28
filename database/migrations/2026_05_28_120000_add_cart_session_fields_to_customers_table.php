<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'cart_cooking_instructions')) {
                $table->text('cart_cooking_instructions')->nullable()->after('location_updated_at');
            }
            if (!Schema::hasColumn('customers', 'cart_promo_code')) {
                $table->string('cart_promo_code', 80)->nullable()->after('cart_cooking_instructions');
            }
            if (!Schema::hasColumn('customers', 'cart_discount_offer_id')) {
                $table->unsignedBigInteger('cart_discount_offer_id')->nullable()->after('cart_promo_code');
            }
            if (!Schema::hasColumn('customers', 'cart_selected_address_id')) {
                $table->unsignedInteger('cart_selected_address_id')->nullable()->after('cart_discount_offer_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            foreach (['cart_selected_address_id', 'cart_discount_offer_id', 'cart_promo_code', 'cart_cooking_instructions'] as $column) {
                if (Schema::hasColumn('customers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
