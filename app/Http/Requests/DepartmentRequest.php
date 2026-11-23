<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class DepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->isMethod('post')) {
            return $this->user()->can('create', \App\Models\Department::class);
        }

        return $this->user()->can('update', $this->route('department'));
    }

    public function rules(): array
    {
        return [
            'company_id' => [
                'required', 
                'exists:companies,id',
                function ($attribute, $value, $fail) {
                    $authUser = $this->user();
                    // اگر ادمین کل نیست، نمی‌تواند برای شرکت دیگری دپارتمان بسازد
                    if (!$authUser->isCreator() && $value != $authUser->company_id) {
                        $fail('شما مجاز به انتخاب شرکت دیگری نیستید.');
                    }
                }
            ],
            'name'       => ['required', 'string', 'max:255'],
            'slug'       => ['nullable', 'string', 'max:255'],
        ];
    }

    
    public function validatedData(): array
    {
        $data = $this->validated();
        
        $data['slug'] = $this->filled('slug') 
            ? Str::slug($this->slug) 
            : Str::slug($this->name);

        return $data;
    }
}