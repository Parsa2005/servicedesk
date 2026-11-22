<?php

namespace App\Policies;

use App\Models\User;

class MembershipPolicy
{
    public function viewAny(User $authUser): bool
    {
        return $authUser->level === 'creator'
            || $authUser->roles()->where('slug', 'company-manager')->exists()
            || $authUser->hasPermission('users.view')
            || $authUser->hasPermission('users.index');
    }

    public function update(User $authUser, User $targetUser): bool
    {
        if ($authUser->level === 'creator') {
            return true;
        }

        $isManager = $authUser->roles()->where('slug', 'company-manager')->exists();
        if ($isManager && $authUser->company) {
            return in_array((int) $targetUser->company_id, $authUser->company->getDescendantCompanyIds(), true);
        }

        if ($targetUser->company_id !== $authUser->company_id) {
            return false;
        }

        return $authUser->hasPermission('users.edit');
    }
}