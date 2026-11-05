@extends('layouts.app')

@section('content')
<div class="container my-4" dir="rtl">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold m-0">[مدیریت] {{ $ticket->subject }}</h4>
            <small class="text-muted">کد پیگیری: <span class="font-monospace text-primary fw-bold">{{ $ticket->tracking_code }}</span></small>
        </div>
        <div class="d-flex gap-2">
            <!-- 👈 دکمه بستن تیکت توسط ادمین -->
            @if($ticket->status !== 'closed')
                <form action="{{ route('panel.tickets.close', $ticket->id) }}" method="POST" onsubmit="return confirm('آیا از بستن این تیکت اطمینان دارید؟');">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-outline-danger btn-sm">بستن تیکت</button>
                </form>
            @endif
            <a href="{{ route('panel.tickets.index') }}" class="btn btn-outline-secondary btn-sm">بازگشت به لیست</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- اطلاعات کامل ثبت‌کننده تیکت و دپارتمان ارجاعی -->
    <div class="card shadow-sm border-0 mb-4 bg-white">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-dark">مشخصات ثبت‌کننده و ارجاعات سازمانی</h6>
        </div>
        <div class="card-body">
            <div class="row g-3 text-sm">
                <div class="col-md-3">
                    <span class="text-muted d-block mb-1">نام ثبت‌کننده:</span>
                    <strong class="text-dark">{{ $ticket->user->name ?? '-' }}</strong>
                </div>
                <div class="col-md-3">
                    <span class="text-muted d-block mb-1">ایمیل:</span>
                    <span class="font-monospace text-secondary">{{ $ticket->user->email ?? '-' }}</span>
                </div>
                <div class="col-md-3">
                    <span class="text-muted d-block mb-1">شرکت ارجاع‌شده:</span>
                    <span class="badge bg-info text-dark">{{ $ticket->company->name ?? 'بدون شرکت' }}</span>
                </div>
                <div class="col-md-3">
                    <span class="text-muted d-block mb-1">دپارتمان (واحد ارجاعی):</span>
                    <span class="badge bg-success">{{ $ticket->department->name ?? 'کل شرکت' }}</span>
                </div>
            </div>
            <div class="mt-3 pt-3 border-top text-muted small d-flex justify-content-between align-items-center">
                <div>تاریخ ثبت: <span class="fw-semibold text-dark">{{ $ticket->created_at->format('Y/m/d - H:i') }}</span></div>
                <div>(<span class="text-primary fw-semibold">{{ $ticket->created_at->diffForHumans() }}</span>)</div>
            </div>
        </div>
    </div>

    <!-- تغییر وضعیت سریع توسط ادمین -->
    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold">وضعیت فعلی تیکت:</span>
            <form action="{{ route('panel.tickets.status', $ticket->id) }}" method="POST" class="d-flex align-items-center gap-2">
                @csrf
                @method('PATCH')
                <select name="status" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                    <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>باز</option>
                    <option value="pending" {{ $ticket->status == 'pending' ? 'selected' : '' }}>در انتظار بررسی</option>
                    <option value="in_progress" {{ $ticket->status == 'in_progress' ? 'selected' : '' }}>در حال بررسی</option>
                    <option value="resolved" {{ $ticket->status == 'resolved' ? 'selected' : '' }}>حل شده</option>
                    <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>بسته شده</option>
                </select>
            </form>
        </div>
    </div>

    <!-- متن اصلی تیکت -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ $ticket->user->name }} <span class="badge bg-primary ms-2">نویسنده تیکت</span></span>
            <small class="text-muted">{{ $ticket->created_at->format('Y/m/d H:i') }} ({{ $ticket->created_at->diffForHumans() }})</small>
        </div>
        <div class="card-body">
            <p class="mb-0" style="white-space: pre-line;">{{ $ticket->description }}</p>
        </div>
    </div>

    <!-- تاریخچه گفتگوها و یادداشت‌های داخلی -->
    @foreach($ticket->comments as $comment)
        @php
            $userRoleTitle = $comment->user->roles->pluck('name')->join(', ') ?: ($comment->user->level === 'creator' ? 'سازنده سیستم' : 'کاربر سازمان');
        @endphp
        <div class="card shadow-sm border-0 mb-3 {{ $comment->is_internal ? 'border-warning bg-warning-subtle' : '' }}">
            <div class="card-header py-2 d-flex justify-content-between align-items-center {{ $comment->is_internal ? 'bg-warning text-dark' : 'bg-light' }}">
                <span class="fw-bold">
                    {{ $comment->user->name }} 
                    <span class="badge bg-secondary ms-1 text-xs">{{ $userRoleTitle }}</span>
                    <span class="text-muted small ms-1">({{ $comment->user->company->name ?? 'بدون شرکت' }})</span>
                    @if($comment->is_internal)
                        <span class="badge bg-dark ms-2">یادداشت داخلی (محرمانه)</span>
                    @endif
                </span>
                <small>{{ $comment->created_at->diffForHumans() }}</small>
            </div>
            <div class="card-body">
                <p class="mb-0" style="white-space: pre-line;">{{ $comment->message }}</p>
            </div>
        </div>
    @endforeach

    <!-- فرم پاسخ‌دهی ادمین -->
    @if($ticket->status !== 'closed')
        @if(auth()->user()->hasPermission('tickets.reply') || auth()->user()->level === 'creator')
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-bold">پاسخ به تیکت / ثبت یادداشت داخلی</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('panel.tickets.reply', $ticket->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <textarea name="message" rows="4" class="form-control" placeholder="متن پیام خود را بنویسید..." required>{{ old('message') }}</textarea>
                        </div>

                        @if(auth()->user()->hasPermission('tickets.internal-notes') || auth()->user()->level === 'creator')
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="is_internal" value="1" class="form-check-input" id="internalNoteCheck" {{ old('is_internal') ? 'checked' : '' }}>
                                <label class="form-check-label text-danger fw-semibold" for="internalNoteCheck">
                                    ثبت به عنوان یادداشت داخلی (کاربر ثبت‌کننده تیکت این پیام را نخواهد دید)
                                </label>
                            </div>
                        @endif

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-4">ارسال پاسخ</button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div class="alert alert-secondary text-center">
                شما مجوز لازم برای ارسال پاسخ به این تیکت را ندارید.
            </div>
        @endif
    @else
        <div class="alert alert-dark text-center">
            این تیکت بسته شده است و امکان ارسال پاسخ جدید وجود ندارد.
        </div>
    @endif
</div>
@endsection