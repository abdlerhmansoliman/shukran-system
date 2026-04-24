<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeMonthlyReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'month',
        'year',
        'required_working_days',
        'required_working_hours',
        'actual_worked_days',
        'actual_worked_hours',
        'overtime_hours',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'month' => 'integer',
            'year' => 'integer',
            'required_working_days' => 'decimal:2',
            'required_working_hours' => 'decimal:2',
            'actual_worked_days' => 'decimal:2',
            'actual_worked_hours' => 'decimal:2',
            'overtime_hours' => 'decimal:2',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
