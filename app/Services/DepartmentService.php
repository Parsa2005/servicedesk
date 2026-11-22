<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;

class DepartmentService
{
    protected function isCompanyManager(User $user): bool
    {
        return $user->roles()->where('slug', 'company-manager')->exists();
    }

    protected function resolveCompanyScope(User $authUser): null|array|int
    {
        if ($authUser->isCreator()) {
            return null;
        }

        if ($this->isCompanyManager($authUser) && $authUser->company) {
            return $authUser->company->getDescendantCompanyIds();
        }

        return $authUser->company_id;
    }

    public function getPaginatedDepartments(User $authUser)
    {
        $query = Department::with('company')->withCount('users');
        $scope = $this->resolveCompanyScope($authUser);

        if (is_array($scope)) {
            $query->whereIn('company_id', $scope);
        } elseif ($scope !== null) {
            $query->where('company_id', $scope);
        }

        return $query->latest()->paginate(10);
    }

    public function getActiveCompanies(User $authUser)
    {
        $query = Company::where('is_active', true);
        $scope = $this->resolveCompanyScope($authUser);

        if (is_array($scope)) {
            $query->whereIn('id', $scope);
        } elseif ($scope !== null) {
            $query->where('id', $scope);
        }

        return $query->get();
    }

    public function createDepartment(array $data): Department
    {
        return Department::create($data);
    }

    public function updateDepartment(Department $department, array $data): bool
    {
        return $department->update($data);
    }

    public function deleteDepartment(Department $department): bool
    {
        return $department->delete();
    }
}