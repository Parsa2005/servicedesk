@extends('layouts.app')

@section('content')
<div class="container my-4" dir="rtl">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0 fw-bold text-dark">ویرایش نقش: {{ $role->name }}</h5>
            <a href="{{ route('panel.roles.index') }}" class="btn btn-outline-secondary btn-sm">بازگشت</a>
        </div>

        <div class="card-body">
            {{-- 👈 اصلاح پاس دادن شیء $role به جای $role->id برای ساخت دقیق URL --}}
            <form action="{{ route('panel.roles.update', $role) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-bold">عنوان نقش (فارسی) <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $role->name) }}">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="slug" class="form-label fw-bold">شناسه لاتین (Slug) <span class="text-danger">*</span></label>
                        <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $role->slug) }}">
                        @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold m-0">مدیریت دسترسی‌های نقش</h6>
                    <button type="button" class="btn btn-sm btn-link text-decoration-none" id="toggle-all">انتخاب / لغو همه</button>
                </div>

                @php
                    $activePermissionIds = old('permissions', $role->permissions->pluck('id')->toArray());
                @endphp

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
                                                           {{ in_array($permission->id, $activePermissionIds) ? 'checked' : '' }}>
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
                    <button type="submit" class="btn btn-primary px-4">بروزرسانی تغییرات</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // انتخاب/لغو کل یک گروه
    document.querySelectorAll('.group-checkbox').forEach(function (groupCheck) {
        groupCheck.addEventListener('change', function () {
            const groupIndex = this.getAttribute('data-group');
            const childCheckboxes = document.querySelectorAll('.group-item-' + groupIndex);
            childCheckboxes.forEach(cb => cb.checked = this.checked);
        });
    });

    // انتخاب خودکار زیرمجموعه‌ها بر اساس پیشوند Slug
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

    // انتخاب/لغو کل همه گروه‌ها
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