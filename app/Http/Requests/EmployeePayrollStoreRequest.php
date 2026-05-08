<?php

namespace App\Http\Requests;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeePayrollStoreRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'month' => $this->input('month', now()->month),
            'year' => $this->input('year', now()->year),
            'required_working_days' => $this->input('required_working_days', 0),
            'required_working_hours' => $this->input('required_working_hours', 0),
            'actual_worked_days' => $this->input('actual_worked_days', 0),
            'actual_worked_hours' => $this->input('actual_worked_hours', 0),
            'overtime_hours' => $this->input('overtime_hours', 0),
            'absence_deduction' => $this->input('absence_deduction', 0),
            'overtime_amount' => $this->input('overtime_amount', 0),
            'total_bonus' => $this->input('total_bonus', 0),
            'total_deductions' => $this->input('total_deductions', 0),
            'status' => $this->input('status', 'draft'),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'required_working_days' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'required_working_hours' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'actual_worked_days' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'actual_worked_hours' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'overtime_hours' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'absence_deduction' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'overtime_amount' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'total_bonus' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'total_deductions' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'status' => ['required', Rule::in(['draft', 'paid'])],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $employee = $this->route('employee');

            if (! $employee instanceof Employee) {
                return;
            }

            if ($this->netSalary($employee) < 0) {
                $validator->errors()->add('total_deductions', __('The net salary cannot be less than zero.'));
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function reportData(): array
    {
        $validated = $this->validated();

        return [
            'month' => (int) $validated['month'],
            'year' => (int) $validated['year'],
            'required_working_days' => $this->decimalValue('required_working_days'),
            'required_working_hours' => $this->decimalValue('required_working_hours'),
            'actual_worked_days' => $this->decimalValue('actual_worked_days'),
            'actual_worked_hours' => $this->decimalValue('actual_worked_hours'),
            'overtime_hours' => $this->decimalValue('overtime_hours'),
            'notes' => $validated['notes'] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function payrollData(Employee $employee): array
    {
        $validated = $this->validated();
        $calculation = $this->calculation($employee);

        return [
            'month' => (int) $validated['month'],
            'year' => (int) $validated['year'],
            'basic_salary' => $calculation['base_salary'],
            'hour_salary' => $calculation['hour_salary'],
            'absence_deduction' => $this->decimalValue('absence_deduction'),
            'overtime_amount' => $this->decimalValue('overtime_amount'),
            'total_bonus' => $this->decimalValue('total_bonus'),
            'total_deductions' => $this->decimalValue('total_deductions'),
            'net_salary' => $calculation['net_salary'],
            'status' => $validated['status'],
        ];
    }

    public function bonusAmount(): float
    {
        return $this->decimalValue('total_bonus');
    }

    public function deductionAmount(): float
    {
        return $this->decimalValue('total_deductions');
    }

    public function period(): array
    {
        $validated = $this->validated();

        return [
            'month' => (int) $validated['month'],
            'year' => (int) $validated['year'],
        ];
    }

    private function netSalary(Employee $employee): float
    {
        return $this->calculation($employee)['net_salary'];
    }

    /**
     * @return array{base_salary: float, hour_salary: float, net_salary: float}
     */
    private function calculation(Employee $employee): array
    {
        $requiredHours = $this->decimalValue('required_working_hours');
        $actualHours = $this->decimalValue('actual_worked_hours');
        $employeeSalary = (float) $employee->basic_salary;

        $hourSalary = $employee->salary_type === 'hourly'
            ? $employeeSalary
            : ($requiredHours > 0 ? round($employeeSalary / $requiredHours, 2) : 0);

        $baseSalary = $employee->salary_type === 'hourly'
            ? round($actualHours * $hourSalary, 2)
            : round($employeeSalary, 2);

        $netSalary = $baseSalary
            + $this->decimalValue('overtime_amount')
            + $this->decimalValue('total_bonus')
            - $this->decimalValue('absence_deduction')
            - $this->decimalValue('total_deductions');

        return [
            'base_salary' => round($baseSalary, 2),
            'hour_salary' => round($hourSalary, 2),
            'net_salary' => round($netSalary, 2),
        ];
    }

    private function decimalValue(string $key): float
    {
        return round((float) $this->input($key, 0), 2);
    }
}
