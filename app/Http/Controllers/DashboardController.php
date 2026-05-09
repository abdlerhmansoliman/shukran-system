<?php

namespace App\Http\Controllers;

use App\Enums\GroupEnrollmentStatus;
use App\Enums\GroupStatus;
use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\Employee;
use App\Models\Group;
use App\Models\GroupEnrollment;
use App\Models\Payment;
use App\Models\Payroll;
use Illuminate\Database\Eloquent\Builder;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $activeCustomerQuery = fn (Builder $query) => $query->whereHas(
            'customerPackages',
            fn (Builder $builder) => $builder->where('status', 'active')
        );

        $incomingThisMonth = Payment::query()
            ->where('direction', 'incoming')
            ->where('status', 'completed')
            ->whereBetween('paid_at', [$monthStart, $monthEnd])
            ->sum('amount');

        $outgoingThisMonth = Payment::query()
            ->where('direction', 'outgoing')
            ->where('status', 'completed')
            ->whereBetween('paid_at', [$monthStart, $monthEnd])
            ->sum('amount');

        return view('dashboard', [
            'stats' => [
                'total_customers' => Customer::query()->count(),
                'active_customers' => Customer::query()->where($activeCustomerQuery)->count(),
                'active_subscriptions' => CustomerPackage::query()->where('status', 'active')->count(),
                'subscription_balance' => CustomerPackage::query()
                    ->where('status', 'active')
                    ->where('remaining_amount', '>', 0)
                    ->sum('remaining_amount'),
                'incoming_this_month' => $incomingThisMonth,
                'outgoing_this_month' => $outgoingThisMonth,
                'net_cash_this_month' => $incomingThisMonth - $outgoingThisMonth,
                'active_groups' => Group::query()->where('status', GroupStatus::Active->value)->count(),
                'planned_groups' => Group::query()->where('status', GroupStatus::Planned->value)->count(),
                'active_enrollments' => GroupEnrollment::query()->where('status', GroupEnrollmentStatus::Active->value)->count(),
                'active_employees' => Employee::query()->where('status', 'active')->count(),
                'draft_payroll_total' => Payroll::query()
                    ->where('month', now()->month)
                    ->where('year', now()->year)
                    ->where('status', 'draft')
                    ->sum('net_salary'),
                'draft_payroll_count' => Payroll::query()
                    ->where('month', now()->month)
                    ->where('year', now()->year)
                    ->where('status', 'draft')
                    ->count(),
            ],
            'recentCustomers' => Customer::query()
                ->with(['category.parent', 'customerPackages' => fn ($query) => $query->where('status', 'active')])
                ->latest()
                ->limit(5)
                ->get(),
            'unpaidSubscriptions' => CustomerPackage::query()
                ->with(['customer', 'package'])
                ->where('status', 'active')
                ->where('remaining_amount', '>', 0)
                ->orderByDesc('remaining_amount')
                ->limit(5)
                ->get(),
            'currentGroups' => Group::query()
                ->with(['category.parent', 'instructor'])
                ->withCount([
                    'groupEnrollments as active_enrollments_count' => fn (Builder $query) => $query->whereIn('status', GroupEnrollmentStatus::reservedValues()),
                ])
                ->whereIn('status', [GroupStatus::Planned->value, GroupStatus::Active->value])
                ->latest()
                ->limit(5)
                ->get(),
            'draftPayrolls' => Payroll::query()
                ->with('employee.user')
                ->where('month', now()->month)
                ->where('year', now()->year)
                ->where('status', 'draft')
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}
