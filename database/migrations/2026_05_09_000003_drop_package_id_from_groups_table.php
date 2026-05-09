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
        if (! Schema::hasColumn('groups', 'package_id')) {
            return;
        }

        Schema::table('groups', function (Blueprint $table) {
            $table->dropConstrainedForeignId('package_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('groups', 'package_id')) {
            return;
        }

        Schema::table('groups', function (Blueprint $table) {
            $table->foreignId('package_id')
                ->nullable()
                ->after('category_id')
                ->constrained()
                ->nullOnDelete();
        });
    }
};
