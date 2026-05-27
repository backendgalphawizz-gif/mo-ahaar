<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tickets')) {
            Schema::create('tickets', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('user_id');
                $table->string('type', 50);
                $table->string('subject', 190);
                $table->text('description');
                $table->string('status', 50)->default('open');
                $table->string('priority', 20)->default('medium');
                $table->bigInteger('assigned_to')->nullable();
                $table->timestamps();

                $table->index(['status', 'type']);
                $table->index('assigned_to');
            });
        }

        DB::statement('ALTER TABLE tickets MODIFY user_id BIGINT NOT NULL');
        DB::statement('ALTER TABLE tickets MODIFY assigned_to BIGINT NULL');

        if (!$this->constraintExists('tickets', 'tickets_user_id_foreign')) {
            DB::statement('ALTER TABLE tickets ADD CONSTRAINT tickets_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE');
        }

        if (!$this->constraintExists('tickets', 'tickets_assigned_to_foreign')) {
            DB::statement('ALTER TABLE tickets ADD CONSTRAINT tickets_assigned_to_foreign FOREIGN KEY (assigned_to) REFERENCES users(user_id) ON DELETE SET NULL');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }

    private function constraintExists(string $table, string $constraintName): bool
    {
        $databaseName = DB::getDatabaseName();

        $result = DB::selectOne(
            'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? LIMIT 1',
            [$databaseName, $table, $constraintName]
        );

        return $result !== null;
    }
};