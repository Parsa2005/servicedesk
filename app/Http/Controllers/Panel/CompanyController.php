<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Http\Requests\CompanyRequest;
use App\Services\CompanyService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CompanyController extends Controller
{
    use AuthorizesRequests;

    protected $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    public function index()
    {
        $this->authorize('viewAny', Company::class);

        $companies = $this->companyService->getPaginatedCompanies(auth()->user());

        return view('panel.companies.index', compact('companies'));
    }

    public function create()
    {
        $this->authorize('create', Company::class);

        $companies = $this->companyService->getAllCompanies(auth()->user());

        return view('panel.companies.create', compact('companies'));
    }

    public function store(CompanyRequest $request)
    {
        $this->companyService->createCompany($request->validatedData());

        return redirect()->route('panel.companies.index')
            ->with('success', 'شرکت جدید با موفقیت ایجاد شد.');
    }

    public function edit(Company $company)
    {
        $this->authorize('update', $company);

        $companies = $this->companyService->getOtherCompanies($company, auth()->user());

        return view('panel.companies.edit', compact('company', 'companies'));
    }

    public function update(CompanyRequest $request, Company $company)
    {
        $this->companyService->updateCompany($company, $request->validatedData());

        return redirect()->route('panel.companies.index')
            ->with('success', 'اطلاعات شرکت با موفقیت ویرایش شد.');
    }

    public function destroy(Company $company)
    {
        $this->authorize('delete', $company);

        if ($company->children()->count() > 0) {
            return back()->with('error', 'این شرکت دارای زیرمجموعه است؛ ابتدا باید زیرمجموعه‌های آن را تعیین تکلیف کنید.');
        }

        $this->companyService->deleteCompany($company);

        return back()->with('success', 'شرکت با موفقیت حذف شد.');
    }
}