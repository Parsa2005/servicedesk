<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardService
{
    /**
     * برگرداندن آرایه شناسه‌های شرکت مجاز:
     * null = همه شرکت‌ها (فقط creator)
     * array = خود شرکت + زیرمجموعه‌ها
     */
    protected function allowedCompanyIds(User $authUser): ?array
    {
        if ($authUser->isCreator()) {
            return null;
        }

        if ($authUser->company) {
            return $authUser->company->getDescendantCompanyIds();
        }

        return $authUser->company_id ? [(int) $authUser->company_id] : [];
    }

    /**
     * محاسبه آمارهای داشبورد مدیریت با احتساب سطح دسترسی و قلمرو شرکت‌ها
     */
    public function getDashboardStats(User $authUser): array
    {
        $allowedIds = $this->allowedCompanyIds($authUser);

        $userQuery = User::query();
        $ticketQuery = Ticket::query();
        $roleQuery = Role::query();

        if ($allowedIds !== null) {
            $userQuery->whereIn('company_id', $allowedIds);

            $ticketQuery->where(function ($q) use ($allowedIds) {
                $q->whereIn('company_id', $allowedIds)
                  ->orWhereIn('target_company_id', $allowedIds);
            });

            $roleQuery->where(function ($q) use ($allowedIds) {
                $q->whereIn('company_id', $allowedIds)
                  ->orWhereNull('company_id');
            });
        }

        $stats = [
            'total_users'  => (clone $userQuery)->count(),
            'admins_count' => (clone $userQuery)->where(function ($q) {
                $q->where('level', 'creator')->orWhereHas('roles');
            })->count(),
            'roles_count'  => (clone $roleQuery)->count(),
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
        $allowedIds = $this->allowedCompanyIds($authUser);
        $ticketQuery = Ticket::query();

        if ($allowedIds !== null) {
            $ticketQuery->where(function ($q) use ($allowedIds) {
                $q->whereIn('company_id', $allowedIds)
                  ->orWhereIn('target_company_id', $allowedIds);
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