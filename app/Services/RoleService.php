<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class RoleService
{
    public function getPaginatedRoles(Request $request, User $authUser, int $perPage = 10)
    {
        $query = Role::with(['company', 'permissions'])->withCount('permissions', 'users');

        if (!$authUser->isCreator()) {
            $query->where('company_id', $authUser->company_id);
        } elseif ($request->filled('company_id')) {
            if ($request->input('company_id') === 'global') {
                $query->whereNull('company_id');
            } else {
                $query->where('company_id', $request->company_id);
            }
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function getCompaniesForForm(User $authUser)
    {
        return $authUser->isCreator()
            ? Company::orderBy('name')->get()
            : collect();
    }

    public function getGroupedPermissions()
    {
        return Permission::orderBy('group')->orderBy('name')->get()->groupBy('group');
    }

    public function resolveTargetCompanyId(Request $request, User $authUser, ?Role $role = null): mixed
    {
        if ($role !== null) {
            return $authUser->isCreator() ? $request->company_id : $role->company_id;
        }

        return $authUser->isCreator() ? $request->company_id : $authUser->company_id;
    }

    public function createRole(array $data, array $permissionIds = []): Role
    {
        $role = Role::create([
            'name'       => $data['name'],
            'slug'       => $data['slug'],
            'company_id' => $data['company_id'] ?? null,
        ]);

        $role->permissions()->sync($permissionIds);

        return $role;
    }

    public function updateRole(Role $role, array $data, array $permissionIds = []): Role
    {
        $role->update([
            'name'       => $data['name'],
            'slug'       => $data['slug'],
            'company_id' => $data['company_id'] ?? null,
        ]);

        $role->permissions()->sync($permissionIds);

        return $role;
    }

    public function deleteRole(Role $role): void
    {
        $role->permissions()->detach();
        $role->users()->detach();
        $role->delete();
    }
}