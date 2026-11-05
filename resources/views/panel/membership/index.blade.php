@extends('layouts.app')

@section('content')
<div class="container-fluid my-4" dir="rtl">
    <div class="row justify-content-center">
        <div class="col-md-11">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0 fw-bold text-dark">مدیریت عضویت و انتساب سازمانی کاربران</h5>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- فرم فیلتر و جستجو -->
                    <form method="GET" action="{{ route('panel.membership.index') }}" class="row g-3 mb-4">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="جستجو نام یا ایمیل..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-4">
                            <select name="company_id" class="form-select">
                                <option value="">همه شرکت‌ها</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary px-4">اعمال فیلتر</button>
                            <a href="{{ route('panel.membership.index') }}" class="btn btn-outline-secondary">پاک‌سازی</a>
                        </div>
                    </form>

                    <!-- جدول کاربران -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle border">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>نام و نام خانوادگی</th>
                                    <th>ایمیل</th>
                                    <th>سطح کاربر</th>
                                    <th>شرکت</th>
                                    <th>دپارتمان (واحد سازمانی)</th>
                                    <th class="text-center">عملیات انتساب</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="fw-bold">{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            <span class="badge bg-{{ $user->level === 'creator' ? 'danger' : ($user->level === 'admin' ? 'warning text-dark' : 'secondary') }}">
                                                {{ ucfirst($user->level) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($user->company)
                                                <span class="badge bg-info text-dark fs-6">{{ $user->company->name }}</span>
                                            @else
                                                <span class="badge bg-light text-muted border">بدون شرکت</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->department)
                                                <span class="badge bg-success fs-6">{{ $user->department->name }}</span>
                                            @else
                                                <span class="text-muted small"><em>بدون دپارتمان (کل شرکت)</em></span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('panel.membership.edit', $user->id) }}" class="btn btn-sm btn-outline-primary">
                                                ویرایش عضویت
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- Modal ویرایش انتساب کاربر -->
                                    <div class="modal fade" id="membershipModal-{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('panel.membership.update', $user->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">تخصیص شرکت و دپارتمان برای: {{ $user->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-start" dir="rtl">
                                                        <!-- انتخاب شرکت (اجباری) -->
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">شرکت <span class="text-danger">*</span></label>
                                                            <select name="company_id" class="form-select company-select" data-user-id="{{ $user->id }}" required>
                                                                <option value="">انتخاب شرکت...</option>
                                                                @foreach($companies as $company)
                                                                    <option value="{{ $company->id }}" {{ old('company_id', $user->company_id) == $company->id ? 'selected' : '' }}>
                                                                        {{ $company->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <!-- انتخاب دپارتمان (اختیاری) -->
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">دپارتمان (واحد سازمانی)</label>
                                                            <select name="department_id" class="form-select department-select" id="department-select-{{ $user->id }}">
                                                                <option value="">بدون دپارتمان (انتساب به کل شرکت)</option>
                                                                @foreach($departments as $dept) <!-- 👈 حتما به صورت جمع (departments) باشد -->
                                                                    <option value="{{ $dept->id }}" data-company="{{ $dept->company_id }}" {{ old('department_id', $user->department_id) == $dept->id ? 'selected' : '' }}>
                                                                        {{ $dept->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <small class="text-muted d-block mt-1">فقط دپارتمان‌های مربوط به شرکت انتخاب‌شده فعال خواهند بود.</small>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                                                        <button type="submit" class="btn btn-success">ذخیره انتساب</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">هیچ کاربری یافت نشد.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- اسکریپت فیلتر دینامیک دپارتمان‌ها بر اساس شرکت انتخاب شده -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const companySelects = document.querySelectorAll('.company-select');

    companySelects.forEach(select => {
        function filterDepartments(companySelect) {
            const userId = companySelect.getAttribute('data-user-id');
            const selectedCompanyId = companySelect.value;
            const departmentSelect = document.getElementById('department-select-' + userId);
            const options = departmentSelect.querySelectorAll('option');

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

            // اگر دپارتمان انتخاب‌شده فعلی مربوط به شرکت جدید نباشد، آن را ریست کن
            const selectedOption = departmentSelect.options[departmentSelect.selectedIndex];
            if (selectedOption && selectedOption.disabled) {
                departmentSelect.value = '';
            }
        }

        select.addEventListener('change', function () {
            filterDepartments(this);
        });

        // اجرای اولیه هنگام باز شدن صفحه
        filterDepartments(select);
    });
});
</script>
@endsection