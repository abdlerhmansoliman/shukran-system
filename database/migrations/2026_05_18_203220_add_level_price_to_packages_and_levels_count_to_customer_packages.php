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
            $table->decimal('level_price', 12, 2)->after('price')->default(0);
        });

        Schema::table('customer_packages', function (Blueprint $table) {
            $table->integer('levels_count')->after('package_id')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('level_price');
        });

        Schema::table('customer_packages', function (Blueprint $table) {
            $table->dropColumn('levels_count');
        });
    }
};
