<?php

namespace App\Services;

use App\Models\Company;

class CompanyService
{
    public function getPaginatedCompanies()
    {
        return Company::with(['parent'])
            ->withCount(['users', 'departments', 'tickets', 'children'])
            ->latest()
            ->paginate(10);
    }

    public function getAllCompanies()
    {
        return Company::all();
    }

    public function getOtherCompanies(Company $company)
    {
        return Company::where('id', '!=', $company->id)->get();
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