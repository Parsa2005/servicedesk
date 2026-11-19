<?php

namespace App\Policies;

use App\Models\User;

class MembershipPolicy
{
    public function viewAny(User $authUser): bool
    {
        return $authUser->level === 'creator' || $authUser->hasPermission('users.view') || $authUser->hasPermission('users.index');
    }

    public function update(User $authUser, User $targetUser): bool
    {
        if ($authUser->level !== 'creator' && $targetUser->company_id !== $authUser->company_id) {
            return false;
        }

        return $authUser->level === 'creator' || $authUser->hasPermission('users.edit');
    }
}