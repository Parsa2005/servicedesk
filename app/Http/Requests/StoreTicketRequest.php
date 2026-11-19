<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Company;
use App\Models\Department;
use App\Models\User;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = auth()->user();
        $senderCompany = $user->company;

        return [
            'subject'           => ['required', 'string', 'max:255'],
            'target_company_id' => [
                'required',
                'exists:companies,id',
                function ($attribute, $value, $fail) use ($senderCompany) {
                    if ($senderCompany) {
                        $targetCompany = Company::find($value);
                        if (!$senderCompany->canInteractWith($targetCompany)) {
                            $fail('شما مجاز به ارسال تیکت به این شرکت نیستید (متعلق به هرم سازمانی شما نیست).');
                        }
                    }
                }    
            ],
            'department_id'     => [
                'required',
                'exists:departments,id',
                function ($attribute, $value, $fail) {
                    $department = Department::find($value);
                    if ($department && $department->company_id != $this->target_company_id) {
                        $fail('دپارتمان انتخاب شده متعلق به شرکت مقصد نمی‌باشد.');
                    }
                }
            ],
            'assigned_to'       => [
                'nullable', 
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $targetUser = User::find($value);
                    if ($targetUser) {
                        $targetComp = Company::find($this->target_company_id);
                        $rootCompany = $targetComp ? $targetComp->getRootCompany() : null;
                        $allowedCompanyIds = $rootCompany ? $rootCompany->getGroupCompanies()->pluck('id') : collect([$this->target_company_id]);

                        if (!$allowedCompanyIds->contains($targetUser->company_id)) {
                            $fail('کاربر انتخاب‌شده برای ارجاع، متعلق به هرم سازمانی شرکت مقصد نمی‌باشد.');
                        }
                    }
                }
            ],
            'category_id'       => ['required', 'exists:ticket_categories,id'],
            'priority'          => ['required', 'in:low,normal,high,critical'],
            'message'           => ['required', 'string'],
            'attachments.*'     => ['nullable', 'file', 'max:20480'],
        ];
    }
}