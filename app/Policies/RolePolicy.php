<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    protected function canManageInCompany(User $authUser, Role $role, string $permission): bool
    {
        if ($authUser->isCreator()) {
            return true;
        }

        if (!$authUser->hasPermission($permission)) {
            return false;
        }

        return (int) $role->company_id === (int) $authUser->company_id;
    }

    public function viewAny(User $authUser): bool
    {
        if ($authUser->isCreator()) {
            return true;
        }

        return $authUser->hasPermission('roles.index') || $authUser->hasPermission('roles.view');
    }

    public function view(User $authUser, Role $role): bool
    {
        return $this->canManageInCompany($authUser, $role, 'roles.view')
            || $this->canManageInCompany($authUser, $role, 'roles.index');
    }

    public function create(User $authUser): bool
    {
        if ($authUser->isCreator()) {
            return true;
        }

        return $authUser->hasPermission('roles.create');
    }

    public function update(User $authUser, Role $role): bool
    {
        return $this->canManageInCompany($authUser, $role, 'roles.edit');
    }

    public function delete(User $authUser, Role $role): bool
    {
        return $this->canManageInCompany($authUser, $role, 'roles.destroy');
    }
}