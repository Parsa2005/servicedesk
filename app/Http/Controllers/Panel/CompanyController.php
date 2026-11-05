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
        $companies = Company::withCount(['users', 'departments', 'tickets'])->latest()->paginate(10);
        return view('panel.companies.index', compact('companies'));
    }

    public function create()
    {
        $this->authorize('companies.create');
        return view('panel.companies.create');
    }

    public function store(Request $request)
    {
        $this->authorize('companies.create');
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:companies,name'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:companies,slug'],
            'domain' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable','boolean'],
        ]);

        Company::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'domain' => $request->domain,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('panel.companies.index')->with('success', 'شرکت جدید با موفقیت ایجاد شد.');
    }

    public function edit(Company $company)
    {
        $this->authorize('companies.edit');
        return view('panel.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $this->authorize('companies.edit');
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:companies,name,' . $company->id],
            'slug' => ['nullable', 'string', 'max:255', 'unique:companies,slug,' . $company->id],
            'domain' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable','boolean'],
        ]);

        $company->update([
            'name' => $request->name,
            'slug' => $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->name),
            'domain' => $request->domain,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('panel.companies.index')->with('success', 'اطلاعات شرکت با موفقیت ویرایش شد.');
    }

    public function destroy(Company $company)
    {
        // بررسی مجوز حذف شرکت
        $this->authorize('companies.delete');

        $company->delete();
        return back()->with('success', 'شرکت با موفقیت حذف شد.');
    }
}