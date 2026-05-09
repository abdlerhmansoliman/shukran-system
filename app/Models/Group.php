<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = [
        'name',
        'level_id',
        'category_id',
        'instructor_id',
        'capacity',
        'start_date',
        'end_date',
        'days_of_week',
        'start_time',
        'end_time',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'days_of_week' => 'array',
        ];
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function groupEnrollments()
    {
        return $this->hasMany(GroupEnrollment::class);
    }

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'group_enrollments')
            ->withPivot(['customer_package_id', 'status', 'joined_at', 'left_at'])
            ->withTimestamps();
    }
}
