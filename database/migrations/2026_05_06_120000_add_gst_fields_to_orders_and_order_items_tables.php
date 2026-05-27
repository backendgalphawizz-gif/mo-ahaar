<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'gst_amount')) {
                $table->decimal('gst_amount', 12, 2)->default(0)->after('tax_amount');
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'gst_amount')) {
                $table->decimal('gst_amount', 12, 2)->default(0)->after('tax_amount');
            }

            if (!Schema::hasColumn('order_items', 'gst_percentage')) {
                $table->decimal('gst_percentage', 5, 2)->default(0)->after('gst_amount');
            }

            if (!Schema::hasColumn('order_items', 'gst_calculation_type')) {
                $table->string('gst_calculation_type', 20)->nullable()->after('gst_percentage');
            }

            if (!Schema::hasColumn('order_items', 'effective_price')) {
                $table->decimal('effective_price', 12, 2)->default(0)->after('unit_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            foreach (['effective_price', 'gst_calculation_type', 'gst_percentage', 'gst_amount'] as $column) {
                if (Schema::hasColumn('order_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'gst_amount')) {
                $table->dropColumn('gst_amount');
            }
        });
    }
};
