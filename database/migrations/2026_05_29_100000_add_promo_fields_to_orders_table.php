<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'promo_code')) {
                $table->string('promo_code', 80)->nullable()->after('notes');
            }
            if (!Schema::hasColumn('orders', 'promo_discount')) {
                $table->decimal('promo_discount', 10, 2)->default(0)->after('promo_code');
            }
            if (!Schema::hasColumn('orders', 'discount_offer_id')) {
                $table->unsignedBigInteger('discount_offer_id')->nullable()->after('promo_discount');
            }
            if (!Schema::hasColumn('orders', 'offer_discount')) {
                $table->decimal('offer_discount', 10, 2)->default(0)->after('discount_offer_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            foreach (['offer_discount', 'discount_offer_id', 'promo_discount', 'promo_code'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
