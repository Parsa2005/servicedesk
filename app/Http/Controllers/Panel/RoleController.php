<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('roles.index');
        
        $authUser = auth()->user();
        
        $query = Role::with(['company', 'permissions'])->withCount('permissions', 'users');

    
        if ($authUser->level !== 'creator') {
            $query->where('company_id', $authUser->company_id);
        } else {
        
            if ($request->filled('company_id')) {
                $query->where('company_id', $request->company_id);
            }
        }

        $roles = $query->latest()->paginate(10)->withQueryString();
        $companies = Company::all();

        return view('panel.roles.index', compact('roles', 'companies'));
    }

    public function create()
    {
        $this->authorize('roles.create');
        
        $authUser = auth()->user();
        
        $companies = ($authUser->level === 'creator') ? Company::all() : collect();
        $permissionsGrouped = Permission::all()->groupBy('group');

        return view('panel.roles.create', compact('permissionsGrouped', 'companies'));
    }

    public function store(Request $request)
    {
        $this->authorize('roles.create');
        
        $authUser = auth()->user();

        
        $companyId = ($authUser->level === 'creator') ? $request->company_id : $authUser->company_id;

        $request->validate([
            'name'          => ['required', 'string', 'max:255'],

            'slug'          => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('roles')->where(function ($query) use ($companyId) {
                    if ($companyId) {
                        $query->where('company_id', $companyId);
                    } else {
                        $query->whereNull('company_id');
                    }
                })
            ],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
            'company_id'    => ['nullable', 'exists:companies,id'],
        ]);

        $role = Role::create([
            'name'       => $request->name,
            'slug'       => $request->slug,
            'company_id' => $companyId,
        ]);

        
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return redirect()->route('panel.roles.index')->with('success', 'نقش جدید با موفقیت ایجاد شد.');
    }

    public function edit(Role $role)
    {
        $this->authorize('roles.edit');
        
        $authUser = auth()->user();

        
        if ($authUser->level !== 'creator' && $role->company_id !== $authUser->company_id) {
            abort(403, 'شما مجاز به ویرایش این نقش نیستید.');
        }

        $companies = ($authUser->level === 'creator') ? Company::all() : collect();
        $permissionsGrouped = Permission::all()->groupBy('group');
        $role->load('permissions');

        return view('panel.roles.edit', compact('role', 'permissionsGrouped', 'companies'));
    }

    public function update(Request $request, Role $role)
    {
        $this->authorize('roles.edit');
        
        $authUser = auth()->user();

        // 🛡️ بررسی امنیت
        if ($authUser->level !== 'creator' && $role->company_id !== $authUser->company_id) {
            abort(403, 'شما مجاز به به‌روزرسانی این نقش نیستید.');
        }

        $companyId = ($authUser->level === 'creator') ? $request->company_id : $role->company_id;

        $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'slug'          => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('roles')->where(function ($query) use ($companyId) {
                    if ($companyId) {
                        $query->where('company_id', $companyId);
                    } else {
                        $query->whereNull('company_id');
                    }
                })->ignore($role->id) 
            ],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
            'company_id'    => ['nullable', 'exists:companies,id'],
        ]);

        $role->update([
            'name'       => $request->name,
            'slug'       => $request->slug,
            'company_id' => $companyId,
        ]);

        
        $role->permissions()->sync($request->input('permissions', []));

        return redirect()->route('panel.roles.index')->with('success', 'نقش با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(Role $role)
    {
        $this->authorize('roles.destroy');
        
        $authUser = auth()->user();

        
        if ($authUser->level !== 'creator' && $role->company_id !== $authUser->company_id) {
            abort(403, 'شما مجاز به حذف این نقش نیستید.');
        }

        $role->permissions()->detach();
        $role->delete();

        return redirect()->route('panel.roles.index')->with('success', 'نقش با موفقیت حذف شد.');
    }
}