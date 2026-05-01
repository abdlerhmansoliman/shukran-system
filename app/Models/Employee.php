<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'department_id',
        'age',
        'phone',
        'job_title',
        'basic_salary',
        'salary_type',
        'hire_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'age' => 'integer',
            'basic_salary' => 'decimal:2',
            'hire_date' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function monthlyReports()
    {
        return $this->hasMany(EmployeeMonthlyReport::class);
    }

    public function adjustments()
    {
        return $this->hasMany(EmployeeAdjustment::class);
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }
}
