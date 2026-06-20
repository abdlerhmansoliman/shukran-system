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
            $table->string('job')->nullable()->after('notes');
            $table->string('college')->nullable()->after('job');
            $table->string('progress_report_link')->nullable()->after('college');
            $table->date('test_date')->nullable()->after('progress_report_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'job',
                'college',
                'progress_report_link',
                'test_date',
            ]);
        });
    }
};
