@extends('layouts.app')

@section('content')
<div class="container my-4" dir="rtl">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0 fw-bold text-dark">ایجاد نقش جدید</h5>
            <a href="{{ route('panel.roles.index') }}" class="btn btn-outline-secondary btn-sm">بازگشت</a>
        </div>

        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger" style="background: #ffe6e6; color: #d9534f; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('panel.roles.store') }}" method="POST">
                @csrf

                <div class="row g-3 mb-4">
                    {{-- انتخاب شرکت (فقط برای ادمین کل / creator نمایش داده می‌شود) --}}
                    @if(auth()->user()->level === 'creator' && isset($companies) && $companies->count() > 0)
                        <div class="col-md-12">
                            <label for="company_id" class="form-label fw-bold">انتخاب شرکت <span class="text-muted">(اختیاری - خالی = نقش عمومی سیستمی)</span></label>
                            <select name="company_id" id="company_id" class="form-select @error('company_id') is-invalid @enderror">
                                <option value="">-- بدون شرکت (عمومی / سیستمی) --</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('company_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    @endif

                    <div class="col-md-6">
                        <label for="name" class="form-label fw-bold">عنوان نقش (فارسی) <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="مثال: مدیر پشتیبانی">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="slug" class="form-label fw-bold">شناسه لاتین (Slug) <span class="text-danger">*</span></label>
                        <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" placeholder="مثال: support-manager">
                        @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold m-0">تعیین دسترسی‌های نقش</h6>
                    <button type="button" class="btn btn-sm btn-link text-decoration-none" id="toggle-all">انتخاب / لغو همه</button>
                </div>

                <div class="row g-3">
                    @forelse($permissionsGrouped as $group => $permissions)
                        <div class="col-md-6 col-lg-4">
                            <div class="card border h-100">
                                <div class="card-header bg-light d-flex align-items-center justify-content-between">
                                    <div class="form-check m-0">
                                        <input class="form-check-input group-checkbox" type="checkbox" id="group-{{ $loop->index }}" data-group="{{ $loop->index }}">
                                        <label class="form-check-label fw-bold text-primary" for="group-{{ $loop->index }}">
                                            بخش: {{ ucfirst($group) }}
                                        </label>
                                    </div>
                                    <span class="badge bg-secondary rounded-pill">{{ count($permissions) }}</span>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        @foreach($permissions as $permission)
                                            <li class="mb-2 ms-3">
                                                <div class="form-check">
                                                    <input class="form-check-input permission-checkbox group-item-{{ $loop->parent->index }}" 
                                                           type="checkbox" 
                                                           name="permissions[]" 
                                                           value="{{ $permission->id }}" 
                                                           data-slug="{{ $permission->slug }}"
                                                           id="perm-{{ $permission->id }}"
                                                           {{ is_array(old('permissions')) && in_array($permission->id, old('permissions')) ? 'checked' : '' }}>
                                                    <label class="form-check-label text-dark" for="perm-{{ $permission->id }}">
                                                        {{ $permission->display_name ?? $permission->name }}
                                                        <small class="text-muted d-block" style="font-size: 0.75rem;">({{ $permission->slug }})</small>
                                                    </label>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-warning mb-0">هیچ دسترسی در دیتابیس ثبت نشده است.</div>
                        </div>
                    @endforelse
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-success px-4">ذخیره نقش</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.group-checkbox').forEach(function (groupCheck) {
        groupCheck.addEventListener('change', function () {
            const groupIndex = this.getAttribute('data-group');
            const childCheckboxes = document.querySelectorAll('.group-item-' + groupIndex);
            childCheckboxes.forEach(cb => cb.checked = this.checked);
        });
    });

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('permission-checkbox')) {
            const slug = e.target.getAttribute('data-slug');
            if (!slug) return;
            const isChecked = e.target.checked;
            document.querySelectorAll('.permission-checkbox').forEach(cb => {
                const cbSlug = cb.getAttribute('data-slug');
                if (cbSlug && cbSlug.startsWith(slug + '.')) {
                    cb.checked = isChecked;
                }
            });
        }
    });

    let allSelected = false;
    const toggleAllBtn = document.getElementById('toggle-all');
    if (toggleAllBtn) {
        toggleAllBtn.addEventListener('click', function () {
            allSelected = !allSelected;
            document.querySelectorAll('.permission-checkbox, .group-checkbox').forEach(cb => cb.checked = allSelected);
        });
    }
});
</script>
@endsection