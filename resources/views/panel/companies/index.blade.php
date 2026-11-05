@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>مدیریت شرکت‌ها</h2>
        <a href="{{ route('panel.companies.create') }}" class="btn btn-primary">افزودن شرکت جدید</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>نام شرکت</th>
                        <th>دامنه</th>
                        <th>وضعیت</th>
                        <th>تعداد دپارتمان‌ها</th>
                        <th>تعداد کاربران</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $company)
                        <tr>
                            <td class="fw-bold">{{ $company->name }}</td>
                            <td>{{ $company->domain ?? '---' }}</td>
                            <td>
                                @if($company->is_active)
                                    <span class="badge bg-success">فعال</span>
                                @else
                                    <span class="badge bg-danger">غیرفعال</span>
                                @endif
                            </td>
                            <td>{{ $company->departments_count }}</td>
                            <td>{{ $company->users_count }}</td>
                            <td>
                                <a href="{{ route('panel.companies.edit', $company) }}" class="btn btn-sm btn-outline-primary">ویرایش</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">هیچ شرکتی ثبت نشده است.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $companies->links() }}
        </div>
    </div>
</div>
@endsection