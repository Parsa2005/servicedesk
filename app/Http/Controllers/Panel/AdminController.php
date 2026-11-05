<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Company;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // لیست کاربران با ایزوله‌سازی کامل Multi-Tenant
    public function index(Request $request)
    {
        $this->authorize('users.index');
        
        $authUser = auth()->user();
        $query = User::with(['roles', 'company']);

        // 🛡️ اگر ادمین کل (creator) نباشد، فقط کاربران شرکت خودش را می‌بیند
        if ($authUser->level !== 'creator') {
            $query->where('company_id', $authUser->company_id);
        } else {
            // اگر creator بود، می‌تواند با فیلتر، کاربران شرکت‌های مختلف را جستجو کند
            if ($request->filled('company_id')) {
                $query->where('company_id', $request->company_id);
            }
        }

        $users = $query->when($request->search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $companies = Company::all(); // برای فیلتر در پنل ادمین کل

        return view('panel.users.index', compact('users', 'companies'));
    }

    // فرم ویرایش کاربر و دسترسی‌ها با بررسی امنیت Multi-Tenant
    public function edit(User $user)
    {
        $this->authorize('users.edit');
        
        $authUser = auth()->user();

        // 🛡️ جلوگیری از دسترسی ادمین یک شرکت به ویرایش کاربران شرکت دیگر
        if ($authUser->level !== 'creator' && $user->company_id !== $authUser->company_id) {
            abort(403, 'شما مجاز به ویرایش این کاربر نیستید.');
        }

        $user->load('roles');
        $roles = Role::with('permissions')->get(); // دریافت نقش‌ها همراه با مجوزها به صورت بهینه

        return view('panel.users.edit', compact('user', 'roles'));
    }

    // ذخیره تغییرات سطح و نقش‌ها با بررسی امنیت
    public function update(Request $request, User $user)
    {
        $this->authorize('users.edit');
        
        $authUser = auth()->user();

        // 🛡️ جلوگیری از ویرایش کاربران شرکت دیگر
        if ($authUser->level !== 'creator' && $user->company_id !== $authUser->company_id) {
            abort(403, 'شما مجاز به بروزرسانی این کاربر نیستید.');
        }

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

    // حذف کاربر با بررسی امنیت و جلوگیری از حذف خود
    public function destroy(User $user)
    {
        $this->authorize('users.delete');
        
        $authUser = auth()->user();

        // 🛡️ جلوگیری از حذف کاربران شرکت دیگر
        if ($authUser->level !== 'creator' && $user->company_id !== $authUser->company_id) {
            abort(403, 'شما مجاز به حذف این کاربر نیستید.');
        }

        // جلوگیری از حذف حساب کاربری خود ادمین
        if ($user->id === $authUser->id) {
            return back()->with('error', 'امکان حذف حساب کاربری خودتان وجود ندارد.');
        }

        $user->delete();
        
        return back()->with('success', 'کاربر با موفقیت حذف شد.');
    }
}