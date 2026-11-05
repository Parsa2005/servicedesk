<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // لیست کاربران
    public function index(Request $request)
    {
        $this->authorize('users.index');
        $users = User::with('roles')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('panel.users.index', compact('users'));
    }

    // فرم ویرایش کاربر و دسترسی‌ها (استفاده از with به جای all خالص)
    public function edit(User $user)
    {
        $this->authorize('users.edit');
        $user->load('roles');
        $roles = Role::with('permissions')->get(); // دریافت نقش‌ها همراه با مجوزها به صورت بهینه

        return view('panel.users.edit', compact('user', 'roles'));
    }

    // ذخیره تغییرات سطح و نقش‌ها (فقط بین کاربر و ادمین)
    public function update(Request $request, User $user)
    {
        $this->authorize('users.edit');
        $request->validate([
            'level' => ['required', 'in:user,admin'], // محدود کردن سطح به کاربر عادی و ادمین
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        // بروزرسانی سطح اصلی کاربر
        $user->update([
            'level' => $request->level,
        ]);

        // اگر سطح ادمین بود نقش‌ها را همگام‌سازی کن، در غیر این صورت نقش‌ها را پاک کن
        if ($request->level === 'admin') {
            $user->roles()->sync($request->roles ?? []);
        } else {
            $user->roles()->detach();
        }

        return redirect()->route('panel.users.index')->with('success', 'دسترسی‌ها و نقش‌های کاربر با موفقیت بروزرسانی شد.');
    }

    public function destroy(User $user)
    {
        $this->authorize('users.delete');
        $user->delete();
        return back()->with('success', 'نقش با موفقیت حذف شد.');
    }
}