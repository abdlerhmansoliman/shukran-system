<?php

namespace App\Http\Requests;

use App\Enums\EmployeeSalaryType;
use App\Enums\EmployeeStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class EmployeeStoreRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'salary_type' => $this->input('salary_type', EmployeeSalaryType::Monthly->value),
            'status' => $this->input('status', EmployeeStatus::Active->value),
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', Password::defaults()],
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

        return [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ];
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
