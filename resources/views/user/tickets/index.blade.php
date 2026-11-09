@extends('layouts.app')

@section('content')
<div class="container my-5" dir="rtl">
    <div class="row justify-content-center">
        <div class="col-md-11">
            <!-- هدر صفحه و دکمه ایجاد تیکت -->
            <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-4 rounded-4 shadow-sm border border-light-subtle">
                <div>
                    <h4 class="fw-bold text-dark mb-1">لیست تیکت‌های پشتیبانی</h4>
                    <span class="text-muted small">مدیریت و پیگیری درخواست‌های ارسالی و دریافتی</span>
                </div>
                <a href="{{ route('user.tickets.create') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2Z"/>
                    </svg>
                    ثبت تیکت جدید
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm rounded-4">{{ session('success') }}</div>
            @endif

            <!-- باکس فیلتر و جستجو -->
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body p-4">
                    <form action="{{ route('user.tickets.index') }}" method="GET" class="row g-3">
                        <div class="col-md-5">
                            <input type="text" name="search" class="form-control rounded-pill px-4 shadow-xs" placeholder="جستجو در موضوع تیکت..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-4">
                            <select name="status" class="form-select rounded-pill px-4 shadow-xs">
                                <option value="">همه وضعیت‌ها</option>
                                <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>باز</option>
                                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>در حال بررسی</option>
                                <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>حل شده</option>
                                <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>بسته شده</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-dark rounded-pill px-4 w-100 fw-bold shadow-xs">فیلتر</button>
                            <a href="{{ route('user.tickets.index') }}" class="btn btn-light border rounded-pill px-3 w-100 fw-bold">حذف فیلتر</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- جدول لیست تیکت‌ها -->
            <div class="card shadow border-0 rounded-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-center">
                            <thead class="table-light text-secondary small text-uppercase">
                                <tr>
                                    <th class="py-3">کد تیکت</th>
                                    <th class="py-3 text-start pe-4">موضوع</th>
                                    <th class="py-3">شرکت مبدأ / مقصد</th>
                                    <th class="py-3">مسئول رسیدگی</th>
                                    <th class="py-3">وضعیت</th>
                                    <th class="py-3">اولویت</th>
                                    <th class="py-3">تاریخ ایجاد</th>
                                    <th class="py-3">عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tickets as $ticket)
                                    <tr>
                                        <td class="fw-bold text-muted small">#{{ $ticket->id }}</td>
                                        <td class="text-start pe-4 fw-bold text-dark">{{ Str::limit($ticket->subject, 40) }}</td>
                                        <td>
                                            <span class="small d-block text-secondary">از: {{ $ticket->company->name ?? 'مشتری آزاد' }}</span>
                                            <span class="small fw-semibold text-primary">به: {{ $ticket->targetCompany->name ?? '-' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border rounded-pill px-3 py-1">
                                                {{ $ticket->assigned->name ?? 'تعیین نشده' }}
                                            </span>
                                        </td>
                                        <td>
                                            <!-- 👈 اصلاح شد (حذف _attribute) -->
                                            <span class="badge {{ $ticket->status_badge }} rounded-pill px-3 py-2 shadow-xs">
                                                {{ $ticket->status_label }}
                                            </span>
                                        </td>
                                        <td>
                                            <!-- 👈 اصلاح شد (حذف _attribute) -->
                                            <span class="badge {{ $ticket->priority_badge_class }} rounded-pill px-3 py-1">
                                                {{ $ticket->priority_label }}
                                            </span>
                                        </td>
                                        <td class="text-muted small">{{ $ticket->created_at->format('Y/m/d H:i') }}</td>
                                        <td>
                                            <a href="{{ route('user.tickets.show', $ticket) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs fw-bold">
                                                مشاهده
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">هیچ تیکتی یافت نشد.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($tickets->hasPages())
                    <div class="card-footer bg-white py-3">
                        {{ $tickets->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection