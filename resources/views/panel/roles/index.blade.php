@extends('layouts.app')

@section('content')
<div class="container my-4" dir="rtl">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0 fw-bold text-dark">مدیریت نقش‌های سیستم</h5>
            <a href="{{ route('panel.roles.create') }}" class="btn btn-primary btn-sm">+ ایجاد نقش جدید</a>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- فیلتر بر اساس شرکت (فقط برای ادمین کل / creator) --}}
            @if(auth()->user()->level === 'creator' && isset($companies))
                <form method="GET" action="{{ route('panel.roles.index') }}" class="row g-3 mb-4 align-items-center">
                    <div class="col-md-4">
                        <select name="company_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- همه شرکت‌ها (نمایش کلی) --</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            @endif

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>عنوان نقش</th>
                            <th>شناسه لاتین (Slug)</th>
                            {{-- ستون شرکت فقط برای ادمین کل نمایش داده می‌شود --}}
                            @if(auth()->user()->level === 'creator')
                                <th>شرکت مربوطه</th>
                            @endif
                            <th>تعداد دسترسی‌ها</th>
                            <th class="text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-bold text-dark">{{ $role->name }}</td>
                                <td><code class="text-primary">{{ $role->slug }}</code></td>
                                
                                {{-- مقدار ستون شرکت برای creator --}}
                                @if(auth()->user()->level === 'creator')
                                    <td>
                                        @if($role->company)
                                            <span class="badge bg-secondary">{{ $role->company->name }}</span>
                                        @else
                                            <span class="badge bg-dark">سیستمی / عمومی (بدون شرکت)</span>
                                        @endif
                                    </td>
                                @endif

                                <td>
                                    <span class="badge bg-info text-dark">
                                        {{ $role->permissions->count() }} دسترسی
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('panel.roles.edit', $role->id) }}" class="btn btn-sm btn-outline-primary me-1">ویرایش</a>
                                    
                                    <form action="{{ route('panel.roles.destroy', $role->id) }}" method="POST" class="d-inline" onsubmit="return confirm('آیا از حذف این نقش اطمینان دارید؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->level === 'creator' ? '6' : '5' }}" class="text-center text-muted py-4">
                                    هیچ نقشی تعریف نشده است.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($roles->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $roles->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection