<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Company;
use App\Models\User;

class DepartmentService
{
    public function getPaginatedDepartments(User $authUser)
    {
        $query = Department::with('company')->withCount('users');

        if (!$authUser->isCreator()) {
            $query->where('company_id', $authUser->company_id);
        }

        return $query->latest()->paginate(10);
    }

    public function getActiveCompanies(User $authUser)
    {
        $query = Company::where('is_active', true);

        if (!$authUser->isCreator()) {
            $query->where('id', $authUser->company_id);
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