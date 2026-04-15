<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerPackage extends Model
{
    protected $fillable = [
        'customer_id',
        'package_id',
        'price',
        'discount',
        'final_price',
        'start_date',
        'end_date',
        'status',
        'created_by',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
