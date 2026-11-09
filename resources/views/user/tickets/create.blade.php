@extends('layouts.app')

@section('content')
<div class="container my-5" dir="rtl">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card shadow border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-white py-4 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <h4 class="m-0 fw-bold text-dark">ثبت تیکت پشتیبانی جدید</h4>
                    <a href="{{ route('user.tickets.index') }}" class="btn btn-light border text-secondary fw-bold btn-sm px-3 rounded-pill shadow-xs">بازگشت به لیست</a>
                </div>
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('user.tickets.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- موضوع تیکت -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small">موضوع تیکت <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control rounded-pill px-4 py-2 shadow-xs @error('subject') is-invalid @enderror" value="{{ old('subject') }}" placeholder="موضوع درخواست خود را وارد کنید..." required>
                            @error('subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- انتخاب شرکت و دپارتمان مقصد -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label fw-bold text-dark small">شرکت مقصد <span class="text-danger">*</span></label>
                                <select name="target_company_id" id="target_company_select" class="form-select rounded-pill px-4 py-2 shadow-xs @error('target_company_id') is-invalid @enderror" required>
                                    <option value="">انتخاب شرکت مقصد...</option>
                                    @foreach($allowedCompanies as $comp)
                                        <option value="{{ $comp->id }}" {{ old('target_company_id') == $comp->id ? 'selected' : '' }}>
                                            {{ $comp->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('target_company_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">دپارتمان مقصد <span class="text-danger">*</span></label>
                                <select name="department_id" id="department_select" class="form-select rounded-pill px-4 py-2 shadow-xs @error('department_id') is-invalid @enderror" required>
                                    <option value="">ابتدا شرکت مقصد را انتخاب کنید...</option>
                                    @foreach($targetDepartments as $dept)
                                        <option value="{{ $dept->id }}" data-company="{{ $dept->company_id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <!-- انتخاب شخص مسئول در دپارتمان مقصد (گردش کار A -> B -> C) -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small">ارجاع به شخص مسئول (اختیاری)</label>
                            <select name="assigned_to" id="assigned_user_select" class="form-select rounded-pill px-4 py-2 shadow-xs @error('assigned_to') is-invalid @enderror">
                                <option value="">انتخاب کارشناس / مسئول مربوطه (پیش‌فرض: مدیر دپارتمان)...</option>
                                @foreach($departmentUsers as $usr)
                                    <option value="{{ $usr->id }}" data-department="{{ $usr->department_id }}" {{ old('assigned_to') == $usr->id ? 'selected' : '' }}>
                                        {{ $usr->name }} ({{ $usr->department->name ?? 'بدون دپارتمان' }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted ms-2 mt-1 d-block">می‌توانید مشخص کنید تیکت مستقیماً به دست کدام کارشناس برسد.</small>
                            @error('assigned_to') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- دسته‌بندی و اولویت -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label fw-bold text-dark small">دسته‌بندی <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select rounded-pill px-4 py-2 shadow-xs @error('category_id') is-invalid @enderror" required>
                                    <option value="">انتخاب دسته‌بندی...</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">اولویت درخواست <span class="text-danger">*</span></label>
                                <select name="priority" class="form-select rounded-pill px-4 py-2 shadow-xs @error('priority') is-invalid @enderror" required>
                                    <option value="low">کم</option>
                                    <option value="normal" selected>عادی</option>
                                    <option value="high">بالا</option>
                                    <option value="critical">بحرانی / فوری</option>
                                </select>
                                @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <!-- متن پیام / شرح درخواست -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small">شرح درخواست / متن پیام <span class="text-danger">*</span></label>
                            <textarea name="message" rows="5" class="form-control rounded-4 p-3 shadow-xs @error('message') is-invalid @enderror" placeholder="جزئیات مشکل یا درخواست خود را بنویسید..." required>{{ old('message') }}</textarea>
                            @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- پیوست چند فایل به صورت هم‌زمان (سنتی با attachments[]) -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small">فایل‌های پیوست (انتخاب چندگانه)</label>
                            <input type="file" name="attachments[]" class="form-control rounded-pill px-4 py-2 shadow-xs" multiple>
                            <small class="text-muted ms-2 mt-1 d-block">می‌توانید چند فایل را به صورت هم‌زمان انتخاب و ضمیمه کنید.</small>
                            @error('attachments.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <!-- دکمه‌های عملیاتی -->
                        <div class="text-end d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('user.tickets.index') }}" class="btn btn-light border rounded-pill px-4 fw-bold">انصراف</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm">ثبت و ارسال نهایی تیکت</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- اسکریپت فیلتر داینامیک دپارتمان‌ها و کاربران مسئول -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const companySelect = document.getElementById('target_company_select');
    const deptSelect = document.getElementById('department_select');
    const userSelect = document.getElementById('assigned_user_select');

    if (!companySelect || !deptSelect) return;

    const allDeptOptions = Array.from(deptSelect.options);
    const allUserOptions = Array.from(userSelect.options);

    companySelect.addEventListener('change', function () {
        const companyId = this.value;
        
        // فیلتر دپارتمان‌ها بر اساس شرکت انتخاب‌شده
        deptSelect.innerHTML = '<option value="">انتخاب دپارتمان...</option>';
        allDeptOptions.forEach(opt => {
            if (opt.getAttribute('data-company') === companyId) {
                deptSelect.appendChild(opt.cloneNode(true));
            }
        });

        // ریست کردن بخش کاربران
        userSelect.innerHTML = '<option value="">ابتدا دپارتمان را انتخاب کنید...</option>';
    });

    deptSelect.addEventListener('change', function () {
        const deptId = this.value;
        
        // فیلتر کاربران بر اساس دپارتمان انتخاب‌شده
        userSelect.innerHTML = '<option value="">انتخاب کارشناس / مسئول مربوطه...</option>';
        allUserOptions.forEach(opt => {
            if (!deptId || opt.getAttribute('data-department') === deptId) {
                userSelect.appendChild(opt.cloneNode(true));
            }
        });
    });
});
</script>
@endsection