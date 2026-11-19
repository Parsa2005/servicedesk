<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // بررسی مجوز ویرایش از طریق UserPolicy
        return $this->user()->can('update', $this->route('user'));
    }

    public function rules(): array
    {
        return [
            'level'   => ['required', 'in:user,admin'],
            'roles'   => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ];
    }
}