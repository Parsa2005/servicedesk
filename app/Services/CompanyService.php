<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;

class CompanyService
{
    
    protected function allowedCompanyIds(?User $user): ?array
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return [];
        }

        if ($user->isCreator()) {
            return null;
        }

        if ($user->company) {
            return $user->company->getDescendantCompanyIds();
        }

        return [];
    }

    public function getPaginatedCompanies(?User $user = null)
    {
        $allowedIds = $this->allowedCompanyIds($user);

        $query = Company::with(['parent'])
            ->withCount(['users', 'departments', 'tickets', 'children']);

        if ($allowedIds !== null) {
            $query->whereIn('id', $allowedIds);
        }

        return $query->latest()->paginate(10);
    }

    public function getAllCompanies(?User $user = null)
    {
        $allowedIds = $this->allowedCompanyIds($user);

        $query = Company::query();

        if ($allowedIds !== null) {
            $query->whereIn('id', $allowedIds);
        }

        return $query->orderBy('name')->get();
    }

    public function getOtherCompanies(Company $company, ?User $user = null)
    {
        $allowedIds = $this->allowedCompanyIds($user);

        $query = Company::where('id', '!=', $company->id);

        if ($allowedIds !== null) {
            $query->whereIn('id', $allowedIds);
        }

        return $query->orderBy('name')->get();
    }

    public function createCompany(array $data): Company
    {
        return Company::create($data);
    }

    public function updateCompany(Company $company, array $data): bool
    {
        return $company->update($data);
    }

    public function deleteCompany(Company $company): bool
    {
        return $company->delete();
    }
}