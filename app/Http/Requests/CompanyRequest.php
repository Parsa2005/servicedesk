<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class CompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        // بررسی مجوز بر اساس متد درخواست (ایجاد یا ویرایش)
        if ($this->isMethod('post')) {
            return $this->user()->can('create', \App\Models\Company::class);
        }

        return $this->user()->can('update', $this->route('company'));
    }

    public function rules(): array
    {
        $company = $this->route('company');
        $companyId = $company ? $company->id : null;

        return [
            'name'      => ['required', 'string', 'max:255', 'unique:companies,name,' . $companyId],
            'slug'      => ['nullable', 'string', 'max:255', 'unique:companies,slug,' . $companyId],
            'parent_id' => [
                'nullable', 
                'exists:companies,id',
                function ($attribute, $value, $fail) use ($companyId) {
                    if ($companyId && $value == $companyId) {
                        $fail('شرکت نمی‌تواند مادر خودش باشد.');
                    }
                }
            ],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * آماده‌سازی داده‌های معتبر شده (اسلاگ و بولین)
     */
    public function validatedData(): array
    {
        $data = $this->validated();
        
        $data['slug'] = $this->filled('slug') 
            ? Str::slug($this->slug) 
            : Str::slug($this->name);
            
        $data['is_active'] = $this->boolean('is_active');

        return $data;
    }
}