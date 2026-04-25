<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeAdjustment;
use App\Models\EmployeeMonthlyReport;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeePayrollSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = collect([
            'Operations',
            'Customer Success',
            'Teaching',
            'Sales',
            'Finance',
        ])->mapWithKeys(fn (string $name) => [
            $name => Department::query()->updateOrCreate(['name' => $name]),
        ]);

        $employees = [
            [
                'name' => 'Dania Hassan',
                'email' => 'dania@example.com',
                'department' => 'Customer Success',
                'age' => 27,
                'phone' => '+20 1000000001',
                'job_title' => 'Customer Success Specialist',
                'basic_salary' => 15000,
                'salary_type' => 'monthly',
                'hire_date' => '2025-09-01',
                'status' => 'active',
                'reports' => [
                    ['month' => 3, 'year' => 2026, 'required_days' => 26, 'actual_days' => 24.5, 'required_hours' => 208, 'worked_hours' => 196, 'overtime_hours' => 4, 'status' => 'paid'],
                    ['month' => 4, 'year' => 2026, 'required_days' => 26, 'actual_days' => 25, 'required_hours' => 208, 'worked_hours' => 200, 'overtime_hours' => 2, 'status' => 'draft'],
                ],
                'adjustments' => [
                    ['month' => 3, 'year' => 2026, 'type' => 'bonus', 'amount' => 500, 'reason' => 'Performance Bonus'],
                    ['month' => 3, 'year' => 2026, 'type' => 'deduction', 'amount' => 100, 'reason' => 'Late Deduction'],
                    ['month' => 4, 'year' => 2026, 'type' => 'bonus', 'amount' => 300, 'reason' => 'Customer Satisfaction Bonus'],
                ],
            ],
            [
                'name' => 'Menna Ali',
                'email' => 'menna@example.com',
                'department' => 'Teaching',
                'age' => 31,
                'phone' => '+20 1000000002',
                'job_title' => 'Senior Instructor',
                'basic_salary' => 18000,
                'salary_type' => 'monthly',
                'hire_date' => '2024-11-15',
                'status' => 'active',
                'reports' => [
                    ['month' => 3, 'year' => 2026, 'required_days' => 26, 'actual_days' => 26, 'required_hours' => 208, 'worked_hours' => 208, 'overtime_hours' => 8, 'status' => 'paid'],
                    ['month' => 4, 'year' => 2026, 'required_days' => 26, 'actual_days' => 25.5, 'required_hours' => 208, 'worked_hours' => 204, 'overtime_hours' => 3, 'status' => 'draft'],
                ],
                'adjustments' => [
                    ['month' => 3, 'year' => 2026, 'type' => 'bonus', 'amount' => 750, 'reason' => 'Excellent Student Feedback'],
                    ['month' => 4, 'year' => 2026, 'type' => 'deduction', 'amount' => 50, 'reason' => 'Schedule Change Deduction'],
                ],
            ],
            [
                'name' => 'Rowan Nabil',
                'email' => 'rowan@example.com',
                'department' => 'Sales',
                'age' => 25,
                'phone' => '+20 1000000003',
                'job_title' => 'Sales Representative',
                'basic_salary' => 12000,
                'salary_type' => 'monthly',
                'hire_date' => '2025-02-10',
                'status' => 'active',
                'reports' => [
                    ['month' => 3, 'year' => 2026, 'required_days' => 26, 'actual_days' => 23.5, 'required_hours' => 208, 'worked_hours' => 188, 'overtime_hours' => 0, 'status' => 'paid'],
                    ['month' => 4, 'year' => 2026, 'required_days' => 26, 'actual_days' => 24, 'required_hours' => 208, 'worked_hours' => 192, 'overtime_hours' => 5, 'status' => 'draft'],
                ],
                'adjustments' => [
                    ['month' => 3, 'year' => 2026, 'type' => 'bonus', 'amount' => 1200, 'reason' => 'Sales Commission'],
                    ['month' => 3, 'year' => 2026, 'type' => 'deduction', 'amount' => 150, 'reason' => 'Attendance Deduction'],
                    ['month' => 4, 'year' => 2026, 'type' => 'bonus', 'amount' => 900, 'reason' => 'Sales Commission'],
                ],
            ],
            [
                'name' => 'Sara Mahmoud',
                'email' => 'sara@example.com',
                'department' => 'Finance',
                'age' => 29,
                'phone' => '+20 1000000004',
                'job_title' => 'Accountant',
                'basic_salary' => 16000,
                'salary_type' => 'monthly',
                'hire_date' => '2024-05-20',
                'status' => 'active',
                'reports' => [
                    ['month' => 3, 'year' => 2026, 'required_days' => 26, 'actual_days' => 26, 'required_hours' => 208, 'worked_hours' => 208, 'overtime_hours' => 2, 'status' => 'paid'],
                    ['month' => 4, 'year' => 2026, 'required_days' => 26, 'actual_days' => 26, 'required_hours' => 208, 'worked_hours' => 208, 'overtime_hours' => 0, 'status' => 'draft'],
                ],
                'adjustments' => [
                    ['month' => 3, 'year' => 2026, 'type' => 'bonus', 'amount' => 400, 'reason' => 'Closing Bonus'],
                ],
            ],
            [
                'name' => 'Omar Youssef',
                'email' => 'omar.employee@example.com',
                'department' => 'Operations',
                'age' => 34,
                'phone' => '+20 1000000005',
                'job_title' => 'Operations Coordinator',
                'basic_salary' => 14000,
                'salary_type' => 'monthly',
                'hire_date' => '2023-08-05',
                'status' => 'active',
                'reports' => [
                    ['month' => 3, 'year' => 2026, 'required_days' => 26, 'actual_days' => 25, 'required_hours' => 208, 'worked_hours' => 200, 'overtime_hours' => 6, 'status' => 'paid'],
                    ['month' => 4, 'year' => 2026, 'required_days' => 26, 'actual_days' => 24.5, 'required_hours' => 208, 'worked_hours' => 196, 'overtime_hours' => 4, 'status' => 'draft'],
                ],
                'adjustments' => [
                    ['month' => 3, 'year' => 2026, 'type' => 'deduction', 'amount' => 75, 'reason' => 'Late Deduction'],
                    ['month' => 4, 'year' => 2026, 'type' => 'bonus', 'amount' => 250, 'reason' => 'Extra Shift Bonus'],
                    ['month' => 4, 'year' => 2026, 'type' => 'deduction', 'amount' => 50, 'reason' => 'Other Deduction'],
                ],
            ],
            [
                'name' => 'Laila Samir',
                'email' => 'laila.employee@example.com',
                'department' => 'Teaching',
                'age' => 24,
                'phone' => '+20 1000000006',
                'job_title' => 'Junior Instructor',
                'basic_salary' => 10000,
                'salary_type' => 'monthly',
                'hire_date' => '2026-01-12',
                'status' => 'inactive',
                'reports' => [
                    ['month' => 3, 'year' => 2026, 'required_days' => 26, 'actual_days' => 22, 'required_hours' => 208, 'worked_hours' => 176, 'overtime_hours' => 0, 'status' => 'paid'],
                ],
                'adjustments' => [
                    ['month' => 3, 'year' => 2026, 'type' => 'deduction', 'amount' => 200, 'reason' => 'Unapproved Absence'],
                ],
            ],
        ];

        foreach ($employees as $employeeData) {
            $user = User::query()->updateOrCreate(
                ['email' => $employeeData['email']],
                [
                    'name' => $employeeData['name'],
                    'password' => Hash::make('12345678'),
                ]
            );

            $employee = Employee::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'department_id' => $departments[$employeeData['department']]->id,
                    'age' => $employeeData['age'],
                    'phone' => $employeeData['phone'],
                    'job_title' => $employeeData['job_title'],
                    'basic_salary' => $employeeData['basic_salary'],
                    'salary_type' => $employeeData['salary_type'],
                    'hire_date' => $employeeData['hire_date'],
                    'status' => $employeeData['status'],
                ]
            );

            foreach ($employeeData['reports'] as $reportData) {
                EmployeeMonthlyReport::query()->updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'month' => $reportData['month'],
                        'year' => $reportData['year'],
                    ],
                    [
                        'required_working_days' => $reportData['required_days'],
                        'required_working_hours' => $reportData['required_hours'],
                        'actual_worked_days' => $reportData['actual_days'],
                        'actual_worked_hours' => $reportData['worked_hours'],
                        'overtime_hours' => $reportData['overtime_hours'],
                        'notes' => 'Dummy monthly work report for payroll preview.',
                    ]
                );

                foreach ($employeeData['adjustments'] as $adjustmentData) {
                    if ($adjustmentData['month'] !== $reportData['month'] || $adjustmentData['year'] !== $reportData['year']) {
                        continue;
                    }

                    EmployeeAdjustment::query()->updateOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'month' => $adjustmentData['month'],
                            'year' => $adjustmentData['year'],
                            'type' => $adjustmentData['type'],
                            'reason' => $adjustmentData['reason'],
                        ],
                        [
                            'amount' => $adjustmentData['amount'],
                            'notes' => 'Dummy payroll adjustment.',
                        ]
                    );
                }

                $this->savePayroll($employee, $reportData);
            }
        }
    }

    /**
     * Save a payroll snapshot using the same formula planned for production.
     *
     * @param  array<string, mixed>  $reportData
     */
    private function savePayroll(Employee $employee, array $reportData): void
    {
        $requiredHours = (float) $reportData['required_hours'];
        $workedHours = (float) $reportData['worked_hours'];
        $overtimeHours = (float) $reportData['overtime_hours'];
        $basicSalary = (float) $employee->basic_salary;
        $hourlyRate = $requiredHours > 0 ? $basicSalary / $requiredHours : 0;
        $absentHours = max($requiredHours - $workedHours, 0);

        $totalBonus = (float) EmployeeAdjustment::query()
            ->where('employee_id', $employee->id)
            ->where('month', $reportData['month'])
            ->where('year', $reportData['year'])
            ->where('type', 'bonus')
            ->sum('amount');

        $totalDeductions = (float) EmployeeAdjustment::query()
            ->where('employee_id', $employee->id)
            ->where('month', $reportData['month'])
            ->where('year', $reportData['year'])
            ->where('type', 'deduction')
            ->sum('amount');

        $absenceDeduction = $absentHours * $hourlyRate;
        $overtimeAmount = $overtimeHours * $hourlyRate;
        $netSalary = $basicSalary - $absenceDeduction + $overtimeAmount + $totalBonus - $totalDeductions;

        Payroll::query()->updateOrCreate(
            [
                'employee_id' => $employee->id,
                'month' => $reportData['month'],
                'year' => $reportData['year'],
            ],
            [
                'basic_salary' => round($basicSalary, 2),
                'hour_salary' => round($hourlyRate, 2),
                'absence_deduction' => round($absenceDeduction, 2),
                'overtime_amount' => round($overtimeAmount, 2),
                'total_bonus' => round($totalBonus, 2),
                'total_deductions' => round($totalDeductions, 2),
                'net_salary' => round($netSalary, 2),
                'status' => $reportData['status'],
            ]
        );
    }
}
