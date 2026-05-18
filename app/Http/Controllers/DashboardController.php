<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    public function __invoke()
    {
        return view('dashboard', [
            'stats' => $this->dashboardService->getStats(),
            'recentCustomers' => $this->dashboardService->getRecentCustomers(),
            'unpaidSubscriptions' => $this->dashboardService->getUnpaidSubscriptions(),
            'currentGroups' => $this->dashboardService->getCurrentGroups(),
            'draftPayrolls' => $this->dashboardService->getDraftPayrolls(),
        ]);
    }
}
