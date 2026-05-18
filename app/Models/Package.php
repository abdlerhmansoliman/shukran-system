<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'name',
        'levels_count',
        'price',
        'level_price',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'levels_count' => 'integer',
            'price' => 'decimal:2',
            'level_price' => 'decimal:2',
        ];
    }

    public function customerPackages()
    {
        return $this->hasMany(CustomerPackage::class);
    }
}
