<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->bigInteger('user_id');
            $table->text('message');
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_internal')->default(false);
            $table->string('attachment')->nullable();
            $table->timestamps();

            $table->index(['ticket_id', 'created_at']);
            $table->foreign('ticket_id')->references('id')->on('tickets')->cascadeOnDelete();
            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_replies');
    }
};