<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    protected function canAccessTargetUserCompany(User $authUser, User $targetUser): bool
    {
        if ($authUser->level === 'creator') {
            return true;
        }

        $isManager = $authUser->roles()->where('slug', 'company-manager')->exists();
        if ($isManager && $authUser->company) {
            return in_array((int) $targetUser->company_id, $authUser->company->getDescendantCompanyIds(), true);
        }

        return (int) $targetUser->company_id === (int) $authUser->company_id;
    }

    public function viewAny(User $authUser): bool
    {
        return $authUser->level === 'creator'
            || $authUser->roles()->where('slug', 'company-manager')->exists()
            || $authUser->hasPermission('users.index')
            || $authUser->hasPermission('users.view');
    }

    public function update(User $authUser, User $targetUser): bool
    {
        if (!$this->canAccessTargetUserCompany($authUser, $targetUser)) {
            return false;
        }

        return $authUser->level === 'creator'
            || $authUser->roles()->where('slug', 'company-manager')->exists()
            || $authUser->hasPermission('users.edit');
    }

    public function delete(User $authUser, User $targetUser): bool
    {
        if ($targetUser->id === $authUser->id) {
            return false;
        }

        if (!$this->canAccessTargetUserCompany($authUser, $targetUser)) {
            return false;
        }

        return $authUser->level === 'creator'
            || $authUser->roles()->where('slug', 'company-manager')->exists()
            || $authUser->hasPermission('users.delete');
    }
}