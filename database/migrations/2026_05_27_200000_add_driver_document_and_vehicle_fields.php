<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('driver_profiles')) {
            return;
        }

        Schema::table('driver_profiles', function (Blueprint $table) {
            $columns = [
                'document_type' => fn () => $table->string('document_type', 20)->nullable(),
                'pan_card' => fn () => $table->string('pan_card', 255)->nullable(),
                'pan_card_uploaded_at' => fn () => $table->timestamp('pan_card_uploaded_at')->nullable(),
                'rc_image' => fn () => $table->string('rc_image', 255)->nullable(),
                'rc_image_uploaded_at' => fn () => $table->timestamp('rc_image_uploaded_at')->nullable(),
                'puc_number' => fn () => $table->string('puc_number', 50)->nullable(),
                'puc_expiry_date' => fn () => $table->date('puc_expiry_date')->nullable(),
                'puc_image' => fn () => $table->string('puc_image', 255)->nullable(),
                'puc_image_uploaded_at' => fn () => $table->timestamp('puc_image_uploaded_at')->nullable(),
            ];

            foreach ($columns as $column => $callback) {
                if (!Schema::hasColumn('driver_profiles', $column)) {
                    $callback();
                }
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('driver_profiles')) {
            return;
        }

        Schema::table('driver_profiles', function (Blueprint $table) {
            foreach ([
                'document_type',
                'pan_card',
                'pan_card_uploaded_at',
                'rc_image',
                'rc_image_uploaded_at',
                'puc_number',
                'puc_expiry_date',
                'puc_image',
                'puc_image_uploaded_at',
            ] as $column) {
                if (Schema::hasColumn('driver_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
