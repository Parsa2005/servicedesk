<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MembershipService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected MembershipService $membershipService)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);
        $user = auth()->user();

        $users = $this->membershipService->getPaginatedMemberships($request, $user, 10);
        $companies = $this->membershipService->getScopedCompanies($user);
        $departments = $this->membershipService->getScopedDepartments($user);

        return view('panel.membership.index', compact('users', 'companies', 'departments'));
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);
        $authUser = auth()->user();

        if (!$this->membershipService->canAccessCompany($authUser, $user->company_id)) {
            abort(403, 'شما مجاز به ویرایش عضویت این کاربر نیستید.');
        }

        $user->load(['roles', 'department', 'company']);
        $companies = $this->membershipService->getScopedCompanies($authUser);
        $departments = $this->membershipService->getScopedDepartments($authUser);
        $roles = $this->membershipService->getAssignableRolesForUser($user);

        return view('panel.membership.edit', compact('user', 'roles', 'departments', 'companies'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);
        $authUser = auth()->user();

        if (!$this->membershipService->canAccessCompany($authUser, $user->company_id)) {
            abort(403, 'شما مجاز به ویرایش عضویت این کاربر نیستید.');
        }

        $canChangeCompany = $this->membershipService->isCreator($authUser) || $this->membershipService->isCompanyManager($authUser);

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

        if ($request->filled('company_id') && !$this->membershipService->canAccessCompany($authUser, $targetCompanyId)) {
            return back()->with('error', 'شرکت انتخاب‌شده خارج از محدوده دسترسی شماست.');
        }

        if ($request->has('roles') && $this->membershipService->hasInvalidRolesForCompany($request->roles, $targetCompanyId)) {
            return back()->with('error', 'برخی از نقش‌های انتخاب‌شده معتبر نیستند یا متعلق به این شرکت نمی‌باشند.');
        }

        $this->membershipService->updateMembership($request, $authUser, $user);

        return redirect()->route('panel.membership.index')
            ->with('success', 'اطلاعات و نقش‌های سازمانی کاربر با موفقیت بروزرسانی شد.');
    }
}