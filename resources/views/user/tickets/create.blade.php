@extends('layouts.app')

@section('content')
<div class="container my-4" dir="rtl">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0 fw-bold">ثبت تیکت پشتیبانی جدید</h5>
                    <a href="{{ route('user.tickets.index') }}" class="btn btn-outline-secondary btn-sm">بازگشت</a>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('user.tickets.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">موضوع تیکت <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror" value="{{ old('subject') }}" required>
                            @error('subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">دسته‌بندی <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                    <option value="">انتخاب کنید...</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <!-- دپارتمان مربوطه -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">دپارتمان مربوطه</label>
                                <select name="department_id" id="department_select" class="form-select @error('department_id') is-invalid @enderror">
                                    <option value="">انتخاب دپارتمان...</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                                @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <!-- ارجاع به پشتیبان مسئول (اختیاری و فیلترشونده بر اساس دپارتمان) -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">ارجاع به پشتیبان مسئول <span class="text-muted small">(اختیاری)</span></label>
                            <select name="assigned_to" id="assigned_to_select" class="form-select @error('assigned_to') is-invalid @enderror">
                                <option value="">بدون تعیین مسئول اولیه (پیش‌فرض)</option>
                                @foreach($companyStaff as $staff)
                                    <option value="{{ $staff->id }}" 
                                            data-department="{{ $staff->department_id }}" 
                                            {{ old('assigned_to') == $staff->id ? 'selected' : '' }}>
                                        {{ $staff->name }} ({{ $staff->department->name ?? 'عمومی' }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">اگر دپارتمانی انتخاب کنید، لیست کارشناسان به همان دپارتمان محدود می‌شود.</small>
                            @error('assigned_to') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">اولویت درخواست <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                <option value="low">کم</option>
                                <option value="normal" selected>عادی</option>
                                <option value="high">بالا</option>
                                <option value="critical">بحرانی / فوری</option>
                            </select>
                            @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">متن پیام / شرح درخواست <span class="text-danger">*</span></label>
                            <textarea name="message" rows="5" class="form-control @error('message') is-invalid @enderror" required>{{ old('message') }}</textarea>
                            @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">فایل پیوست (اختیاری)</label>
                            <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror">
                            <small class="text-muted">فرمت‌های مجاز: تصاویر، PDF، Zip</small>
                            @error('attachment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-success px-4">ثبت و ارسال تیکت</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- اسکریپت جاوااسکریپت برای فیلتر کردن داینامیک کارشناسان بر اساس دپارتمان -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const deptSelect = document.getElementById('department_select');
    const staffSelect = document.getElementById('assigned_to_select');
    if (!deptSelect || !staffSelect) return;

    const allOptions = Array.from(staffSelect.options);

    function filterStaff() {
        const selectedDeptId = deptSelect.value;

        staffSelect.innerHTML = '';
        staffSelect.appendChild(allOptions[0]);

        allOptions.forEach((option, index) => {
            if (index === 0) return;

            const staffDeptId = option.getAttribute('data-department');

            if (!selectedDeptId || staffDeptId === selectedDeptId) {
                staffSelect.appendChild(option);
            }
        });
    }

    deptSelect.addEventListener('change', filterStaff);

    if (deptSelect.value) {
        filterStaff();
    }
});
</script>
@endsection