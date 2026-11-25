<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class MembershipService
{
    public function isCreator(User $user): bool
    {
        return $user->level === 'creator'
            || (method_exists($user, 'isCreator') && $user->isCreator());
    }

    public function isCompanyManager(User $user): bool
    {
        return $user->roles()->where('slug', 'company-manager')->exists();
    }

    public function resolveCompanyScope(User $user): null|array|int
    {
        if ($this->isCreator($user)) {
            return null;
        }

        if ($this->isCompanyManager($user) && $user->company) {
            return $user->company->getDescendantCompanyIds();
        }

        return $user->company_id ? (int) $user->company_id : null;
    }

    public function canAccessCompany(User $user, ?int $companyId): bool
    {
        if ($this->isCreator($user)) {
            return true;
        }

        if (!$companyId) {
            return false;
        }

        $scope = $this->resolveCompanyScope($user);

        if (is_array($scope)) {
            return in_array((int) $companyId, $scope, true);
        }

        return (int) $scope === (int) $companyId;
    }

    protected function applyCompanyScopeToQuery($query, null|array|int $scope, string $column = 'company_id'): void
    {
        if (is_array($scope)) {
            $query->whereIn($column, $scope);
        } elseif ($scope !== null) {
            $query->where($column, $scope);
        }
    }

    public function getPaginatedMemberships(Request $request, User $authUser, int $perPage = 10)
    {
        $scope = $this->resolveCompanyScope($authUser);

        $query = User::with(['company', 'department', 'roles']);

        if (is_array($scope)) {
            $query->whereIn('company_id', $scope);

            if ($request->filled('company_id')) {
                $companyId = (int) $request->company_id;
                if (in_array($companyId, $scope, true)) {
                    $query->where('company_id', $companyId);
                } else {
                    $query->whereIn('company_id', []);
                }
            }
        } elseif ($scope !== null) {
            $query->where('company_id', $scope);

            if ($request->filled('company_id') && (int) $request->company_id !== $scope) {
                $query->whereIn('company_id', []);
            }
        } else {
            if ($request->filled('company_id')) {
                $query->where('company_id', $request->company_id);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function getScopedCompanies(User $authUser)
    {
        $scope = $this->resolveCompanyScope($authUser);
        $query = Company::where('is_active', true)->orderBy('name');
        $this->applyCompanyScopeToQuery($query, $scope);

        return $query->get();
    }

    public function getScopedDepartments(User $authUser)
    {
        $scope = $this->resolveCompanyScope($authUser);
        $query = Department::with('company')->orderBy('name');
        $this->applyCompanyScopeToQuery($query, $scope);

        return $query->get();
    }

    public function getAssignableRolesForUser(User $targetUser)
    {
        return Role::where(function ($q) use ($targetUser) {
            $q->where('company_id', $targetUser->company_id)
              ->orWhereNull('company_id');
        })->orderBy('name')->get();
    }

    public function hasInvalidRolesForCompany(array $roleIds, int $targetCompanyId): bool
    {
        if (empty($roleIds)) {
            return false;
        }

        return Role::whereIn('id', $roleIds)
            ->whereNotNull('company_id')
            ->where('company_id', '!=', $targetCompanyId)
            ->exists();
    }

    public function updateMembership(Request $request, User $authUser, User $targetUser): void
    {
        $canChangeCompany = $this->isCreator($authUser) || $this->isCompanyManager($authUser);
        $hasManagementOverride = $this->isCreator($authUser) || $this->isCompanyManager($authUser) || $authUser->level === 'admin';

        $targetCompanyId = ($canChangeCompany && $request->filled('company_id'))
            ? (int) $request->company_id
            : (int) $targetUser->company_id;

        $updateData = [];

        if ($request->filled('name') && ($authUser->hasPermission('users.edit.name') || $hasManagementOverride)) {
            $updateData['name'] = $request->name;
        }

        if ($request->filled('email') && ($authUser->hasPermission('users.edit.email') || $hasManagementOverride)) {
            $updateData['email'] = $request->email;
        }

        if ($authUser->hasPermission('users.edit.company') || $hasManagementOverride) {
            if ($request->has('company_id') || $request->has('department_id')) {
                $updateData['company_id'] = $targetCompanyId;
                $updateData['department_id'] = $request->filled('department_id') ? (int) $request->department_id : null;
            }
        }

        if (!empty($updateData)) {
            $targetUser->update($updateData);
        }

        if ($request->has('roles') && ($authUser->hasPermission('users.edit.role') || $hasManagementOverride)) {
            $targetUser->roles()->sync($request->roles ?? []);
        }
    }
}