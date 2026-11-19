<?php

namespace App\Policies;

use App\Models\User;

class DashboardPolicy
{
    public function view(User $user): bool
    {
        return $user->isCreator() || $user->hasPermission('dashboard.view') || true;
    }

    public function reports(User $user): bool
    {
        return $user->isCreator() || $user->hasPermission('reports.view') || true;
    }
}