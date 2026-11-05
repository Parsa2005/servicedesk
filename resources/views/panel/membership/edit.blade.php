@extends('layouts.app')

@section('content')
<div class="container my-4" dir="rtl">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0 fw-bold text-dark">ویرایش عضویت سازمانی کاربر: {{ $user->name }}</h5>
                    <a href="{{ route('panel.membership.index') }}" class="btn btn-outline-secondary btn-sm">
                        بازگشت به لیست عضویت‌ها
                    </a>
                </div>

                <div class="card-body">
                    <!-- خلاصه اطلاعات کاربر -->
                    <div class="alert alert-light border mb-4">
                        <div class="row">
                            <div class="col-md-6 mb-2 mb-md-0">
                                <strong>نام و نام خانوادگی:</strong> {{ $user->name }}
                            </div>
                            <div class="col-md-6 mb-2 mb-md-0">
                                <strong>ایمیل:</strong> {{ $user->email }}
                            </div>
                            <div class="col-md-6 mt-2">
                                <strong>سطح کاربر:</strong> 
                                <span class="badge bg-{{ $user->level === 'creator' ? 'danger' : ($user->level === 'admin' ? 'warning text-dark' : 'secondary') }}">
                                    {{ ucfirst($user->level) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('panel.membership.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- انتخاب شرکت (اجباری) -->
                        <div class="mb-4">
                            <label for="company_id" class="form-label fw-bold">شرکت مربوطه <span class="text-danger">*</span></label>
                            <select name="company_id" id="company_id" class="form-select @error('company_id') is-invalid @enderror" required>
                                <option value="">انتخاب شرکت...</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ old('company_id', $user->company_id) == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('company_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- انتخاب دپارتمان (اختیاری) -->
                        <div class="mb-4">
                            <label for="department_id" class="form-label fw-bold">واحد سازمانی (دپارتمان)</label>
                            <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror">
                                <option value="">بدون دپارتمان (انتساب به کل شرکت)</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" data-company="{{ $dept->company_id }}" {{ old('department_id', $user->department_id) == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1">انتخاب دپارتمان اختیاری است. تنها دپارتمان‌های مربوط به شرکت انتخاب‌شده قابل انتخاب هستند.</small>
                            @error('department_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="text-end border-top pt-3">
                            <a href="{{ route('panel.membership.index') }}" class="btn btn-secondary me-2">انصراف</a>
                            <button type="submit" class="btn btn-success px-4">ذخیره تغییرات عضویت</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- اسکریپت فیلتر دینامیک دپارتمان بر اساس شرکت -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const companySelect = document.getElementById('company_id');
    const departmentSelect = document.getElementById('department_id');
    const options = departmentSelect.querySelectorAll('option');

    function filterDepartments() {
        const selectedCompanyId = companySelect.value;

        options.forEach(option => {
            const deptCompany = option.getAttribute('data-company');
            if (!deptCompany || deptCompany === selectedCompanyId) {
                option.style.display = 'block';
                option.disabled = false;
            } else {
                option.style.display = 'none';
                option.disabled = true;
            }
        });

        const selectedOption = departmentSelect.options[departmentSelect.selectedIndex];
        if (selectedOption && selectedOption.disabled) {
            departmentSelect.value = '';
        }
    }

    companySelect.addEventListener('change', filterDepartments);
    filterDepartments(); // اجرای اولیه در زمان لود صفحه
});
</script>
@endsection