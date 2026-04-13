<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'status',
        'source',
        'notes',
        'created_by',
        'updated_by'
    ];

    public function creator(){
        return $this->belongsTo(User::class, 'created_by');
    }
}
