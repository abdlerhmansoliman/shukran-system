<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Country;
use App\Models\Level;
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

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $hasEmail ? fake()->unique()->safeEmail() : null,
            'phone' => fake()->numerify('+20 1#########'),
            'status' => fake()->randomElement(['active', 'inactive']),
            'source' => fake()->randomElement([
                'website',
                'whatsapp',
                'facebook',
                'instagram',
                'referral',
                'sales call',
            ]),
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
