<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('accept_terms')->default(false)->after('approval_status');
            $table->timestamp('terms_accepted_at')->nullable()->after('accept_terms');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['accept_terms', 'terms_accepted_at']);
        });
    }
};
