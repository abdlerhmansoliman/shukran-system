<?php

namespace App\Console\Commands;

use App\Enums\CustomerStatus;
use App\Models\Customer;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class AutoTransitionCustomerStatus extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'customers:auto-transition';

    /**
     * The console command description.
     */
    protected $description = 'Automatically transition customer statuses based on inactivity rules';

    /**
     * Number of days before automatic transition to inactive.
     */
    private const int INACTIVITY_DAYS = 60;

    public function handle(): int
    {
        $cutoff = Carbon::now()->subDays(self::INACTIVITY_DAYS);

        // Rule 1: finished → inactive after 60 days with no action
        $finishedCount = Customer::query()
            ->where('status', CustomerStatus::Finished)
            ->where('status_changed_at', '<=', $cutoff)
            ->update([
                'status' => CustomerStatus::Inactive,
                'status_changed_at' => now(),
            ]);

        // Rule 2: General inactivity — any non-protected status with no updates for 60 days
        $inactiveCount = Customer::query()
            ->whereNotIn('status', [
                CustomerStatus::Active->value,
                CustomerStatus::Inactive->value,
                CustomerStatus::Paused->value,
                CustomerStatus::Dropped->value,
            ])
            ->where('updated_at', '<=', $cutoff)
            ->where(function ($query) use ($cutoff) {
                $query->where('status_changed_at', '<=', $cutoff)
                    ->orWhereNull('status_changed_at');
            })
            ->update([
                'status' => CustomerStatus::Inactive,
                'status_changed_at' => now(),
            ]);

        $total = $finishedCount + $inactiveCount;

        if ($total > 0) {
            $this->info("Transitioned {$total} customers to inactive ({$finishedCount} finished, {$inactiveCount} general inactivity).");
        } else {
            $this->info('No customers required status transition.');
        }

        return self::SUCCESS;
    }
}
