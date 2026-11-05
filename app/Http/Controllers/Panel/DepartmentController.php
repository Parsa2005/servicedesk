<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DepartmentController extends Controller
{
    public function index()
    {
        $this->authorize('departments.index');
        
        $departments = Department::with('company')->withCount('users')->latest()->paginate(10);
        
        // 👈 حذف 'users' چون در این متد کوئری زده نشده است
        return view('panel.departments.index', compact('departments'));
    }

    public function create()
    {
        $this->authorize('departments.create');
        $companies = Company::where('is_active', true)->get();
        return view('panel.departments.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $this->authorize('departments.create');
        $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'name'       => ['required', 'string', 'max:255'],
            'slug'       => ['nullable', 'string', 'max:255'],
        ]);

        Department::create([
            'company_id' => $request->company_id,
            'name'       => $request->name,
            'slug'       => Str::slug($request->name), // 👈 تولید خودکار اسلاگ
        ]);

        return redirect()->route('panel.departments.index')->with('success', 'دپارتمان جدید با موفقیت ایجاد شد.');
    }

    public function edit(Department $department)
    {
        $this->authorize('departments.edit');
        $companies = Company::where('is_active', true)->get();
        return view('panel.departments.edit', compact('department', 'companies'));
    }

    public function update(Request $request, Department $department)
    {
        $this->authorize('departments.edit');
        $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'name'       => ['required', 'string', 'max:255'],
            'slug'       => ['nullable', 'string', 'max:255'],
        ]);

        $department->update([
            'company_id' => $request->company_id,
            'name'       => $request->name,
            'slug'       => $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->name),
        ]);

        return redirect()->route('panel.departments.index')->with('success', 'اطلاعات دپارتمان با موفقیت ویرایش شد.');
    }

    public function destroy(Department $department)
    {
        $this->authorize('departments.delete');
        $department->delete();
        return back()->with('success', 'نقش با موفقیت حذف شد.');
    }
}