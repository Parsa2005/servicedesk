<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    
    protected function isCompanyInScope(User $user, Company $company): bool
    {
        if ($user->isCreator()) {
            return true;
        }

        if (!$user->company) {
            return false;
        }

        $allowedIds = $user->company->getDescendantCompanyIds();

        return in_array((int) $company->id, $allowedIds, true);
    }

    public function viewAny(User $user): bool
    {
        return $user->isCreator()
            || $user->hasPermission('companies.index')
            || $user->hasPermission('companies.view');
    }

    public function create(User $user): bool
    {
        return $user->isCreator() || $user->hasPermission('companies.create');
    }

    public function update(User $user, Company $company): bool
    {
        if ($user->isCreator()) {
            return true;
        }

        return $user->hasPermission('companies.edit') && $this->isCompanyInScope($user, $company);
    }

    public function delete(User $user, Company $company): bool
    {
        if ($user->isCreator()) {
            return true;
        }

        return $user->hasPermission('companies.delete') && $this->isCompanyInScope($user, $company);
    }
}