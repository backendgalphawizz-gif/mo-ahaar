<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('products') || Schema::hasColumn('products', 'gst_calculation_type')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->string('gst_calculation_type', 20)
                ->default('excluded')
                ->after('gst_tax_id');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('products') || !Schema::hasColumn('products', 'gst_calculation_type')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('gst_calculation_type');
        });
    }
};
