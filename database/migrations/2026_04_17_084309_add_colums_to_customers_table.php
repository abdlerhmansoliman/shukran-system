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
        Schema::table('customers', function (Blueprint $table) {
            $table->enum('customer_type', ['new', 'old'])->default('new')->after('source');
            $table->date('placement_month')->nullable()->after('customer_type');
            $table->foreignId('tester_id')->nullable()->after('placement_month')->constrained('users')->nullOnDelete();
            $table->foreignId('old_instructor_id')->nullable()->after('tester_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('old_instructor_id');
            $table->dropConstrainedForeignId('tester_id');
            $table->dropColumn(['placement_month', 'customer_type']);
        });
    }
};
