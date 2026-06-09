<?php

namespace App\Http\Controllers;

use App\Enums\AbsenceType;
use App\Http\Requests\AttendanceStoreRequest;
use App\Models\Department;
use App\Models\EmployeeAbsence;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AttendanceController extends Controller
{
    public function __construct(
        private AttendanceService $attendanceService,
    ) {}

    /**
     * Daily attendance view — shows all active employees for a selected date.
     */
    public function index(Request $request)
    {
        Gate::authorize('manage attendance');

        $date = $request->input('date', now()->toDateString());
        $departmentId = $request->input('department_id');

        $employees = $this->attendanceService->getDailyStatus($date, $departmentId);

        $totalEmployees = $employees->count();
        $absentCount = $employees->filter(fn ($e) => $e->absences->isNotEmpty())->count();
        $presentCount = $totalEmployees - $absentCount;

        return view('attendance.index', [
            'date' => $date,
            'employees' => $employees,
            'departments' => Department::query()->orderBy('name')->get(),
            'selectedDepartmentId' => $departmentId,
            'absenceTypes' => AbsenceType::options(),
            'totalEmployees' => $totalEmployees,
            'presentCount' => $presentCount,
            'absentCount' => $absentCount,
        ]);
    }

    /**
     * Record one or more absences for a given date.
     */
    public function store(AttendanceStoreRequest $request)
    {
        Gate::authorize('manage attendance');

        $date = $request->absenceDate();
        $userId = $request->user()->id;

        foreach ($request->absenceData() as $absence) {
            $employee = \App\Models\Employee::findOrFail($absence['employee_id']);

            $this->attendanceService->recordAbsence(
                employee: $employee,
                date: $date,
                type: AbsenceType::from($absence['type']),
                reason: $absence['reason'] ?? null,
                recordedBy: $userId,
            );
        }

        return redirect()
            ->route('attendance.index', ['date' => $date])
            ->with('success', __('Attendance recorded successfully.'));
    }

    /**
     * Remove an absence record (mark employee as present).
     */
    public function destroy(EmployeeAbsence $employeeAbsence)
    {
        Gate::authorize('manage attendance');

        $date = $employeeAbsence->date->toDateString();

        $this->attendanceService->removeAbsence($employeeAbsence);

        return redirect()
            ->route('attendance.index', ['date' => $date])
            ->with('success', __('Employee marked as present.'));
    }

    /**
     * Monthly summary view — shows absence counts per employee for a selected month.
     */
    public function monthly(Request $request)
    {
        Gate::authorize('manage attendance');

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $departmentId = $request->input('department_id');

        $summaries = $this->attendanceService->getMonthlySummaries($month, $year, $departmentId);
        $workingDays = $this->attendanceService->getWorkingDaysInMonth($month, $year);

        return view('attendance.monthly', [
            'month' => $month,
            'year' => $year,
            'summaries' => $summaries,
            'workingDays' => $workingDays,
            'departments' => Department::query()->orderBy('name')->get(),
            'selectedDepartmentId' => $departmentId,
        ]);
    }
}
