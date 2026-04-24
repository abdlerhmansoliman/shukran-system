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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');

            $table->decimal('basic_salary', 10, 2)->default(0);
            $table->decimal('hour_salary', 10, 2)->default(0);

            $table->decimal('absence_deduction', 10, 2)->default(0);
            $table->decimal('overtime_amount', 10, 2)->default(0);

            $table->decimal('total_bonus', 10, 2)->default(0);
            $table->decimal('total_deductions', 10, 2)->default(0);

            $table->decimal('net_salary', 10, 2)->default(0);

            $table->enum('status', ['draft', 'paid'])->default('draft');

            $table->timestamps();

            $table->unique(['employee_id', 'month', 'year']);
            $table->check('month between 1 and 12');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
