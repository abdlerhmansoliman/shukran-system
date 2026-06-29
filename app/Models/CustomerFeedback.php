<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerFeedback extends Model
{
    protected $table = 'customer_feedbacks';

    protected $fillable = [
        'customer_id',
        'profile_id',
        'level_id',
        'feedback',
        'created_by',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
