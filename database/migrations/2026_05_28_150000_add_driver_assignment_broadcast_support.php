<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('driver_profiles')) {
            Schema::table('driver_profiles', function (Blueprint $table) {
                if (!Schema::hasColumn('driver_profiles', 'latitude')) {
                    $table->decimal('latitude', 10, 7)->nullable()->after('address');
                }
                if (!Schema::hasColumn('driver_profiles', 'longitude')) {
                    $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
                }
                if (!Schema::hasColumn('driver_profiles', 'is_online')) {
                    $table->boolean('is_online')->default(false)->after('longitude');
                }
                if (!Schema::hasColumn('driver_profiles', 'last_location_at')) {
                    $table->timestamp('last_location_at')->nullable()->after('is_online');
                }
            });
        }

        if (Schema::hasTable('delivery_assignments')) {
            Schema::table('delivery_assignments', function (Blueprint $table) {
                if (!Schema::hasColumn('delivery_assignments', 'assignment_mode')) {
                    $table->string('assignment_mode', 20)->nullable()->after('status');
                }
                if (!Schema::hasColumn('delivery_assignments', 'broadcast_at')) {
                    $table->timestamp('broadcast_at')->nullable()->after('assigned_at');
                }
            });
        }

        if (!Schema::hasTable('delivery_assignment_invites')) {
            Schema::create('delivery_assignment_invites', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('assignment_id');
                $table->unsignedBigInteger('driver_id');
                $table->string('status', 20)->default('pending');
                $table->timestamp('notified_at')->nullable();
                $table->timestamps();

                $table->unique(['assignment_id', 'driver_id']);
                $table->index(['driver_id', 'status']);
                $table->index(['assignment_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_assignment_invites');

        if (Schema::hasTable('delivery_assignments')) {
            Schema::table('delivery_assignments', function (Blueprint $table) {
                foreach (['assignment_mode', 'broadcast_at'] as $column) {
                    if (Schema::hasColumn('delivery_assignments', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('driver_profiles')) {
            Schema::table('driver_profiles', function (Blueprint $table) {
                foreach (['latitude', 'longitude', 'is_online', 'last_location_at'] as $column) {
                    if (Schema::hasColumn('driver_profiles', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
