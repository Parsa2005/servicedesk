<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DashboardController extends Controller
{
    use AuthorizesRequests;

    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $this->authorize('view', User::class);

        // دریافت تمام آمارها از سرویس
        $dashboardData = $this->dashboardService->getDashboardStats(auth()->user());

        $stats = $dashboardData['stats'];
        $ticketStats = $dashboardData['ticketStats'];

        return view('panel.menu', compact('stats', 'ticketStats'));
    }

    public function reports()
    {
        $this->authorize('reports', User::class);

        $ticketStats = $this->dashboardService->getReportStats(auth()->user());

        return view('panel.reports.index', compact('ticketStats'));
    }
}