<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected RoleService $roleService)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Role::class);

        $roles = $this->roleService->getPaginatedRoles($request, auth()->user(), 10);
        $companies = Company::orderBy('name')->get();

        return view('panel.roles.index', compact('roles', 'companies'));
    }

    public function create()
    {
        $this->authorize('create', Role::class);

        $companies = $this->roleService->getCompaniesForForm(auth()->user());
        $permissionsGrouped = $this->roleService->getGroupedPermissions();

        return view('panel.roles.create', compact('permissionsGrouped', 'companies'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Role::class);

        $companyId = $this->roleService->resolveTargetCompanyId($request, auth()->user());
        $validated = $request->validate($this->roleRules($companyId));

        $this->roleService->createRole([
            'name'       => $validated['name'],
            'slug'       => $validated['slug'],
            'company_id' => $companyId,
        ], $validated['permissions'] ?? []);

        return redirect()->route('panel.roles.index')->with('success', 'نقش جدید با موفقیت ایجاد شد.');
    }

    public function edit(Role $role)
    {
        $this->authorize('update', $role);

        $companies = $this->roleService->getCompaniesForForm(auth()->user());
        $permissionsGrouped = $this->roleService->getGroupedPermissions();
        $role->load('permissions');

        return view('panel.roles.edit', compact('role', 'permissionsGrouped', 'companies'));
    }

    public function update(Request $request, Role $role)
    {
        $this->authorize('update', $role);

        $companyId = $this->roleService->resolveTargetCompanyId($request, auth()->user(), $role);
        $validated = $request->validate($this->roleRules($companyId, $role->id));

        $this->roleService->updateRole($role, [
            'name'       => $validated['name'],
            'slug'       => $validated['slug'],
            'company_id' => $companyId,
        ], $validated['permissions'] ?? []);

        return redirect()->route('panel.roles.index')->with('success', 'نقش با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(Role $role)
    {
        $this->authorize('delete', $role);

        $this->roleService->deleteRole($role);

        return redirect()->route('panel.roles.index')->with('success', 'نقش با موفقیت حذف شد.');
    }

    protected function roleRules(mixed $companyId, ?string $ignoreRoleId = null): array
    {
        $slugRule = Rule::unique('roles')->where(function ($query) use ($companyId) {
            return $companyId
                ? $query->where('company_id', $companyId)
                : $query->whereNull('company_id');
        });

        if ($ignoreRoleId !== null) {
            $slugRule->ignore($ignoreRoleId);
        }

        return [
            'name'          => ['required', 'string', 'max:255'],
            'slug'          => ['required', 'string', 'max:255', $slugRule],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
            'company_id'    => ['nullable', 'exists:companies,id'],
        ];
    }
}