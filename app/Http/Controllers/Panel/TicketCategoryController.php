<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\TicketCategory;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class TicketCategoryController extends Controller
{
    /**
     * نمایش لیست دسته‌بندی‌ها با ایزوله‌سازی Multi-Tenant
     */
    public function index(Request $request)
    {
        // 🛡️ بررسی مجوز مشاهده لیست
        $this->authorize('categories.index');
        
        $user = auth()->user();
        $query = TicketCategory::with('company')->withCount('tickets');

        // ایزوله‌سازی بر اساس شرکت کاربر (کاربران عادی و ادمین‌های شرکت فقط دسته‌بندی‌های خودشان را می‌بینند)
        if ($user->level !== 'creator') {
            $query->where('company_id', $user->company_id);
        } else {
            // اگر ادمین کل (creator) بود، می‌تواند با فیلتر شرکت‌ها را جستجو کند
            if ($request->filled('company_id')) {
                $query->where('company_id', $request->company_id);
            }
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $categories = $query->latest()->paginate(15)->withQueryString();
        $companies = Company::all();

        return view('panel.categories.index', compact('categories', 'companies'));
    }

    /**
     * فرم ایجاد دسته‌بندی جدید
     */
    public function create()
    {
        // 🛡️ بررسی مجوز ایجاد
        $this->authorize('categories.create');

        $user = auth()->user();
        $companies = ($user->level === 'creator') ? Company::all() : collect();

        return view('panel.categories.create', compact('companies'));
    }

    /**
     * ذخیره دسته‌بندی جدید با انتساب هوشمند به شرکت و بررسی عدم تکرار
     */
    public function store(Request $request)
    {
        // 🛡️ بررسی مجوز ایجاد
        $this->authorize('categories.create');

        $user = auth()->user();

        // اگر ادمین کل باشد از ریکوئست می‌خواند، در غیر این صورت به طور خودکار به شرکت کاربر متصل می‌شود
        $companyId = ($user->level === 'creator' && $request->filled('company_id')) 
            ? $request->company_id 
            : $user->company_id;

        // تولید خودکار Slug در صورت خالی بودن
        $slug = $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->name);
        $request->merge(['slug' => $slug]);

        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                // 🛡️ جلوگیری از نام تکراری درون همان شرکت
                Rule::unique('ticket_categories')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })
            ],
            'slug' => [
                'required', 'string', 'max:255',
                // 🛡️ جلوگیری از اسلاگ تکراری درون همان شرکت
                Rule::unique('ticket_categories')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })
            ],
            'company_id' => [$user->level === 'creator' ? 'required' : 'nullable', 'exists:companies,id'],
        ], [
            'name.required'       => 'وارد کردن نام دسته‌بندی الزامی است.',
            'name.unique'         => 'این نام دسته‌بندی قبلاً برای این شرکت ثبت شده است.',
            'slug.unique'         => 'این شناسه (Slug) قبلاً برای این شرکت ثبت شده است.',
            'company_id.required' => 'انتخاب شرکت الزامی است.',
            'company_id.exists'   => 'شرکت انتخاب‌شده معتبر نیست.',
        ]);

        TicketCategory::create([
            'name'        => $request->name,
            'slug'        => $request->slug,
            'company_id'  => $companyId,
            'description' => $request->description ?? null,
        ]);

        return redirect()->route('panel.categories.index')
            ->with('success', 'دسته‌بندی جدید با موفقیت ایجاد شد.');
    }

    /**
     * فرم ویرایش دسته‌بندی با بررسی امنیت Multi-Tenant
     */
    public function edit(TicketCategory $category)
    {
        // 🛡️ بررسی مجوز ویرایش
        $this->authorize('categories.edit');

        $user = auth()->user();

        // بررسی امنیت: جلوگیری از دسترسی ادمین یک شرکت به دسته‌بندی شرکت دیگر
        if ($user->level !== 'creator' && $category->company_id !== $user->company_id) {
            abort(403, 'شما مجاز به ویرایش این دسته‌بندی نیستید.');
        }

        $companies = ($user->level === 'creator') ? Company::all() : collect();

        return view('panel.categories.edit', compact('category', 'companies'));
    }

    /**
     * بروزرسانی دسته‌بندی با بررسی یکتا بودن نام در شرکت
     */
    public function update(Request $request, TicketCategory $category)
    {
        // 🛡️ بررسی مجوز ویرایش
        $this->authorize('categories.edit');

        $user = auth()->user();

        // بررسی امنیت: جلوگیری از دستکاری غیرمجاز
        if ($user->level !== 'creator' && $category->company_id !== $user->company_id) {
            abort(403, 'شما مجاز به ویرایش این دسته‌بندی نیستید.');
        }

        $companyId = ($user->level === 'creator' && $request->filled('company_id')) 
            ? $request->company_id 
            : $category->company_id;

        $slug = $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->name);
        $request->merge(['slug' => $slug]);

        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('ticket_categories')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })->ignore($category->id)
            ],
            'slug' => [
                'required', 'string', 'max:255',
                Rule::unique('ticket_categories')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })->ignore($category->id)
            ],
            'company_id' => [$user->level === 'creator' ? 'required' : 'nullable', 'exists:companies,id'],
        ], [
            'name.required'       => 'وارد کردن نام دسته‌بندی الزامی است.',
            'name.unique'         => 'این نام دسته‌بندی قبلاً برای این شرکت ثبت شده است.',
            'slug.unique'         => 'این شناسه (Slug) قبلاً برای این شرکت ثبت شده است.',
            'company_id.required' => 'انتخاب شرکت الزامی است.',
        ]);

        $category->update([
            'name'        => $request->name,
            'slug'        => $request->slug,
            'company_id'  => $companyId,
            'description' => $request->description ?? null,
        ]);

        return redirect()->route('panel.categories.index')
            ->with('success', 'دسته‌بندی با موفقیت به‌روزرسانی شد.');
    }

    /**
     * حذف دسته‌بندی با بررسی وابستگی‌ها
     */
    public function destroy(TicketCategory $category)
    {
        // 🛡️ بررسی مجوز حذف
        $this->authorize('categories.destroy');

        $user = auth()->user();

        if ($user->level !== 'creator' && $category->company_id !== $user->company_id) {
            abort(403, 'شما مجاز به حذف این دسته‌بندی نیستید.');
        }

        if ($category->tickets()->count() > 0) {
            return back()->with('error', 'به دلیل وجود تیکت‌های مرتبط، امکان حذف این دسته‌بندی وجود ندارد.');
        }

        $category->delete();

        return redirect()->route('panel.categories.index')
            ->with('success', 'دسته‌بندی با موفقیت حذف شد.');
    }
}