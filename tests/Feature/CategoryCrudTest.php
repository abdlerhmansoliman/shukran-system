<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_can_be_created_with_parent(): void
    {
        $admin = User::factory()->create();
        $parent = Category::query()->create(['name' => 'Kids']);

        $response = $this
            ->actingAs($admin)
            ->post(route('categories.store'), [
                'name' => '6-8',
                'parent_id' => $parent->id,
            ]);

        $category = Category::query()->where('name', '6-8')->firstOrFail();

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('categories.edit', $category));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => '6-8',
            'parent_id' => $parent->id,
        ]);
    }

    public function test_category_can_be_updated(): void
    {
        $admin = User::factory()->create();
        $oldParent = Category::query()->create(['name' => 'Kids']);
        $newParent = Category::query()->create(['name' => 'Adults']);
        $category = Category::query()->create([
            'name' => '6-8',
            'parent_id' => $oldParent->id,
        ]);

        $response = $this
            ->actingAs($admin)
            ->put(route('categories.update', $category), [
                'name' => 'Beginners',
                'parent_id' => $newParent->id,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('categories.edit', $category));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Beginners',
            'parent_id' => $newParent->id,
        ]);
    }

    public function test_category_cannot_be_deleted_while_it_has_children(): void
    {
        $admin = User::factory()->create();
        $category = Category::query()->create(['name' => 'Kids']);
        Category::query()->create([
            'name' => '6-8',
            'parent_id' => $category->id,
        ]);

        $response = $this
            ->actingAs($admin)
            ->delete(route('categories.destroy', $category));

        $response
            ->assertSessionHas('error')
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_category_cannot_be_deleted_while_assigned(): void
    {
        $admin = User::factory()->create();
        $category = Category::query()->create(['name' => 'Adults']);

        Customer::query()->create([
            'first_name' => 'Nour',
            'last_name' => 'Ahmed',
            'phone' => '+20 1000000011',
            'status' => 'active',
            'customer_type' => 'new',
            'category_id' => $category->id,
        ]);

        Group::query()->create([
            'name' => 'Saturday A',
            'status' => 'planned',
            'category_id' => $category->id,
        ]);

        $response = $this
            ->actingAs($admin)
            ->delete(route('categories.destroy', $category));

        $response
            ->assertSessionHas('error')
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
        ]);
    }
}
