<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;

class DepartmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isCreator() || $user->hasPermission('departments.index') || $user->hasPermission('departments.view');
    }

    public function create(User $user): bool
    {
        return $user->isCreator() || $user->hasPermission('departments.create');
    }

    public function update(User $user, Department $department): bool
    {
        if (!$user->isCreator() && $department->company_id !== $user->company_id) {
            return false;
        }

        return $user->isCreator() || $user->hasPermission('departments.edit');
    }

    public function delete(User $user, Department $department): bool
    {
        if (!$user->isCreator() && $department->company_id !== $user->company_id) {
            return false;
        }

        return $user->isCreator() || $user->hasPermission('departments.delete');
    }
}