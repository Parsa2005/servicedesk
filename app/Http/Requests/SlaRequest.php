<?php

namespace App\Http\Requests;


use App\Models\Sla;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SlaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $targetCompanyId = $this->resolveTargetCompanyId();
        if (!$targetCompanyId) {
            return false;
        }

        return $this->user()->can('manageCompany', [Sla::class, $targetCompanyId]);
    }

    public function resolveTargetCompanyId(): ?int
    {
        $user = $this->user();

        if ($user->isCreator() && $this->filled('company_id')) {
            return (int) $this->input('company_id');
        }

        return $user->company_id ? (int) $user->company_id : null;
    }

    public function rules(): array
    {
        return [
            'company_id'            => ['nullable', 'exists:companies,id'],
            'slas'                  => ['required', 'array'],
            'slas.low.value'        => ['required', 'numeric', 'min:1'],
            'slas.low.unit'         => ['required', 'in:hour,day'],
            'slas.normal.value'     => ['required', 'numeric', 'min:1'],
            'slas.normal.unit'      => ['required', 'in:hour,day'],
            'slas.high.value'       => ['required', 'numeric', 'min:1'],
            'slas.high.unit'        => ['required', 'in:hour,day'],
            'slas.critical.value'   => ['required', 'numeric', 'min:1'],
            'slas.critical.unit'    => ['required', 'in:hour,day'],
        ];
    }

    public function attributes(): array
    {
        return [
            'slas.low.value'      => 'زمان اولویت کم',
            'slas.normal.value'   => 'زمان اولویت عادی',
            'slas.high.value'     => 'زمان اولویت بالا',
            'slas.critical.value' => 'زمان اولویت فوری',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $toHours = function (string $priority): float {
                $val = (float) $this->input("slas.$priority.value");
                $unit = $this->input("slas.$priority.unit");
                return $unit === 'day' ? $val * 24 : $val;
            };

            $criticalHours = $toHours('critical');
            $highHours     = $toHours('high');
            $normalHours   = $toHours('normal');
            $lowHours      = $toHours('low');

            // ترتیب منطقی: فوری <= بالا <= عادی <= کم
            if ($criticalHours > $highHours) {
                $validator->errors()->add('slas.critical.value', 'زمان حالت فوری نمی‌تواند از زمان حالت بالا بیشتر باشد.');
            }

            if ($highHours > $normalHours) {
                $validator->errors()->add('slas.high.value', 'زمان حالت بالا نمی‌تواند از زمان حالت عادی بیشتر باشد.');
            }

            if ($normalHours > $lowHours) {
                $validator->errors()->add('slas.normal.value', 'زمان حالت عادی نمی‌تواند از زمان حالت کم بیشتر باشد.');
            }
        });
    }
}