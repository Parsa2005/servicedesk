<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\Company;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    /**
     * نمایش لیست کاربران و اعضای سازمان با ایزوله‌سازی Multi-Tenant
     */
    public function index(Request $request)
    {
        $this->authorize('users.view');
        $user = auth()->user();
        
        $query = User::with(['company', 'department', 'roles']);

        // اگر ادمین کل (creator) نباشد، فقط کاربران شرکت خودش را می‌بیند
        if ($user->level !== 'creator') {
            $query->where('company_id', $user->company_id);
        } else {
            if ($request->filled('company_id')) {
                $query->where('company_id', $request->company_id);
            }
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $users = $query->latest()->paginate(10)->withQueryString();
        $companies = Company::where('is_active', true)->get();

        // دریافت دپارتمان‌ها بر اساس سطح دسترسی برای فیلتر دینامیک در مودال
        $departments = ($user->level === 'creator') 
            ? Department::all() 
            : Department::where('company_id', $user->company_id)->get();

        return view('panel.membership.index', compact('users', 'companies', 'departments'));
    }

    /**
     * فرم ویرایش عضویت کاربر (پشتیبانی از جستجو با اسلاگ یا آیدی)
     */
    public function edit(User $user)
    {
        $this->authorize('users.edit');
        $authUser = auth()->user();

        // بررسی امنیت Multi-Tenant
        if ($authUser->level !== 'creator' && $user->company_id !== $authUser->company_id) {
            abort(403, 'شما مجاز به ویرایش عضویت این کاربر نیستید.');
        }

        $user->load(['roles', 'department', 'company']);

        // 👈 اگر کاربر creator است همه دپارتمان‌ها و شرکت‌ها را بفرست، در غیر این صورت فقط شرکت و دپارتمان‌های شرکت خودش
        $companies = ($authUser->level === 'creator') 
            ? Company::where('is_active', true)->get() 
            : Company::where('id', $authUser->company_id)->get();

        $departments = ($authUser->level === 'creator') 
            ? Department::all() 
            : Department::where('company_id', $authUser->company_id)->get();

        $roles = Role::where('company_id', $user->company_id)->get();

        return view('panel.membership.edit', compact('user', 'roles', 'departments', 'companies'));
    }

    /**
     * بروزرسانی اطلاعات عضویت، دپارتمان و نقش‌های کاربر (سازگار با مودال و فرم کامل)
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('users.edit');
        $authUser = auth()->user();

        // بررسی امنیت Multi-Tenant
        if ($authUser->level !== 'creator' && $user->company_id !== $authUser->company_id) {
            abort(403, 'شما مجاز به ویرایش عضویت این کاربر نیستید.');
        }

        // استفاده از sometimes برای اینکه اگر نام یا ایمیل در مودال ارسال نشد خطا ندهد
        $request->validate([
            'name'          => ['sometimes', 'required', 'string', 'max:255'],
            'email'         => ['sometimes', 'required', 'email', 'unique:users,email,' . $user->id],
            'department_id' => ['nullable', 'exists:departments,id'],
            'company_id'    => [$authUser->level === 'creator' ? 'required' : 'nullable', 'exists:companies,id'],
            'roles'         => ['nullable', 'array'],
            'roles.*'       => ['exists:roles,id'],
        ]);

        $targetCompanyId = ($authUser->level === 'creator' && $request->filled('company_id')) 
            ? $request->company_id 
            : $user->company_id;

        // بررسی اعتبار نقش‌های انتخابی
        if ($request->has('roles')) {
            $invalidRoles = Role::whereIn('id', $request->roles)
                ->where('company_id', '!=', $targetCompanyId)
                ->exists();

            if ($invalidRoles) {
                return back()->with('error', 'برخی از نقش‌های انتخاب‌شده معتبر نیستند یا متعلق به این شرکت نمی‌باشند.');
            }
        }

        $updateData = [];

        // ویرایش نام (فقط در صورت ارسال)
        if ($request->filled('name') && ($authUser->hasPermission('users.edit.name') || $authUser->level === 'creator' || $authUser->level === 'admin')) {
            $updateData['name'] = $request->name;
        }

        // ویرایش ایمیل (فقط در صورت ارسال)
        if ($request->filled('email') && ($authUser->hasPermission('users.edit.email') || $authUser->level === 'creator' || $authUser->level === 'admin')) {
            $updateData['email'] = $request->email;
        }

        // ویرایش شرکت و دپارتمان (ارسال‌شده از مودال یا فرم)
        if ($authUser->hasPermission('users.edit.company') || $authUser->level === 'creator' || $authUser->level === 'admin') {
            if ($request->has('company_id')) {
                $updateData['company_id'] = $targetCompanyId;
                $updateData['department_id'] = $request->filled('department_id') ? $request->department_id : null;
            }
        }

        if (!empty($updateData)) {
            $user->update($updateData);
        }

        // همگام‌سازی نقش‌های سازمانی کاربر (در صورت ارسال)
        if ($request->has('roles') && ($authUser->hasPermission('users.edit.role') || $authUser->level === 'creator' || $authUser->level === 'admin')) {
            $user->roles()->sync($request->roles);
        }

        return redirect()->route('panel.membership.index')
            ->with('success', 'اطلاعات و نقش‌های سازمانی کاربر با موفقیت بروزرسانی شد.');
    }
}