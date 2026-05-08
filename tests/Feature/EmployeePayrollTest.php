<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeePayrollTest extends TestCase
{
    use RefreshDatabase;

    public function test_monthly_payroll_can_be_generated(): void
    {
        $admin = User::factory()->create();
        $employee = $this->employee();

        $response = $this
            ->actingAs($admin)
            ->post(route('employees.payrolls.store', $employee), [
                'month' => 5,
                'year' => 2026,
                'required_working_days' => 22,
                'required_working_hours' => 176,
                'actual_worked_days' => 21,
                'actual_worked_hours' => 168,
                'overtime_hours' => 2,
                'absence_deduction' => 250,
                'overtime_amount' => 100,
                'total_bonus' => 500,
                'total_deductions' => 100,
                'status' => 'draft',
                'notes' => 'May payroll',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('employees.show', $employee));

        $this->assertDatabaseHas('employee_monthly_reports', [
            'employee_id' => $employee->id,
            'month' => '5',
            'year' => 2026,
            'actual_worked_hours' => '168.00',
            'notes' => 'May payroll',
        ]);

        $this->assertDatabaseHas('payrolls', [
            'employee_id' => $employee->id,
            'month' => '5',
            'year' => 2026,
            'basic_salary' => '10000.00',
            'absence_deduction' => '250.00',
            'overtime_amount' => '100.00',
            'total_bonus' => '500.00',
            'total_deductions' => '100.00',
            'net_salary' => '10250.00',
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('employee_adjustments', [
            'employee_id' => $employee->id,
            'month' => '5',
            'year' => 2026,
            'type' => 'bonus',
            'amount' => '500.00',
            'reason' => 'Monthly payroll bonus',
        ]);

        $this->assertDatabaseHas('employee_adjustments', [
            'employee_id' => $employee->id,
            'month' => '5',
            'year' => 2026,
            'type' => 'deduction',
            'amount' => '100.00',
            'reason' => 'Monthly payroll deduction',
        ]);
    }

    public function test_completed_salary_payment_marks_month_payroll_as_paid(): void
    {
        $admin = User::factory()->create();
        $employee = $this->employee();
        $paymentMethod = PaymentMethod::query()->create([
            'name' => 'Bank',
            'status' => 'active',
        ]);
        $employee->payrolls()->create([
            'month' => 5,
            'year' => 2026,
            'basic_salary' => 10000,
            'hour_salary' => 0,
            'absence_deduction' => 0,
            'overtime_amount' => 0,
            'total_bonus' => 0,
            'total_deductions' => 0,
            'net_salary' => 10000,
            'status' => 'draft',
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('employees.salary-payments.store', $employee), [
                'amount' => 10000,
                'bonus_amount' => 0,
                'deduction_amount' => 0,
                'paid_at' => '2026-05-08',
                'status' => 'completed',
                'payment_method_id' => $paymentMethod->id,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('employees.show', $employee));

        $this->assertDatabaseHas('payrolls', [
            'employee_id' => $employee->id,
            'month' => '5',
            'year' => 2026,
            'status' => 'paid',
        ]);
    }

    private function employee(): Employee
    {
        $department = Department::query()->create(['name' => 'Operations']);
        $employeeUser = User::factory()->create();

        return Employee::query()->create([
            'user_id' => $employeeUser->id,
            'department_id' => $department->id,
            'basic_salary' => 10000,
            'salary_type' => 'monthly',
            'status' => 'active',
        ]);
    }
}
