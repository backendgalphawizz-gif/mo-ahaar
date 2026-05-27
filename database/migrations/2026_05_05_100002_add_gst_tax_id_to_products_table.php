<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('gst_tax_id')->nullable()->after('tax_name');
            $table->foreign('gst_tax_id')->references('id')->on('gst_taxes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['gst_tax_id']);
            $table->dropColumn('gst_tax_id');
        });
    }
};
