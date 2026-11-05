@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>مدیریت دپارتمان‌ها و واحدها</h2>
        <a href="{{ route('panel.departments.create') }}" class="btn btn-primary">افزودن دپارتمان جدید</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>نام دپارتمان</th>
                        <th>شرکت مربوطه</th>
                        <th>تعداد کاربران عضو</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $department)
                        <tr>
                            <td class="fw-bold">{{ $department->name }}</td>
                            <td>{{ $department->company->name ?? '---' }}</td>
                            <td>{{ $department->users_count }}</td>
                            <td>
                                <a href="{{ route('panel.departments.edit', $department->slug) }}" class="btn btn-sm btn-outline-primary">ویرایش</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">هیچ دپارتمانی ثبت نشده است.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $departments->links() }}
        </div>
    </div>
</div>
@endsection
