<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('employees', 'deleted_at')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['level_id']);
            $table->dropForeign(['category_id']);

            $table->foreign('level_id')->references('id')->on('levels')->nullOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['department_id']);

            $table->foreign('department_id')->references('id')->on('departments')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropForeign(['level_id']);
                $table->dropForeign(['category_id']);

                $table->foreign('level_id')->references('id')->on('levels')->cascadeOnDelete();
                $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
            });

            Schema::table('employees', function (Blueprint $table) {
                $table->dropForeign(['department_id']);

                $table->foreign('department_id')->references('id')->on('departments')->cascadeOnDelete();
            });
        }

        if (Schema::hasColumn('employees', 'deleted_at')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
