<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE group_enrollments MODIFY status ENUM('pending', 'ready', 'active', 'completed', 'cancelled', 'dropped', 'transferred') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::table('group_enrollments')
            ->whereIn('status', ['pending', 'ready', 'cancelled'])
            ->update(['status' => 'active']);

        DB::statement("ALTER TABLE group_enrollments MODIFY status ENUM('active', 'completed', 'dropped', 'transferred') NOT NULL DEFAULT 'active'");
    }
};
