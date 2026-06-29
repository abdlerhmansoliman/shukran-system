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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->integer('age')->nullable();
            $table->string('gender')->nullable();
            $table->foreignId('entry_level_id')->nullable()->constrained('levels')->nullOnDelete();
            $table->foreignId('current_level_id')->nullable()->constrained('levels')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('tester_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('placement_month')->nullable();
            $table->string('job')->nullable();
            $table->string('college')->nullable();
            $table->string('progress_report_link', 2000)->nullable();
            $table->date('test_date')->nullable();
            $table->foreignId('agreed_package_id')->nullable()->constrained('packages')->nullOnDelete();
            $table->decimal('agreed_amount', 10, 2)->nullable();
            $table->string('keywords')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Populate the profiles table with data from the customers table
        $customers = DB::table('customers')->get();
        foreach ($customers as $customer) {
            DB::table('profiles')->insert([
                'customer_id' => $customer->id,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'age' => $customer->age ?? null,
                'gender' => $customer->gender ?? null,
                'entry_level_id' => $customer->entry_level_id ?? null,
                'current_level_id' => $customer->current_level_id ?? null,
                'category_id' => $customer->category_id ?? null,
                'tester_id' => $customer->tester_id ?? null,
                'placement_month' => $customer->placement_month ?? null,
                'job' => $customer->job ?? null,
                'college' => $customer->college ?? null,
                'progress_report_link' => $customer->progress_report_link ?? null,
                'test_date' => $customer->test_date ?? null,
                'agreed_package_id' => $customer->agreed_package_id ?? null,
                'agreed_amount' => $customer->agreed_amount ?? null,
                'keywords' => $customer->keywords ?? null,
                'notes' => $customer->notes ?? null,
                'created_at' => $customer->created_at ?? now(),
                'updated_at' => $customer->updated_at ?? now(),
            ]);
        }

        // Add profile_id to customer_packages, group_enrollments, and customer_feedbacks
        Schema::table('customer_packages', function (Blueprint $table) {
            $table->foreignId('profile_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
        });

        Schema::table('group_enrollments', function (Blueprint $table) {
            $table->foreignId('profile_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
        });

        Schema::table('customer_feedbacks', function (Blueprint $table) {
            $table->foreignId('profile_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
        });

        // Map existing customer packages, group enrollments, and feedbacks to their customer's profile
        DB::statement('UPDATE customer_packages SET profile_id = (SELECT id FROM profiles WHERE profiles.customer_id = customer_packages.customer_id)');
        DB::statement('UPDATE group_enrollments SET profile_id = (SELECT id FROM profiles WHERE profiles.customer_id = group_enrollments.customer_id)');
        DB::statement('UPDATE customer_feedbacks SET profile_id = (SELECT id FROM profiles WHERE profiles.customer_id = customer_feedbacks.customer_id)');

        // Drop academic/profile columns from customers table
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['entry_level_id']);
            $table->dropForeign(['current_level_id']);
            $table->dropForeign(['category_id']);
            $table->dropForeign(['tester_id']);
            $table->dropForeign(['agreed_package_id']);

            $table->dropColumn([
                'age',
                'gender',
                'entry_level_id',
                'current_level_id',
                'category_id',
                'tester_id',
                'placement_month',
                'job',
                'college',
                'progress_report_link',
                'test_date',
                'agreed_package_id',
                'agreed_amount',
                'keywords',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->integer('age')->nullable();
            $table->string('gender')->nullable();
            $table->foreignId('entry_level_id')->nullable()->constrained('levels')->nullOnDelete();
            $table->foreignId('current_level_id')->nullable()->constrained('levels')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('tester_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('placement_month')->nullable();
            $table->string('job')->nullable();
            $table->string('college')->nullable();
            $table->string('progress_report_link', 2000)->nullable();
            $table->date('test_date')->nullable();
            $table->foreignId('agreed_package_id')->nullable()->constrained('packages')->nullOnDelete();
            $table->decimal('agreed_amount', 10, 2)->nullable();
            $table->string('keywords')->nullable();
        });

        // Copy profile details back to customer table
        $profiles = DB::table('profiles')->get()->groupBy('customer_id');
        foreach ($profiles as $customerId => $customerProfiles) {
            $firstProfile = $customerProfiles->first();
            if ($firstProfile) {
                DB::table('customers')->where('id', $customerId)->update([
                    'age' => $firstProfile->age,
                    'gender' => $firstProfile->gender,
                    'entry_level_id' => $firstProfile->entry_level_id,
                    'current_level_id' => $firstProfile->current_level_id,
                    'category_id' => $firstProfile->category_id,
                    'tester_id' => $firstProfile->tester_id,
                    'placement_month' => $firstProfile->placement_month,
                    'job' => $firstProfile->job,
                    'college' => $firstProfile->college,
                    'progress_report_link' => $firstProfile->progress_report_link,
                    'test_date' => $firstProfile->test_date,
                    'agreed_package_id' => $firstProfile->agreed_package_id,
                    'agreed_amount' => $firstProfile->agreed_amount,
                    'keywords' => $firstProfile->keywords,
                ]);
            }
        }

        Schema::table('customer_packages', function (Blueprint $table) {
            $table->dropForeign(['profile_id']);
            $table->dropColumn('profile_id');
        });

        Schema::table('group_enrollments', function (Blueprint $table) {
            $table->dropForeign(['profile_id']);
            $table->dropColumn('profile_id');
        });

        Schema::table('customer_feedbacks', function (Blueprint $table) {
            $table->dropForeign(['profile_id']);
            $table->dropColumn('profile_id');
        });

        Schema::dropIfExists('profiles');
    }
};
