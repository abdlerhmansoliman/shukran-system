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
        'paid_amount',
        'remaining_amount',
        'payment_date',
        'payment_status',
        'start_date',
        'end_date',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount' => 'decimal:2',
            'final_price' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'payment_date' => 'date',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

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
