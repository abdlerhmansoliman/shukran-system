<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discount extends Model
{
    protected $fillable = [
        'name',
        'type',
        'amount',
        'status',
    ];

    public function customerPackages(): HasMany
    {
        return $this->hasMany(CustomerPackage::class);
    }
}
