<?php

namespace App\Http\Controllers;

use App\DataTables\EmployeeDataTable;
use App\Enums\EmployeeSalaryType;
use App\Enums\EmployeeStatus;
use App\Http\Requests\EmployeeStoreRequest;
use App\Http\Requests\EmployeeUpdateRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function index(EmployeeDataTable $datatable)
    {
        return $datatable->render('employees.index');
    }

    public function create()
    {
        return view('employees.create', $this->formData());
    }

    public function store(EmployeeStoreRequest $request)
    {
        $employee = DB::transaction(function () use ($request) {
            $user = User::query()->create($request->userData());

            return Employee::query()->create([
                ...$request->employeeData(),
                'user_id' => $user->id,
            ]);
        });

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', __('Employee created successfully.'));
    }

    public function show(Employee $employee)
    {
        $employee->load([
            'user',
            'department',
            'monthlyReports' => fn ($query) => $query->latest('year')->latest('month'),
            'adjustments' => fn ($query) => $query->latest('year')->latest('month'),
            'payrolls' => fn ($query) => $query->latest('year')->latest('month'),
        ]);

        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $employee->load(['user', 'department']);

        return view('employees.edit', [
            'employee' => $employee,
            ...$this->formData(),
        ]);
    }

    public function update(EmployeeUpdateRequest $request, Employee $employee)
    {
        DB::transaction(function () use ($request, $employee) {
            $employee->user()->update($request->userData());
            $employee->update($request->employeeData());
        });

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', __('Employee updated successfully.'));
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()
            ->route('employees.index')
            ->with('success', __('Employee deleted successfully.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'departments' => Department::query()
                ->orderBy('name')
                ->get(),
            'salaryTypes' => EmployeeSalaryType::options(),
            'statuses' => EmployeeStatus::options(),
        ];
    }
}
