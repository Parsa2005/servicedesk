<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = auth()->user();
        $allowedCompanyIds = $this->resolveAllowedCompanyIds($user);

        $query = $user->notifications()->latest();

        if ($request->get('filter') === 'unread') {
            $query->whereNull('read_at');
        }

        $filtered = $query->get()
            ->filter(fn (DatabaseNotification $notification) => $this->isNotificationAllowed($notification, $allowedCompanyIds))
            ->values();

        $unreadCount = $filtered->whereNull('read_at')->count();

        $perPage = 15;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $items = $filtered->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $notifications = new LengthAwarePaginator(
            $items,
            $filtered->count(),
            $perPage,
            $currentPage,
            [
                'path'  => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('user.notifications.index', [
            'notifications' => $notifications,
            'unreadCount'   => $unreadCount,
            'filter'        => $request->get('filter', 'all'),
        ]);
    }

    public function readAll(): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $allowedCompanyIds = $this->resolveAllowedCompanyIds($user);

        foreach ($user->unreadNotifications()->get() as $notification) {
            if ($this->isNotificationAllowed($notification, $allowedCompanyIds)) {
                $notification->markAsRead();
            }
        }

        return back()->with('success', 'تمام اعلان‌های مجاز به عنوان خوانده‌شده علامت‌گذاری شدند.');
    }

    /**
     * @return int[]|null null یعنی دسترسی به همه شرکت‌ها (creator)
     */
    protected function resolveAllowedCompanyIds(User $user): ?array
    {
        $isCreator = $user->level === 'creator'
            || (method_exists($user, 'isCreator') && $user->isCreator());

        if ($isCreator) {
            return null;
        }

        $isManager = $user->roles()->where('slug', 'company-manager')->exists();

        if ($isManager && $user->company) {
            return array_map('intval', $user->company->getDescendantCompanyIds());
        }

        return $user->company_id ? [(int) $user->company_id] : [];
    }

    /**
     * @param int[]|null $allowedCompanyIds
     */
    protected function isNotificationAllowed(DatabaseNotification $notification, ?array $allowedCompanyIds): bool
    {
        if ($allowedCompanyIds === null) {
            return true;
        }

        $companyId = $notification->data['company_id'] ?? null;

        if ($companyId === null) {
            return true;
        }

        return in_array((int) $companyId, $allowedCompanyIds, true);
    }
}