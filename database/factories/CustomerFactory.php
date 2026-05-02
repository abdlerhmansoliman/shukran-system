<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Level;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $hasEmail = fake()->boolean(85);
        $customerType = fake()->randomElement(['new', 'old']);
        $staffIds = User::query()->pluck('id');

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $hasEmail ? fake()->unique()->safeEmail() : null,
            'phone' => fake()->numerify('+20 1#########'),
            'second_phone_number' => fake()->optional()->numerify('+20 1#########'),
            'status' => fake()->randomElement(['active', 'inactive']),
            'source' => fake()->randomElement([
                'website',
                'whatsapp',
                'facebook',
                'instagram',
                'referral',
                'sales call',
            ]),
            'customer_type' => $customerType,
            'placement_month' => fake()->optional(80)->dateTimeBetween('-4 months', 'now')?->format('Y-m-01'),
            'tester_id' => $staffIds->isNotEmpty() ? $staffIds->random() : null,
            'old_instructor_id' => $customerType === 'old' && $staffIds->isNotEmpty()
                ? $staffIds->random()
                : null,
            'notes' => fake()->boolean(70) ? fake()->sentence() : null,
            'level_id' => Level::query()->inRandomOrder()->value('id'),
            'category_id' => Category::query()->inRandomOrder()->value('id'),
            'created_by' => null,
            'age' => fake()->numberBetween(6, 55),
            'gender' => fake()->randomElement(['male', 'female']),
            'address' => fake()->address(),
            'country_id' => Country::query()->inRandomOrder()->value('id'),
        ];
    }
}
