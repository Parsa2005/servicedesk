@extends('layouts.app')

@section('content')
<div class="container my-4" dir="rtl">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold m-0">تیکت‌های من</h4>
            <small class="text-muted">پیگیری درخواست‌ها و هماهنگی‌های درون‌سازمانی شما</small>
        </div>
        <!-- 👈 اصلاح نام روت با پیشوند user. -->
        <a href="{{ route('user.tickets.create') }}" class="btn btn-primary px-4">
            <i class="bi bi-plus-lg"></i> ثبت تیکت جدید
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <!-- 👈 اصلاح نام روت با پیشوند user. -->
            <form action="{{ route('user.tickets.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">همه وضعیت‌ها</option>
                        <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>باز</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>در حال بررسی</option>
                        <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>حل شده</option>
                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>بسته شده</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="جستجو بر اساس موضوع تیکت...">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">فیلتر</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>موضوع</th>
                            <th>دسته‌بندی</th>
                            <th>اولویت</th>
                            <th>وضعیت</th>
                            <th>تاریخ ایجاد</th>
                            <th class="text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            <tr>
                                <td class="fw-semibold">
                                    <!-- 👈 اصلاح نام روت با پیشوند user. -->
                                    <a href="{{ route('user.tickets.show', $ticket->id) }}" class="text-decoration-none text-dark">
                                        {{ Str::limit($ticket->subject, 45) }}
                                    </a>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $ticket->category->name ?? 'متفرقه' }}</span></td>
                                <td>
                                    @php
                                        $priorities = ['low' => 'secondary', 'normal' => 'info', 'high' => 'warning', 'critical' => 'danger'];
                                    @endphp
                                    <span class="badge bg-{{ $priorities[$ticket->priority] ?? 'dark' }}">
                                        {{ __($ticket->priority) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $statuses = ['open' => 'danger', 'in_progress' => 'warning', 'resolved' => 'info', 'closed' => 'secondary'];
                                    @endphp
                                    <span class="badge bg-{{ $statuses[$ticket->status] ?? 'dark' }}">
                                        {{ __($ticket->status) }}
                                    </span>
                                </td>
                                <td class="text-muted small">{{ $ticket->created_at->diffForHumans() }}</td>
                                <td class="text-center">
                                    <!-- 👈 اصلاح نام روت با پیشوند user. -->
                                    <a href="{{ route('user.tickets.show', $ticket->id) }}" class="btn btn-sm btn-outline-primary px-3">
                                        مشاهده و گفتگو
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">هیچ تیکتی ثبت نکرده‌اید.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3 d-flex justify-content-center">
                {{ $tickets->links() }}
            </div>
        </div>
    </div>
</div>
@endsection