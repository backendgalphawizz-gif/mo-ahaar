<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users_role')) {
            return;
        }

        $exists = DB::table('users_role')->where('role_type', 4)->exists();
        if (!$exists) {
            DB::table('users_role')->insert([
                'role_type' => 4,
                'role' => 'Driver',
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users_role')) {
            DB::table('users_role')->where('role_type', 4)->delete();
        }
    }
};
