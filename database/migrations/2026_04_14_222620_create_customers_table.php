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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('source')->nullable();
            $table->string('notes')->nullable();
            $table->foreignId('level_id')->nullable()->constrained('levels')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('cascade');
            $table->string('created_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->integer('age')->nullable();
            $table->string('gender')->nullable();
            $table->string('address')->nullable();
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
