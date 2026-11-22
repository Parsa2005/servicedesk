<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;

class DepartmentPolicy
{
    protected function canAccessCompany(User $user, ?int $companyId): bool
    {
        if ($user->isCreator()) {
            return true;
        }

        if (!$companyId) {
            return false;
        }

        $isManager = $user->roles()->where('slug', 'company-manager')->exists();
        if ($isManager && $user->company) {
            return in_array((int) $companyId, $user->company->getDescendantCompanyIds(), true);
        }

        return (int) $user->company_id === (int) $companyId;
    }

    public function viewAny(User $user): bool
    {
        return $user->isCreator()
            || $user->roles()->where('slug', 'company-manager')->exists()
            || $user->hasPermission('departments.index')
            || $user->hasPermission('departments.view');
    }

    public function create(User $user): bool
    {
        return $user->isCreator()
            || $user->roles()->where('slug', 'company-manager')->exists()
            || $user->hasPermission('departments.create');
    }

    public function update(User $user, Department $department): bool
    {
        if (!$this->canAccessCompany($user, $department->company_id)) {
            return false;
        }

        return $user->isCreator()
            || $user->roles()->where('slug', 'company-manager')->exists()
            || $user->hasPermission('departments.edit');
    }

    public function delete(User $user, Department $department): bool
    {
        if (!$this->canAccessCompany($user, $department->company_id)) {
            return false;
        }

        return $user->isCreator()
            || $user->roles()->where('slug', 'company-manager')->exists()
            || $user->hasPermission('departments.delete');
    }
}