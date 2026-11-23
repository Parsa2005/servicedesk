<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\Company;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    use AuthorizesRequests;

    protected function isCreator(User $user): bool
    {
        return $user->level === 'creator'
            || (method_exists($user, 'isCreator') && $user->isCreator());
    }

    protected function isCompanyManager(User $user): bool
    {
        return $user->roles()->where('slug', 'company-manager')->exists();
    }

    
    protected function resolveCompanyScope(User $user): null|array|int
    {
        if ($this->isCreator($user)) {
            return null;
        }

        if ($this->isCompanyManager($user) && $user->company) {
            return $user->company->getDescendantCompanyIds();
        }

        return $user->company_id ? (int) $user->company_id : null;
    }

    protected function canAccessCompany(User $user, ?int $companyId): bool
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

    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);
        $user = auth()->user();
        $scope = $this->resolveCompanyScope($user);

        $query = User::with(['company', 'department', 'roles']);

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

            if ($request->filled('company_id') && (int) $request->company_id !== $scope) {
                $query->whereRaw('1 = 0');
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

        $users = $query->latest()->paginate(10)->withQueryString();

        // لیست شرکت‌های فعال در محدوده دسترسی
        $companiesQuery = Company::where('is_active', true)->orderBy('name');
        if (is_array($scope)) {
            $companiesQuery->whereIn('id', $scope);
        } elseif ($scope !== null) {
            $companiesQuery->where('id', $scope);
        }
        $companies = $companiesQuery->get();

        // دریافت دپارتمان‌ها بر اساس قلمرو دسترسی برای فیلتر دینامیک در مودال
        $departmentsQuery = Department::with('company')->orderBy('name');
        if (is_array($scope)) {
            $departmentsQuery->whereIn('company_id', $scope);
        } elseif ($scope !== null) {
            $departmentsQuery->where('company_id', $scope);
        }
        $departments = $departmentsQuery->get();

        return view('panel.membership.index', compact('users', 'companies', 'departments'));
    }

    
    public function edit(User $user)
    {
        $this->authorize('update', $user);
        $authUser = auth()->user();

        if (!$this->canAccessCompany($authUser, $user->company_id)) {
            abort(403, 'شما مجاز به ویرایش عضویت این کاربر نیستید.');
        }

        $user->load(['roles', 'department', 'company']);
        $scope = $this->resolveCompanyScope($authUser);

        $companiesQuery = Company::where('is_active', true)->orderBy('name');
        $departmentsQuery = Department::with('company')->orderBy('name');

        if (is_array($scope)) {
            $companiesQuery->whereIn('id', $scope);
            $departmentsQuery->whereIn('company_id', $scope);
        } elseif ($scope !== null) {
            $companiesQuery->where('id', $scope);
            $departmentsQuery->where('company_id', $scope);
        }

        $companies = $companiesQuery->get();
        $departments = $departmentsQuery->get();

        $roles = Role::where(function ($q) use ($user) {
            $q->where('company_id', $user->company_id)
              ->orWhereNull('company_id');
        })->orderBy('name')->get();

        return view('panel.membership.edit', compact('user', 'roles', 'departments', 'companies'));
    }

    
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);
        $authUser = auth()->user();

        if (!$this->canAccessCompany($authUser, $user->company_id)) {
            abort(403, 'شما مجاز به ویرایش عضویت این کاربر نیستید.');
        }

        $canChangeCompany = $this->isCreator($authUser) || $this->isCompanyManager($authUser);

        $request->validate([
            'name'          => ['sometimes', 'required', 'string', 'max:255'],
            'email'         => ['sometimes', 'required', 'email', 'unique:users,email,' . $user->id],
            'department_id' => ['nullable', 'exists:departments,id'],
            'company_id'    => [$canChangeCompany ? 'nullable' : 'nullable', 'exists:companies,id'],
            'roles'         => ['nullable', 'array'],
            'roles.*'       => ['exists:roles,id'],
        ]);

        $targetCompanyId = ($canChangeCompany && $request->filled('company_id'))
            ? (int) $request->company_id
            : (int) $user->company_id;

        if ($request->filled('company_id') && !$this->canAccessCompany($authUser, $targetCompanyId)) {
            return back()->with('error', 'شرکت انتخاب‌شده خارج از محدوده دسترسی شماست.');
        }

        // بررسی اعتبار نقش‌های انتخابی (متعلق به شرکت هدف یا نقش پایه سراسری)
        if ($request->has('roles')) {
            $invalidRoles = Role::whereIn('id', $request->roles)
                ->whereNotNull('company_id')
                ->where('company_id', '!=', $targetCompanyId)
                ->exists();

            if ($invalidRoles) {
                return back()->with('error', 'برخی از نقش‌های انتخاب‌شده معتبر نیستند یا متعلق به این شرکت نمی‌باشند.');
            }
        }

        $updateData = [];

        if ($request->filled('name') && ($authUser->hasPermission('users.edit.name') || $this->isCreator($authUser) || $this->isCompanyManager($authUser) || $authUser->level === 'admin')) {
            $updateData['name'] = $request->name;
        }

        if ($request->filled('email') && ($authUser->hasPermission('users.edit.email') || $this->isCreator($authUser) || $this->isCompanyManager($authUser) || $authUser->level === 'admin')) {
            $updateData['email'] = $request->email;
        }

        if ($authUser->hasPermission('users.edit.company') || $this->isCreator($authUser) || $this->isCompanyManager($authUser) || $authUser->level === 'admin') {
            if ($request->has('company_id') || $request->has('department_id')) {
                $updateData['company_id'] = $targetCompanyId;
                $updateData['department_id'] = $request->filled('department_id') ? (int) $request->department_id : null;
            }
        }

        if (!empty($updateData)) {
            $user->update($updateData);
        }

        if ($request->has('roles') && ($authUser->hasPermission('users.edit.role') || $this->isCreator($authUser) || $this->isCompanyManager($authUser) || $authUser->level === 'admin')) {
            $user->roles()->sync($request->roles ?? []);
        }

        return redirect()->route('panel.membership.index')
            ->with('success', 'اطلاعات و نقش‌های سازمانی کاربر با موفقیت بروزرسانی شد.');
    }
}