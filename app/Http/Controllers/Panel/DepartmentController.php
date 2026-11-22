<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Http\Requests\DepartmentRequest;
use App\Services\DepartmentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DepartmentController extends Controller
{
    use AuthorizesRequests;

    protected $departmentService;

    public function __construct(DepartmentService $departmentService)
    {
        $this->departmentService = $departmentService;
    }

    public function index()
    {
        $this->authorize('viewAny', Department::class);

        $departments = $this->departmentService->getPaginatedDepartments(auth()->user());

        return view('panel.departments.index', compact('departments'));
    }

    public function create()
    {
        $this->authorize('create', Department::class);

        $companies = $this->departmentService->getActiveCompanies(auth()->user());

        return view('panel.departments.create', compact('companies'));
    }

    public function store(DepartmentRequest $request)
    {
        $this->departmentService->createDepartment($request->validatedData());

        return redirect()->route('panel.departments.index')
            ->with('success', 'دپارتمان جدید با موفقیت ایجاد شد.');
    }

    public function edit(Department $department)
    {
        $this->authorize('update', $department);

        $companies = $this->departmentService->getActiveCompanies(auth()->user());

        return view('panel.departments.edit', compact('department', 'companies'));
    }

    public function update(DepartmentRequest $request, Department $department)
    {
        $this->departmentService->updateDepartment($department, $request->validatedData());

        return redirect()->route('panel.departments.index')
            ->with('success', 'اطلاعات دپارتمان با موفقیت ویرایش شد.');
    }

    public function destroy(Department $department)
    {
        $this->authorize('delete', $department);

        $this->departmentService->deleteDepartment($department);

        return back()->with('success', 'دپارتمان با موفقیت حذف شد.');
    }
}