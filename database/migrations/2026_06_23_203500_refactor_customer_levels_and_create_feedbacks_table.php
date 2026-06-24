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
            $table->dropForeign('customers_level_id_foreign');
            $table->renameColumn('level_id', 'entry_level_id');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->foreign('entry_level_id')->references('id')->on('levels')->nullOnDelete();
            $table->foreignId('current_level_id')->nullable()->after('entry_level_id')->constrained('levels')->nullOnDelete();
        });

        Schema::create('customer_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('level_id')->nullable()->constrained('levels')->nullOnDelete();
            $table->text('feedback');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_feedbacks');

        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['current_level_id']);
            $table->dropColumn('current_level_id');
            $table->dropForeign(['entry_level_id']);
            $table->renameColumn('entry_level_id', 'level_id');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->foreign('level_id')->references('id')->on('levels')->nullOnDelete();
        });
    }
};
