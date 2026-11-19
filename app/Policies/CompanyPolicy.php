<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isCreator() || $user->hasPermission('companies.index') || $user->hasPermission('companies.view');
    }

    public function create(User $user): bool
    {
        return $user->isCreator() || $user->hasPermission('companies.create');
    }

    public function update(User $user, Company $company): bool
    {
        return $user->isCreator() || $user->hasPermission('companies.edit');
    }

    public function delete(User $user, Company $company): bool
    {
        return $user->isCreator() || $user->hasPermission('companies.delete');
    }
}