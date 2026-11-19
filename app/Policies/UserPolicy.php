<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $authUser): bool
    {
        return $authUser->level === 'creator' || $authUser->hasPermission('users.index') || $authUser->hasPermission('users.view');
    }

    public function update(User $authUser, User $targetUser): bool
    {
        if ($authUser->level !== 'creator' && $targetUser->company_id !== $authUser->company_id) {
            return false;
        }

        return $authUser->level === 'creator' || $authUser->hasPermission('users.edit');
    }

    public function delete(User $authUser, User $targetUser): bool
    {
        if ($authUser->level !== 'creator' && $targetUser->company_id !== $authUser->company_id) {
            return false;
        }

        if ($targetUser->id === $authUser->id) {
            return false;
        }

        return $authUser->level === 'creator' || $authUser->hasPermission('users.delete');
    }
}