<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $this->authorize('roles.index');
        $roles = Role::withCount('permissions', 'users')->latest()->paginate(10);
        return view('panel.roles.index', compact('roles'));
    }

    public function create()
    {
        $this->authorize('roles.create');
        // 👈 ارسال دسترسی‌ها به صورت گروه‌بندی شده برای ویو
        $permissionsGrouped = Permission::all()->groupBy('group');
        return view('panel.roles.create', compact('permissionsGrouped'));
    }

    public function store(Request $request)
    {
        $this->authorize('roles.create');
        $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'slug'          => ['required', 'string', 'max:255', 'unique:roles,slug'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role = Role::create([
            'name' => $request->name,
            'slug' => $request->slug,
        ]);

        // همگام‌سازی دسترسی‌ها
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return redirect()->route('panel.roles.index')->with('success', 'نقش جدید با موفقیت ایجاد شد.');
    }

    public function edit(Role $role)
    {
        $this->authorize('roles.edit');
        // 👈 ارسال دسترسی‌ها و اطلاعات نقش به ویو
        $permissionsGrouped = Permission::all()->groupBy('group');
        $role->load('permissions');

        return view('panel.roles.edit', compact('role', 'permissionsGrouped'));
    }

    public function update(Request $request, Role $role)
    {
        $this->authorize('roles.edit');
        $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'slug'          => ['required', 'string', 'max:255', 'unique:roles,slug,' . $role->id],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role->update([
            'name' => $request->name,
            'slug' => $request->slug,
        ]);

        // به‌روزرسانی دسترسی‌ها (اگر تیکی نبود آرایه خالی ارسال می‌شود)
        $role->permissions()->sync($request->input('permissions', []));

        return redirect()->route('panel.roles.index')->with('success', 'نقش با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(Role $role)
    {
        $this->authorize('roles.destroy');
        $role->permissions()->detach();
        $role->delete();

        return redirect()->route('panel.roles.index')->with('success', 'نقش با موفقیت حذف شد.');
    }
}