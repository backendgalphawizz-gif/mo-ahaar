<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'active_cart_vendor_id')) {
                $table->unsignedInteger('active_cart_vendor_id')
                    ->nullable()
                    ->after('cart_selected_address_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'active_cart_vendor_id')) {
                $table->dropColumn('active_cart_vendor_id');
            }
        });
    }
};

