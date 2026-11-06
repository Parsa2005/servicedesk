<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    public function index()
    {
        $this->authorize('companies.index');
        
        // 👈 بارگذاری رابطه parent برای نمایش شرکت مادر در لیست
        $companies = Company::with(['parent'])
            ->withCount(['users', 'departments', 'tickets', 'children'])
            ->latest()
            ->paginate(10);
            
        return view('panel.companies.index', compact('companies'));
    }

    public function create()
    {
        $this->authorize('companies.create');
        
        // 👈 ارسال لیست شرکت‌ها برای انتخاب به عنوان «شرکت مادر»
        $companies = Company::all();
        
        return view('panel.companies.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $this->authorize('companies.create');
        
        $request->validate([
            'name'      => ['required', 'string', 'max:255', 'unique:companies,name'],
            'slug'      => ['nullable', 'string', 'max:255', 'unique:companies,slug'],
            'parent_id' => ['nullable', 'exists:companies,id'], // 👈 اعتبارسنجی شرکت مادر
            'is_active' => ['nullable', 'boolean'],
        ]);

        Company::create([
            'name'      => $request->name,
            'slug'      => $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->name),
            'parent_id' => $request->parent_id, // 👈 ذخیره شرکت مادر
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('panel.companies.index')->with('success', 'شرکت جدید با موفقیت ایجاد شد.');
    }

    public function edit(Company $company)
    {
        $this->authorize('companies.edit');
        
        // 👈 ارسال لیست شرکت‌ها به جز خودِ شرکت (برای جلوگیری از اینکه خودش مادر خودش شود)
        $companies = Company::where('id', '!=', $company->id)->get();
        
        return view('panel.companies.edit', compact('company', 'companies'));
    }

    public function update(Request $request, Company $company)
    {
        $this->authorize('companies.edit');
        
        $request->validate([
            'name'      => ['required', 'string', 'max:255', 'unique:companies,name,' . $company->id],
            'slug'      => ['nullable', 'string', 'max:255', 'unique:companies,slug,' . $company->id],
            'parent_id' => [
                'nullable', 
                'exists:companies,id',
                function ($attribute, $value, $fail) use ($company) {
                    // 🛡️ جلوگیری از انتخاب خود شرکت به عنوان والد
                    if ($value == $company->id) {
                        $fail('شرکت نمی‌تواند مادر خودش باشد.');
                    }
                }
            ],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $company->update([
            'name'      => $request->name,
            'slug'      => $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->name),
            'parent_id' => $request->parent_id, // 👈 آپدیت شرکت مادر
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('panel.companies.index')->with('success', 'اطلاعات شرکت با موفقیت ویرایش شد.');
    }

    public function destroy(Company $company)
    {
        $this->authorize('companies.delete');

        // 🛡️ بررسی اینکه آیا شرکت زیرمجموعه‌ای دارد یا خیر
        if ($company->children()->count() > 0) {
            return back()->with('error', 'این شرکت دارای زیرمجموعه است؛ ابتدا باید زیرمجموعه‌های آن را تعیین تکلیف کنید.');
        }

        $company->delete();
        return back()->with('success', 'شرکت با موفقیت حذف شد.');
    }
}