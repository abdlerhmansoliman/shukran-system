<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'status',
        'source',
        'notes',
        'level_id',
        'category_id',
        'created_by',
        'age',
        'gender',
        'address',
        'country_id',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function customerPackages()
    {
        return $this->hasMany(CustomerPackage::class);
    }
}
