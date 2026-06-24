<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\Group;
use App\Models\Payroll;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function getStats(): array
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $totalCustomers = Customer::query()->count();
        $activeCustomers = Customer::query()->whereHas('customerPackages', function ($query) {
            $query->where('status', 'active');
        })->count();

        $activeSubscriptions = CustomerPackage::query()->where('status', 'active')->count();
        $subscriptionBalance = CustomerPackage::query()->where('status', 'active')->sum('remaining_amount');

        $activeGroups = Group::query()->where('status', 'active')->count();
        $plannedGroups = Group::query()->where('status', 'planned')->count();
        $activeEnrollments = DB::table('group_enrollments')->where('status', 'active')->count();

        $draftPayrollsQuery = Payroll::query()
            ->where('status', 'draft')
            ->where('month', $currentMonth)
            ->where('year', $currentYear);

        $draftPayrollCount = (clone $draftPayrollsQuery)->count();
        $draftPayrollTotal = (clone $draftPayrollsQuery)->sum('net_salary');

        $incomingThisMonth = Payment::query()
            ->where('direction', 'incoming')
            ->where('status', 'completed')
            ->whereMonth('paid_at', $currentMonth)
            ->whereYear('paid_at', $currentYear)
            ->sum('amount');

        $outgoingThisMonth = Payment::query()
            ->where('direction', 'outgoing')
            ->where('status', 'completed')
            ->whereMonth('paid_at', $currentMonth)
            ->whereYear('paid_at', $currentYear)
            ->sum('amount');

        return [
            'total_customers' => $totalCustomers,
            'active_customers' => $activeCustomers,
            'active_subscriptions' => $activeSubscriptions,
            'subscription_balance' => $subscriptionBalance,
            'active_groups' => $activeGroups,
            'planned_groups' => $plannedGroups,
            'active_enrollments' => $activeEnrollments,
            'draft_payroll_total' => $draftPayrollTotal,
            'draft_payroll_count' => $draftPayrollCount,
            'incoming_this_month' => $incomingThisMonth,
            'outgoing_this_month' => $outgoingThisMonth,
            'net_cash_this_month' => $incomingThisMonth - $outgoingThisMonth,
        ];
    }

    /**
     * @return Collection<int, Customer>
     */
    public function getRecentCustomers(): Collection
    {
        return Customer::query()
            ->with(['category', 'category.parent'])
            ->latest()
            ->limit(5)
            ->get();
    }

    /**
     * @return Collection<int, CustomerPackage>
     */
    public function getUnpaidSubscriptions(): Collection
    {
        return CustomerPackage::query()
            ->with(['customer', 'package'])
            ->where('status', 'active')
            ->where('remaining_amount', '>', 0)
            ->orderByDesc('remaining_amount')
            ->limit(5)
            ->get();
    }

    /**
     * @return Collection<int, Group>
     */
    public function getCurrentGroups(): Collection
    {
        return Group::query()
            ->withCount('activeEnrollments')
            ->whereIn('status', ['active', 'planned'])
            ->latest()
            ->limit(5)
            ->get();
    }

    /**
     * @return Collection<int, Payroll>
     */
    public function getDraftPayrolls(): Collection
    {
        return Payroll::query()
            ->with('employee')
            ->where('status', 'draft')
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->latest()
            ->limit(5)
            ->get();
    }
}
