<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $fillable = [
        'name',
        'status',
    ];

    public function packages()
    {
        return $this->hasMany(Package::class);
    }
}
