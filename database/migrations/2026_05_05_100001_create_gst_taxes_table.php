<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gst_taxes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->decimal('percentage', 5, 2);
            $table->tinyInteger('status')->default(1)->comment('1=active, 0=inactive');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gst_taxes');
    }
};
