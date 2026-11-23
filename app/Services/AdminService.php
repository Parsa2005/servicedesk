<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;

class AdminService
{
    protected function isCompanyManager(User $user): bool
    {
        return $user->roles()->where('slug', 'company-manager')->exists();
    }

    protected function resolveCompanyScope(User $authUser): null|array|int
    {
        if ($authUser->level === 'creator' || (method_exists($authUser, 'isCreator') && $authUser->isCreator())) {
            return null;
        }

        if ($this->isCompanyManager($authUser) && $authUser->company) {
            return $authUser->company->getDescendantCompanyIds();
        }

        return $authUser->company_id;
    }

    public function getFilteredUsers(Request $request, User $authUser)
    {
        $query = User::with(['roles', 'company']);
        $scope = $this->resolveCompanyScope($authUser);

        if (is_array($scope)) {
            $query->whereIn('company_id', $scope);

            if ($request->filled('company_id')) {
                $companyId = (int) $request->company_id;
                if (in_array($companyId, $scope, true)) {
                    $query->where('company_id', $companyId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }
        } elseif ($scope !== null) {
            $query->where('company_id', $scope);
        } else {
            if ($request->filled('company_id')) {
                $query->where('company_id', $request->company_id);
            }
        }

        return $query->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function updateUser(Request $request, User $user): void
    {
        $user->update([
            'level' => $request->level,
        ]);

        if ($request->level === 'admin') {
            $user->roles()->sync($request->roles ?? []);
        } else {
            $user->roles()->detach();
        }
    }

    public function deleteUser(User $user): void
    {
        $user->delete();
    }
}