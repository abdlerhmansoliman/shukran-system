<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'name',
        'program_id',
        'category_id',
        'levels_count',
        'sessions_count',
        'level_price',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'levels_count' => 'integer',
            'sessions_count' => 'integer',
            'level_price' => 'decimal:2',
        ];
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function customerPackages()
    {
        return $this->hasMany(CustomerPackage::class);
    }
}
