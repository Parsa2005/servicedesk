<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\TicketCategory;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // کنترل دسترسی اصلی به عهده Policy یا Middleware است
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                'exists:ticket_categories,id',
                function ($attribute, $value, $fail) {
                    // جلوگیری از انتخاب دسته‌بندی متعلق به شرکت دیگر (امنیت Multi-Tenant)
                    $category = TicketCategory::find($value);
                    if ($category && $category->company_id !== auth()->user()->company_id) {
                        $fail('دسته‌بندی انتخاب‌شده معتبر نمی‌کند.');
                    }
                },
            ],
            'department_id' => ['nullable', 'exists:departments,id'],
            'subject'       => ['required', 'string', 'max:255'],
            'description'   => ['required', 'string'],
            'priority'      => ['required', 'in:low,normal,high,critical'],
            'attachments'   => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,pdf,zip,doc,docx', 'max:5120'], // حداکثر ۵ مگابایت
        ];
    }
}