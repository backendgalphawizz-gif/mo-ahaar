<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('vendors')) {
            return;
        }

        Schema::table('vendors', function (Blueprint $table) {
            if (!Schema::hasColumn('vendors', 'aadhaar_card_front')) {
                $table->string('aadhaar_card_front', 255)->nullable()->after('aadhaar_card');
            }
            if (!Schema::hasColumn('vendors', 'aadhaar_card_back')) {
                $table->string('aadhaar_card_back', 255)->nullable()->after('aadhaar_card_front');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('vendors')) {
            return;
        }

        Schema::table('vendors', function (Blueprint $table) {
            if (Schema::hasColumn('vendors', 'aadhaar_card_back')) {
                $table->dropColumn('aadhaar_card_back');
            }
            if (Schema::hasColumn('vendors', 'aadhaar_card_front')) {
                $table->dropColumn('aadhaar_card_front');
            }
        });
    }
};
