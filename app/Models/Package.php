<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'name',
        'levels_count',
        'price',
        'status',
    ];

    public function customerPackages()
    {
        return $this->hasMany(CustomerPackage::class);
    }
}
