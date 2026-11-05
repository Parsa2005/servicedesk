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

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>عنوان نقش</th>
                            <th>شناسه لاتین (Slug)</th>
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
                                <td>
                                    <span class="badge bg-info text-dark">
                                        {{ $role->permissions->count() }} دسترسی
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('panel.roles.edit', $role->slug) }}" class="btn btn-sm btn-outline-primary me-1">ویرایش</a>
                                    
                                    <form action="{{ route('panel.roles.destroy', $role->slug) }}" method="POST" class="d-inline" onsubmit="return confirm('آیا از حذف این نقش اطمینان دارید؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">هیچ نقشی تعریف نشده است.</td>
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