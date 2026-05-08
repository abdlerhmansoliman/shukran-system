<?php

namespace App\Http\Controllers;

use App\DataTables\EmployeeDataTable;
use App\Enums\EmployeeSalaryType;
use App\Enums\EmployeeStatus;
use App\Http\Requests\EmployeeSalaryPaymentStoreRequest;
use App\Http\Requests\EmployeeStoreRequest;
use App\Http\Requests\EmployeeUpdateRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Support\Carbon;
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
            'payments' => fn ($query) => $query->latest('paid_at')->latest(),
            'payments.creator',
            'payments.paymentMethod',
        ]);

        return view('employees.show', compact('employee'));
    }

    public function createSalaryPayment(Employee $employee)
    {
        $employee->load([
            'user',
            'department',
            'payrolls' => fn ($query) => $query->latest('year')->latest('month'),
        ]);

        return view('employees.salary-payments.create', [
            'employee' => $employee,
            'latestPayroll' => $employee->payrolls->first(),
            'paymentMethods' => PaymentMethod::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function storeSalaryPayment(EmployeeSalaryPaymentStoreRequest $request, Employee $employee)
    {
        DB::transaction(function () use ($request, $employee) {
            $employee->payments()->create($request->paymentData());
            $paidAt = Carbon::parse($request->validated('paid_at'));

            if ($request->bonusAmount() > 0) {
                $employee->adjustments()->create([
                    'month' => $paidAt->month,
                    'year' => $paidAt->year,
                    'type' => 'bonus',
                    'amount' => $request->bonusAmount(),
                    'reason' => 'Salary payment bonus',
                    'notes' => $request->validated('notes'),
                ]);
            }

            if ($request->deductionAmount() > 0) {
                $employee->adjustments()->create([
                    'month' => $paidAt->month,
                    'year' => $paidAt->year,
                    'type' => 'deduction',
                    'amount' => $request->deductionAmount(),
                    'reason' => 'Salary payment deduction',
                    'notes' => $request->validated('notes'),
                ]);
            }
        });

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', __('Salary payment recorded successfully.'));
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
