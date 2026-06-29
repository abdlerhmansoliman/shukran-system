<?php

namespace App\Models;

use App\Enums\CustomerKeyword;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'customer_id',
        'first_name',
        'last_name',
        'age',
        'gender',
        'entry_level_id',
        'current_level_id',
        'category_id',
        'tester_id',
        'placement_month',
        'job',
        'college',
        'progress_report_link',
        'test_date',
        'agreed_package_id',
        'agreed_amount',
        'keywords',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'age' => 'integer',
            'placement_month' => 'date',
            'test_date' => 'date',
            'agreed_amount' => 'decimal:2',
            'keywords' => CustomerKeyword::class,
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function entryLevel()
    {
        return $this->belongsTo(Level::class, 'entry_level_id');
    }

    public function currentLevel()
    {
        return $this->belongsTo(Level::class, 'current_level_id');
    }

    public function level()
    {
        return $this->belongsTo(Level::class, 'current_level_id')->withDefault(function ($level, $profile) {
            return $profile->entryLevel;
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tester()
    {
        return $this->belongsTo(User::class, 'tester_id');
    }

    public function agreedPackage()
    {
        return $this->belongsTo(Package::class, 'agreed_package_id');
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

    public function groupEnrollments()
    {
        return $this->hasMany(GroupEnrollment::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(CustomerFeedback::class)->latest();
    }
}
