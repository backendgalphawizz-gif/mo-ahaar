<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        if (!Schema::hasColumn('products', 'mrp_price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('mrp_price', 10, 2)->nullable()->after('price');
            });
        }

        // Backfill existing rows so old products keep working with the new pricing flow.
        DB::table('products')->whereNull('mrp_price')->update([
            'mrp_price' => DB::raw('price'),
        ]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('products') || !Schema::hasColumn('products', 'mrp_price')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('mrp_price');
        });
    }
};
