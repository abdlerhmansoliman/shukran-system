<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EmployeeCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_be_created(): void
    {
        $admin = User::factory()->create();
        $department = Department::query()->create(['name' => 'Operations']);

        $response = $this
            ->actingAs($admin)
            ->post(route('employees.store'), [
                'name' => 'Nour Ahmed',
                'email' => 'nour@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'department_id' => $department->id,
                'age' => 28,
                'phone' => '+20 1000000099',
                'job_title' => 'Operations Specialist',
                'basic_salary' => 12500,
                'salary_type' => 'monthly',
                'hire_date' => '2026-04-01',
                'status' => 'active',
            ]);

        $employee = Employee::query()->whereHas('user', fn ($query) => $query->where('email', 'nour@example.com'))->firstOrFail();

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('employees.show', $employee));

        $this->assertDatabaseHas('users', [
            'name' => 'Nour Ahmed',
            'email' => 'nour@example.com',
        ]);

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'department_id' => $department->id,
            'job_title' => 'Operations Specialist',
            'status' => 'active',
        ]);
    }

    public function test_employee_can_be_updated_without_changing_password(): void
    {
        $admin = User::factory()->create();
        $department = Department::query()->create(['name' => 'Teaching']);
        $newDepartment = Department::query()->create(['name' => 'Customer Success']);
        $employeeUser = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old.employee@example.com',
            'password' => Hash::make('old-password'),
        ]);
        $employee = Employee::query()->create([
            'user_id' => $employeeUser->id,
            'department_id' => $department->id,
            'age' => 30,
            'phone' => '123',
            'job_title' => 'Instructor',
            'basic_salary' => 10000,
            'salary_type' => 'monthly',
            'hire_date' => '2026-01-01',
            'status' => 'active',
        ]);

        $response = $this
            ->actingAs($admin)
            ->put(route('employees.update', $employee), [
                'name' => 'Updated Employee',
                'email' => 'updated.employee@example.com',
                'department_id' => $newDepartment->id,
                'age' => 31,
                'phone' => '456',
                'job_title' => 'Senior Instructor',
                'basic_salary' => 15000,
                'salary_type' => 'hourly',
                'hire_date' => '2026-02-01',
                'status' => 'inactive',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('employees.show', $employee));

        $employee->refresh();
        $employeeUser->refresh();

        $this->assertSame('Updated Employee', $employeeUser->name);
        $this->assertSame('updated.employee@example.com', $employeeUser->email);
        $this->assertTrue(Hash::check('old-password', $employeeUser->password));
        $this->assertSame($newDepartment->id, $employee->department_id);
        $this->assertSame('Senior Instructor', $employee->job_title);
        $this->assertSame('hourly', $employee->salary_type);
        $this->assertSame('inactive', $employee->status);
    }

    public function test_employee_can_be_deleted_without_deleting_user_account(): void
    {
        $admin = User::factory()->create();
        $department = Department::query()->create(['name' => 'Finance']);
        $employeeUser = User::factory()->create();
        $employee = Employee::query()->create([
            'user_id' => $employeeUser->id,
            'department_id' => $department->id,
            'basic_salary' => 10000,
            'salary_type' => 'monthly',
            'status' => 'active',
        ]);

        $response = $this
            ->actingAs($admin)
            ->delete(route('employees.destroy', $employee));

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('employees.index'));

        $this->assertSoftDeleted('employees', ['id' => $employee->id]);
        $this->assertDatabaseHas('users', ['id' => $employeeUser->id]);
    }
}
