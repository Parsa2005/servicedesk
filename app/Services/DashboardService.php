<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardService
{
    protected function isCompanyManager(User $authUser): bool
    {
        return $authUser->roles()->where('slug', 'company-manager')->exists();
    }

    /**
     * @return int[]|null null یعنی دسترسی کامل به همه شرکت‌ها (Creator)
     */
    protected function allowedCompanyIds(User $authUser): ?array
    {
        if ($authUser->level === 'creator' || (method_exists($authUser, 'isCreator') && $authUser->isCreator())) {
            return null;
        }

        if ($this->isCompanyManager($authUser) && $authUser->company) {
            return array_map('intval', $authUser->company->getDescendantCompanyIds());
        }

        return $authUser->company_id ? [(int) $authUser->company_id] : [];
    }

    protected function applyTicketCompanyScope($query, ?array $allowedIds)
    {
        if ($allowedIds === null) {
            return $query;
        }

        if (empty($allowedIds)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($q) use ($allowedIds) {
            $q->whereIn('company_id', $allowedIds)
              ->orWhereIn('target_company_id', $allowedIds);
        });
    }

    public function getDashboardStats(User $authUser): array
    {
        $allowedIds = $this->allowedCompanyIds($authUser);

        $userQuery = User::query();
        $ticketQuery = $this->applyTicketCompanyScope(Ticket::query(), $allowedIds);
        $roleQuery = Role::query();

        if ($allowedIds !== null) {
            if (empty($allowedIds)) {
                $userQuery->whereRaw('1 = 0');
                $roleQuery->whereRaw('1 = 0');
            } else {
                $userQuery->whereIn('company_id', $allowedIds);

                $roleQuery->where(function ($q) use ($allowedIds) {
                    $q->whereIn('company_id', $allowedIds)
                      ->orWhereNull('company_id');
                });
            }
        }

        $stats = [
            'total_users'  => (clone $userQuery)->count(),
            'admins_count' => (clone $userQuery)->where(function ($q) {
                $q->where('level', 'creator')->orWhereHas('roles');
            })->count(),
            'roles_count'  => (clone $roleQuery)->count(),
        ];

        return [
            'stats'       => $stats,
            'ticketStats' => $this->buildTicketStats($ticketQuery),
        ];
    }

    public function getReportStats(User $authUser, ?Request $request = null): array
    {
        $request = $request ?? request();
        $allowedIds = $this->allowedCompanyIds($authUser);

        $ticketQuery = $this->applyTicketCompanyScope(Ticket::query(), $allowedIds);

        // فیلتر اختیاری بر اساس یک شرکت خاص در محدوده مجاز کاربر
        if ($request->filled('company_id')) {
            $requestedCompanyId = (int) $request->input('company_id');
            if ($allowedIds === null || in_array($requestedCompanyId, $allowedIds, true)) {
                $ticketQuery->where(function ($q) use ($requestedCompanyId) {
                    $q->where('company_id', $requestedCompanyId)
                      ->orWhere('target_company_id', $requestedCompanyId);
                });
            }
        }

        // فیلتر اختیاری وضعیت
        if ($request->filled('status')) {
            $ticketQuery->where('status', $request->input('status'));
        }

        $ticketStats = $this->buildTicketStats($ticketQuery);

        $reportTickets = (clone $ticketQuery)
            ->with(['user', 'assigned', 'company', 'department', 'category'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $companiesQuery = Company::query()->orderBy('name');
        if ($allowedIds !== null) {
            if (empty($allowedIds)) {
                $companiesQuery->whereRaw('1 = 0');
            } else {
                $companiesQuery->whereIn('id', $allowedIds);
            }
        }

        return [
            'ticketStats'   => $ticketStats,
            'reportTickets' => $reportTickets,
            'companies'     => $companiesQuery->get(),
            'filters'       => [
                'company_id' => $request->input('company_id'),
                'status'     => $request->input('status'),
            ],
        ];
    }

    protected function buildTicketStats($ticketQuery): array
    {
        $today = Carbon::today();

        return [
            'open_today'         => (clone $ticketQuery)->whereDate('created_at', $today)->where('status', 'open')->count(),
            'in_progress_today'  => (clone $ticketQuery)->whereDate('created_at', $today)->where('status', 'in_progress')->count(),
            'resolved_today'     => (clone $ticketQuery)->whereDate('created_at', $today)->where('status', 'resolved')->count(),
            'closed_today'       => (clone $ticketQuery)->whereDate('created_at', $today)->where('status', 'closed')->count(),
            'overdue_count'      => (clone $ticketQuery)->whereNotIn('status', ['resolved', 'closed'])
                ->whereNotNull('sla_due_at')
                ->where('sla_due_at', '<', now())
                ->count(),
            'by_status'          => (clone $ticketQuery)->selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status')->toArray(),
            'by_priority'        => (clone $ticketQuery)->selectRaw('priority, count(*) as count')->groupBy('priority')->pluck('count', 'priority')->toArray(),
            'sla_met_percentage' => $this->calculateSlaPercentage($ticketQuery),
        ];
    }

    protected function calculateSlaPercentage($ticketQuery): int
    {
        $totalWithSla = (clone $ticketQuery)
            ->whereIn('status', ['resolved', 'closed'])
            ->whereNotNull('sla_due_at')
            ->count();

        if ($totalWithSla === 0) {
            return 0;
        }

        $metSla = (clone $ticketQuery)
            ->whereIn('status', ['resolved', 'closed'])
            ->whereNotNull('sla_due_at')
            ->whereColumn('updated_at', '<=', 'sla_due_at')
            ->count();

        return (int) round(($metSla / $totalWithSla) * 100);
    }
}