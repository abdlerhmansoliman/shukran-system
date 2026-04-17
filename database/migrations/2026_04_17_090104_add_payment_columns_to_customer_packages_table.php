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
        Schema::table('customer_packages', function (Blueprint $table) {
            $table->decimal('paid_amount', 10, 2)->default(0)->after('final_price');
            $table->decimal('remaining_amount', 10, 2)->default(0)->after('paid_amount');
            $table->date('payment_date')->nullable()->after('remaining_amount');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid')->after('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_packages', function (Blueprint $table) {
            $table->dropColumn([
                'payment_status',
                'payment_date',
                'remaining_amount',
                'paid_amount',
            ]);
        });
    }
};
