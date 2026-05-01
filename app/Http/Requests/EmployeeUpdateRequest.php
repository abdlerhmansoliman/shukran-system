<?php

namespace App\Http\Requests;

use App\Enums\EmployeeSalaryType;
use App\Enums\EmployeeStatus;
use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class EmployeeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $employee = $this->route('employee');
        $userId = $employee instanceof Employee ? $employee->user_id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'department_id' => ['required', 'exists:departments,id'],
            'age' => ['nullable', 'integer', 'min:16', 'max:100'],
            'phone' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'basic_salary' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'salary_type' => ['required', Rule::in(EmployeeSalaryType::values())],
            'hire_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(EmployeeStatus::values())],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function userData(): array
    {
        $validated = $this->validated();
        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function employeeData(): array
    {
        $validated = $this->validated();

        return [
            'department_id' => $validated['department_id'],
            'age' => $validated['age'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'job_title' => $validated['job_title'] ?? null,
            'basic_salary' => $validated['basic_salary'],
            'salary_type' => $validated['salary_type'],
            'hire_date' => $validated['hire_date'] ?? null,
            'status' => $validated['status'],
        ];
    }
}
