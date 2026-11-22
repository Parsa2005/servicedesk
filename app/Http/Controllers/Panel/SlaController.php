<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\SlaRequest;
use App\Models\Company;
use App\Models\Sla;
use App\Services\SlaService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class SlaController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected SlaService $slaService)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Sla::class);

        $user = auth()->user();

        if ($user->isCreator()) {
            $companies = Company::orderBy('name')->get();
        } else {
            $userCompany = $user->company;
            $companies = $userCompany ? $userCompany->getDescendantCompanies() : collect();
        }

        if ($companies->isEmpty()) {
            return redirect()->route('panel.dashboard')
                ->with('error', 'شرکتی در محدوده دسترسی شما برای تنظیم SLA یافت نشد.');
        }

        $selectedCompanyId = $request->filled('company_id')
            ? (int) $request->company_id
            : (int) $companies->first()->id;

        // اگر کاربر انتخاب‌شده خارج از لیست مجاز خودش بود، به اولین شرکت مجاز برمی‌گردد
        if (!$companies->contains('id', $selectedCompanyId)) {
            $selectedCompanyId = (int) $companies->first()->id;
        }

        $this->authorize('manageCompany', [Sla::class, $selectedCompanyId]);

        $selectedCompany = $companies->firstWhere('id', $selectedCompanyId) ?? Company::findOrFail($selectedCompanyId);
        $slas = $this->slaService->getCompanySlasFormatted($selectedCompanyId);

        return view('panel.slas.index', compact('companies', 'selectedCompany', 'slas'));
    }

    public function update(SlaRequest $request)
    {
        $companyId = $request->resolveTargetCompanyId();

        if (!$companyId) {
            return back()->with('error', 'شرکت معتبر برای ذخیره SLA انتخاب نشده است.');
        }

        $this->authorize('manageCompany', [Sla::class, $companyId]);

        $this->slaService->saveCompanySlas($companyId, $request->input('slas'));

        return redirect()->route('panel.slas.index', ['company_id' => $companyId])
            ->with('success', 'تنظیمات SLA شرکت با موفقیت ذخیره شد.');
    }
}