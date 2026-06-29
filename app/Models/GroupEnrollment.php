<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupEnrollment extends Model
{
    protected $fillable = [
        'group_id',
        'customer_id',
        'profile_id',
        'customer_package_id',
        'status',
        'joined_at',
        'left_at',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'date',
            'left_at' => 'date',
        ];
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function customerPackage()
    {
        return $this->belongsTo(CustomerPackage::class);
    }
}
