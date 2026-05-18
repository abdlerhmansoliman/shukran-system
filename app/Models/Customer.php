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
        'second_phone_number',
        'source',
        'notes',
        'level_id',
        'category_id',
        'created_by',
        'age',
        'gender',
        'address',
        'country_id',
        'wallet_balance',
        'customer_type',
        'tester_id',
        'placement_month',
        'old_instructor_id',
    ];

    protected function casts(): array
    {
        return [
            'age' => 'integer',
            'wallet_balance' => 'decimal:2',
            'placement_month' => 'date',
        ];
    }

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

    public function subscriptions()
    {
        return $this->customerPackages();
    }

    public function getStatusAttribute(): string
    {
        return $this->hasActiveSubscription() ? 'active' : 'inactive';
    }

    public function setStatusAttribute(mixed $value): void
    {
        // Customer status is virtual and comes from active subscriptions.
    }

    public function hasActiveSubscription(): bool
    {
        if (array_key_exists('active_subscriptions_count', $this->attributes)) {
            return (int) $this->attributes['active_subscriptions_count'] > 0;
        }

        if ($this->relationLoaded('customerPackages')) {
            return $this->customerPackages->contains('status', 'active');
        }

        return $this->customerPackages()
            ->where('status', 'active')
            ->exists();
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function groupEnrollments()
    {
        return $this->hasMany(GroupEnrollment::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_enrollments')
            ->withPivot(['customer_package_id', 'status', 'joined_at', 'left_at'])
            ->withTimestamps();
    }

    public function tester()
    {
        return $this->belongsTo(User::class, 'tester_id');
    }

    public function oldInstructor()
    {
        return $this->belongsTo(User::class, 'old_instructor_id');
    }
}
