<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;

class StoreCommentRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');

        return $this->user()->can('reply', $ticket);
    }

    
    public function rules(): array
    {
        $ticket = $this->route('ticket');

        return [
            'body'        => ['required', 'string'],
            'assigned_to' => [
                'nullable', 
                'exists:users,id',
                function ($attribute, $value, $fail) use ($ticket) {
                    $targetUser = User::find($value);
                    if ($targetUser) {
                        $rootCompany = $ticket->targetCompany 
                            ? $ticket->targetCompany->getRootCompany() 
                            : ($ticket->company ? $ticket->company->getRootCompany() : null);

                        $allowedCompanyIds = $rootCompany 
                            ? $rootCompany->getGroupCompanies()->pluck('id') 
                            : collect([$ticket->target_company_id, $ticket->company_id]);

                        if (!$allowedCompanyIds->contains($targetUser->company_id)) {
                            $fail('کاربر انتخاب‌شده برای ارجاع، متعلق به هرم سازمانی این تیکت نمی‌باشد.');
                        }
                    }
                }
            ],
            'attachments'   => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:20480'],
        ];
    }
}