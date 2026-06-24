<?php

namespace App\Http\Controllers;

use App\DataTables\EmployeeDataTable;
use App\Enums\EmployeeSalaryType;
use App\Enums\EmployeeStatus;
use App\Http\Requests\EmployeePayrollStoreRequest;
use App\Http\Requests\EmployeeSalaryPaymentStoreRequest;
use App\Http\Requests\EmployeeStoreRequest;
use App\Http\Requests\EmployeeUpdateRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\PaymentMethod;
use App\Models\Payroll;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function index(EmployeeDataTable $datatable)
    {
        Gate::authorize('view employees');

        return $datatable->render('employees.index');
    }

    public function create()
    {
        Gate::authorize('create employees');

        return view('employees.create', $this->formData());
    }

    public function store(EmployeeStoreRequest $request)
    {
        Gate::authorize('create employees');
        $employee = DB::transaction(function () use ($request) {
            $user = User::query()->create($request->userData());

            if ($request->has('role_name')) {
                $user->syncRoles([$request->input('role_name')]);
            }

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
        Gate::authorize('view employees');
        $employee->load([
            'user',
            'department',
            'monthlyReports' => fn ($query) => $query->latest('year')->latest('month'),
            'adjustments' => fn ($query) => $query->latest('year')->latest('month'),
            'payrolls' => fn ($query) => $query->latest('year')->latest('month'),
            'payments' => fn ($query) => $query->latest('paid_at')->latest(),
            'payments.creator',
            'payments.paymentMethod',
            'payments.payroll',
        ]);

        $attendanceService = app(AttendanceService::class);
        $attendanceSummary = $attendanceService->getMonthSummary($employee, now()->month, now()->year);

        return view('employees.show', compact('employee', 'attendanceSummary'));
    }

    public function createSalaryPayment(Employee $employee)
    {
        Gate::authorize('edit employees');
        $employee->load([
            'user',
            'department',
            'payrolls' => fn ($query) => $query
                ->where('status', 'draft')
                ->latest('year')
                ->latest('month'),
        ]);

        $payrolls = $employee->payrolls;

        return view('employees.salary-payments.create', [
            'employee' => $employee,
            'payrolls' => $payrolls,
            'latestPayroll' => $payrolls->first(),
            'paymentMethods' => PaymentMethod::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function createPayroll(Employee $employee)
    {
        Gate::authorize('edit employees');

        $month = (int) request('month', now()->month);
        $year = (int) request('year', now()->year);

        $employee->load([
            'user',
            'department',
            'monthlyReports' => fn ($query) => $query->where('month', $month)->where('year', $year),
            'payrolls' => fn ($query) => $query->where('month', $month)->where('year', $year),
        ]);

        $attendanceService = app(AttendanceService::class);
        $attendanceSummary = $attendanceService->getMonthSummary($employee, $month, $year);
        $absenceDeduction = $attendanceService->calculateAbsenceDeduction($employee, $month, $year);

        return view('employees.payrolls.create', [
            'employee' => $employee,
            'currentReport' => $employee->monthlyReports->first(),
            'currentPayroll' => $employee->payrolls->first(),
            'attendanceSummary' => $attendanceSummary,
            'absenceDeduction' => $absenceDeduction,
            'month' => $month,
            'year' => $year,
        ]);
    }

    public function storePayroll(EmployeePayrollStoreRequest $request, Employee $employee)
    {
        Gate::authorize('edit employees');
        DB::transaction(function () use ($request, $employee) {
            $period = $request->period();

            $employee->monthlyReports()->updateOrCreate($period, $request->reportData());
            $employee->payrolls()->updateOrCreate($period, $request->payrollData($employee));

            $this->syncPayrollAdjustment($employee, $period, 'bonus', 'Monthly payroll bonus', $request->bonusAmount(), $request->validated('notes'));
            $this->syncPayrollAdjustment($employee, $period, 'deduction', 'Monthly payroll deduction', $request->deductionAmount(), $request->validated('notes'));
        });

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', __('Payroll generated successfully.'));
    }

    public function storeSalaryPayment(EmployeeSalaryPaymentStoreRequest $request, Employee $employee)
    {
        Gate::authorize('edit employees');
        DB::transaction(function () use ($request, $employee) {
            $payroll = Payroll::query()
                ->where('employee_id', $employee->id)
                ->lockForUpdate()
                ->findOrFail($request->payrollId());

            if ($payroll->status === 'paid') {
                throw ValidationException::withMessages([
                    'payroll_id' => __('This payroll is already paid.'),
                ]);
            }

            $payment = $employee->payments()->create($request->paymentData());

            if ($payment->status === 'completed') {
                $this->syncPayrollPaymentStatus($payroll);
            }

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
        Gate::authorize('edit employees');
        $employee->load(['user', 'department']);

        return view('employees.edit', [
            'employee' => $employee,
            ...$this->formData(),
        ]);
    }

    public function update(EmployeeUpdateRequest $request, Employee $employee)
    {
        Gate::authorize('edit employees');
        DB::transaction(function () use ($request, $employee) {
            $employee->user->update($request->userData());

            if ($request->has('role_name')) {
                $employee->user->syncRoles([$request->input('role_name')]);
            }

            $employee->update($request->employeeData());
        });

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', __('Employee updated successfully.'));
    }

    public function destroy(Employee $employee)
    {
        Gate::authorize('delete employees');
        DB::transaction(function () use ($employee) {
            $employee->user->update(['is_active' => false]);
            $employee->delete();
        });

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
            'roles' => Role::query()
                ->orderBy('name')
                ->get(),
        ];
    }

    /**
     * @param  array{month: int, year: int}  $period
     */
    private function syncPayrollAdjustment(Employee $employee, array $period, string $type, string $reason, float $amount, ?string $notes): void
    {
        $query = $employee->adjustments()
            ->where($period)
            ->where('type', $type)
            ->where('reason', $reason);

        if ($amount <= 0) {
            $query->delete();

            return;
        }

        $employee->adjustments()->updateOrCreate([
            ...$period,
            'type' => $type,
            'reason' => $reason,
        ], [
            'amount' => $amount,
            'notes' => $notes,
        ]);
    }

    private function syncPayrollPaymentStatus(Payroll $payroll): void
    {
        $completedPayments = $payroll->payments()
            ->where('status', 'completed')
            ->sum('amount');

        if ((float) $completedPayments >= (float) $payroll->net_salary) {
            $payroll->update(['status' => 'paid']);
        }
    }
}
