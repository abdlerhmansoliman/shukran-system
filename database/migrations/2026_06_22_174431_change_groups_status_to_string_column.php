<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->string('status', 30)->default('draft')->change();
        });

        // Update existing data to map to new statuses
        DB::table('groups')->where('status', 'planned')->update(['status' => 'draft']);
        DB::table('groups')->where('status', 'cancelled')->update(['status' => 'inactive']);
        DB::table('groups')->where('status', 'completed')->update(['status' => 'finished']);
        // If there are any 'active' they remain 'active'
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert data back to old enum values
        DB::table('groups')->where('status', 'draft')->update(['status' => 'planned']);
        DB::table('groups')->where('status', 'inactive')->update(['status' => 'cancelled']);
        DB::table('groups')->where('status', 'finished')->update(['status' => 'completed']);
        // Any 'open' or 'hold' will not map directly, maybe revert to planned or active. We'll map them to planned.
        DB::table('groups')->where('status', 'open')->update(['status' => 'planned']);
        DB::table('groups')->where('status', 'hold')->update(['status' => 'planned']);

        // Since it was an enum, changing back from string to enum might fail depending on the DB engine if data doesn't match perfectly.
        // But we attempt it using native change().
        Schema::table('groups', function (Blueprint $table) {
            $table->enum('status', ['planned', 'active', 'completed', 'cancelled'])->default('planned')->change();
        });
    }
};
