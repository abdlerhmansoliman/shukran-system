<?php

namespace App\Services;

use App\Enums\AbsenceType;
use App\Models\Employee;
use App\Models\EmployeeAbsence;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AttendanceService
{
    /**
     * Record or update an absence for an employee on a specific date.
     */
    public function recordAbsence(
        Employee $employee,
        string $date,
        AbsenceType $type,
        ?string $reason = null,
        ?string $notes = null,
        ?int $recordedBy = null,
    ): EmployeeAbsence {
        return $employee->absences()->updateOrCreate(
            ['date' => $date],
            [
                'type' => $type->value,
                'reason' => $reason,
                'notes' => $notes,
                'recorded_by' => $recordedBy,
            ],
        );
    }

    /**
     * Remove an absence record (mark employee as present).
     */
    public function removeAbsence(EmployeeAbsence $absence): void
    {
        $absence->delete();
    }

    /**
     * Get all active employees with their absence status for a given date.
     *
     * @return Collection<int, Employee>
     */
    public function getDailyStatus(string $date, ?int $departmentId = null): Collection
    {
        return Employee::query()
            ->where('status', 'active')
            ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
            ->with([
                'user',
                'department',
                'absences' => fn ($q) => $q->where('date', $date),
            ])
            ->orderBy('id')
            ->get();
    }

    /**
     * Get a monthly attendance summary for a single employee.
     *
     * @return array{working_days: int, absences: Collection, total_absent_days: float, present_days: float}
     */
    public function getMonthSummary(Employee $employee, int $month, int $year): array
    {
        $workingDays = $this->getWorkingDaysInMonth($month, $year);

        $absences = $employee->absences()
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date')
            ->get();

        $totalAbsentDays = $absences->sum(fn (EmployeeAbsence $a) => $a->type->deductionWeight());

        return [
            'working_days' => $workingDays,
            'absences' => $absences,
            'total_absent_days' => round($totalAbsentDays, 2),
            'present_days' => round(max(0, $workingDays - $totalAbsentDays), 2),
        ];
    }

    /**
     * Get monthly attendance summaries for all active employees.
     *
     * @return Collection<int, array{employee: Employee, working_days: int, absences: Collection, total_absent_days: float, present_days: float}>
     */
    public function getMonthlySummaries(int $month, int $year, ?int $departmentId = null): Collection
    {
        $employees = Employee::query()
            ->where('status', 'active')
            ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
            ->with([
                'user',
                'department',
                'absences' => fn ($q) => $q->whereMonth('date', $month)->whereYear('date', $year)->orderBy('date'),
            ])
            ->orderBy('id')
            ->get();

        $workingDays = $this->getWorkingDaysInMonth($month, $year);

        return $employees->map(function (Employee $employee) use ($workingDays) {
            $totalAbsentDays = $employee->absences->sum(fn (EmployeeAbsence $a) => $a->type->deductionWeight());

            return [
                'employee' => $employee,
                'working_days' => $workingDays,
                'absences' => $employee->absences,
                'total_absent_days' => round($totalAbsentDays, 2),
                'present_days' => round(max(0, $workingDays - $totalAbsentDays), 2),
            ];
        });
    }

    /**
     * Calculate the absence deduction amount for a given employee and month.
     * Used when generating payrolls.
     */
    public function calculateAbsenceDeduction(Employee $employee, int $month, int $year): float
    {
        $summary = $this->getMonthSummary($employee, $month, $year);

        if ($summary['total_absent_days'] <= 0 || $summary['working_days'] <= 0) {
            return 0;
        }

        $dailyRate = (float) $employee->basic_salary / $summary['working_days'];

        return round($dailyRate * $summary['total_absent_days'], 2);
    }

    /**
     * Calculate the number of working days in a given month.
     */
    public function getWorkingDaysInMonth(int $month, int $year): int
    {
        $workingDaysOfWeek = config('attendance.working_days', [0, 1, 2, 3, 4]);
        $start = Carbon::create($year, $month, 1);
        $end = $start->copy()->endOfMonth();

        $count = 0;

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if (in_array($date->dayOfWeek, $workingDaysOfWeek)) {
                $count++;
            }
        }

        return $count;
    }
}
