<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardService
{
    /**
     * محاسبه آمارهای داشبورد مدیریت با احتساب سطح دسترسی و شرکت کاربر
     */
    public function getDashboardStats(User $authUser): array
    {
        $userQuery = User::query();
        $ticketQuery = Ticket::query();

        // 🛡️ اگر ادمین کل (creator) نباشد، آمارها فقط مربوط به شرکت خودش است
        if (!$authUser->isCreator()) {
            $userQuery->where('company_id', $authUser->company_id);
            $ticketQuery->where(function ($q) use ($authUser) {
                $q->where('company_id', $authUser->company_id)
                  ->orWhere('target_company_id', $authUser->company_id);
            });
        }

        $stats = [
            'total_users'  => (clone $userQuery)->count(),
            'admins_count' => (clone $userQuery)->where(function ($q) {
                $q->where('level', 'creator')->orWhereHas('roles');
            })->count(),
            'roles_count'  => Role::count(),
        ];

        $today = Carbon::today();

        $ticketStats = [
            'open_today'         => (clone $ticketQuery)->whereDate('created_at', $today)->where('status', 'open')->count(),
            'in_progress_today'  => (clone $ticketQuery)->whereDate('created_at', $today)->where('status', 'in_progress')->count(),
            'resolved_today'     => (clone $ticketQuery)->whereDate('created_at', $today)->where('status', 'resolved')->count(),
            'closed_today'       => (clone $ticketQuery)->whereDate('created_at', $today)->where('status', 'closed')->count(),
            'overdue_count'      => (clone $ticketQuery)->where('status', '!=', 'closed')->where('sla_due_at', '<', now())->count(),
            'by_status'          => (clone $ticketQuery)->selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status'),
            'by_priority'        => (clone $ticketQuery)->selectRaw('priority, count(*) as count')->groupBy('priority')->pluck('count', 'priority'),
            'sla_met_percentage' => $this->calculateSlaPercentage($ticketQuery),
        ];

        return [
            'stats'       => $stats,
            'ticketStats' => $ticketStats,
        ];
    }

    /**
     * محاسبه آمارهای مخصوص بخش گزارشات
     */
    public function getReportStats(User $authUser): array
    {
        $ticketQuery = Ticket::query();

        if (!$authUser->isCreator()) {
            $ticketQuery->where(function ($q) use ($authUser) {
                $q->where('company_id', $authUser->company_id)
                  ->orWhere('target_company_id', $authUser->company_id);
            });
        }

        $today = Carbon::today();

        return [
            'open_today'         => (clone $ticketQuery)->whereDate('created_at', $today)->where('status', 'open')->count(),
            'in_progress_today'  => (clone $ticketQuery)->whereDate('created_at', $today)->where('status', 'in_progress')->count(),
            'resolved_today'     => (clone $ticketQuery)->whereDate('created_at', $today)->where('status', 'resolved')->count(),
            'closed_today'       => (clone $ticketQuery)->whereDate('created_at', $today)->where('status', 'closed')->count(),
            'overdue_count'      => (clone $ticketQuery)->where('status', '!=', 'closed')->where('sla_due_at', '<', now())->count(),
            'by_status'          => (clone $ticketQuery)->selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status'),
            'by_priority'        => (clone $ticketQuery)->selectRaw('priority, count(*) as count')->groupBy('priority')->pluck('count', 'priority'),
            'sla_met_percentage' => $this->calculateSlaPercentage($ticketQuery),
        ];
    }

    
    protected function calculateSlaPercentage($ticketQuery): int
    {
        $totalResolvedOrClosed = (clone $ticketQuery)->whereIn('status', ['resolved', 'closed'])->count();
        if ($totalResolvedOrClosed === 0) {
            return 100;
        }

        $metSla = (clone $ticketQuery)->whereIn('status', ['resolved', 'closed'])
            ->whereColumn('updated_at', '<=', 'sla_due_at')
            ->count();

        return (int) round(($metSla / $totalResolvedOrClosed) * 100);
    }
}