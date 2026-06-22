<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->foreignId('program_id')->nullable()->after('name')->constrained('programs')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->after('program_id')->constrained('categories')->nullOnDelete();
            $table->integer('sessions_count')->default(0)->after('levels_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropForeign(['program_id']);
            $table->dropForeign(['category_id']);
            $table->dropColumn(['program_id', 'category_id', 'sessions_count']);
        });
    }
};
