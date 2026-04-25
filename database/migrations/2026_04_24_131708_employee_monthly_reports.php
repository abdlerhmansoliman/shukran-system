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
        Schema::create('employee_monthly_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            $table->enum('month', array_map('strval', range(1, 12)));
            $table->unsignedSmallInteger('year');

            $table->decimal('required_working_days', 8, 2)->default(0);
            $table->decimal('required_working_hours', 8, 2)->default(0);

            $table->decimal('actual_worked_days', 8, 2)->default(0);
            $table->decimal('actual_worked_hours', 8, 2)->default(0);

            $table->decimal('overtime_hours', 8, 2)->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['employee_id', 'month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_monthly_reports');
    }
};
