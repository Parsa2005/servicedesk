@extends('layouts.app')

@section('content')
<div class="container my-4" dir="rtl">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0 fw-bold text-dark">مدیریت دسته‌بندی‌های تیکت</h5>
            <a href="{{ route('panel.categories.create') }}" class="btn btn-primary btn-sm">ایجاد دسته‌بندی جدید</a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- فیلترها و جستجو -->
            <form method="GET" action="{{ route('panel.categories.index') }}" class="row g-3 mb-4">
                @if(auth()->user()->level === 'creator')
                    <div class="col-md-4">
                        <select name="company_id" class="form-select" onchange="this.form.submit()">
                            <option value="">همه شرکت‌ها</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-{{ auth()->user()->level === 'creator' ? '8' : '12' }}">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="جستجو بر اساس نام دسته‌بندی..." value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit">جستجو</button>
                        @if(request('search') || request('company_id'))
                            <a href="{{ route('panel.categories.index') }}" class="btn btn-outline-danger">حذف فیلتر</a>
                        @endif
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>نام دسته‌بندی</th>
                            <th>شناسه (Slug)</th>
                            @if(auth()->user()->level === 'creator')
                                <th>شرکت مربوطه</th>
                            @endif
                            <th>تعداد تیکت‌ها</th>
                            <th>تاریخ ایجاد</th>
                            <th class="text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>{{ $categories->firstItem() + $loop->index }}</td>
                                <td class="fw-bold">{{ $category->name }}</td>
                                <td><code>{{ $category->slug }}</code></td>
                                @if(auth()->user()->level === 'creator')
                                    <td>{{ $category->company->name ?? 'بدون شرکت' }}</td>
                                @endif
                                <td><span class="badge bg-info text-dark">{{ $category->tickets_count ?? 0 }}</span></td>
                                <td>{{ $category->created_at ? $category->created_at->format('Y/m/d') : '-' }}</td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('panel.categories.edit', $category) }}" class="btn btn-outline-warning">ویرایش</a>
                                        <form action="{{ route('panel.categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('آیا از حذف این دسته‌بندی اطمینان دارید؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger">حذف</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->level === 'creator' ? '7' : '6' }}" class="text-center py-4 text-muted">
                                    هیچ دسته‌بندی‌ای یافت نشد.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $categories->links() }}
            </div>
        </div>
    </div>
</div>
@endsection