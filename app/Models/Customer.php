<?php

namespace App\Models;

use App\Enums\CustomerStatus;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory, SoftDeletes;

    protected $attributes = [
        'status' => 'new',
    ];

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'second_phone_number',
        'source',
        'notes',
        'created_by',
        'address',
        'country_id',
        'wallet_balance',
        'customer_type',
        'status',
        'status_changed_at',
    ];

    protected function casts(): array
    {
        return [
            'wallet_balance' => 'decimal:2',
            'status' => CustomerStatus::class,
            'status_changed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Customer $customer) {
            if ($customer->isDirty('status')) {
                $customer->status_changed_at = now();
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function profiles()
    {
        return $this->hasMany(Profile::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(CustomerFeedback::class)->latest();
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
}
