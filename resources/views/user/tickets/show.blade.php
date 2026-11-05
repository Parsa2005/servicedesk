@extends('layouts.app')

@section('content')
<div class="container my-4" dir="rtl">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold m-0">{{ $ticket->subject }}</h4>
            <small class="text-muted">کد پیگیری: <span class="font-monospace text-primary fw-bold">{{ $ticket->tracking_code }}</span></small>
        </div>
        <div class="d-flex gap-2">
            <!-- 👈 دکمه بستن تیکت توسط کاربر ایجادکننده -->
            @if($ticket->status !== 'closed')
                <form action="{{ route('user.tickets.close', $ticket->id) }}" method="POST" onsubmit="return confirm('آیا از بستن این تیکت اطمینان دارید؟');">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-outline-danger btn-sm">بستن تیکت</button>
                </form>
            @endif
            <a href="{{ route('user.tickets.index') }}" class="btn btn-outline-secondary btn-sm">بازگشت به لیست</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- وضعیت تیکت -->
    <div class="alert alert-light border mb-4 d-flex justify-content-between align-items-center">
        <span>وضعیت: <strong>{{ $ticket->status_label }}</strong></span>
        <span class="text-muted small">دپارتمان: {{ $ticket->department->name ?? 'عمومی' }}</span>
    </div>

    <!-- متن اصلی تیکت -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between">
            <span class="fw-bold">{{ $ticket->user->name }}</span>
            <small class="text-muted">{{ $ticket->created_at->format('Y/m/d H:i') }}</small>
        </div>
        <div class="card-body">
            <p class="mb-0" style="white-space: pre-line;">{{ $ticket->description }}</p>
        </div>
    </div>

    <!-- تاریخچه پاسخ‌ها -->
    @foreach($ticket->comments as $comment)
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                <span class="fw-bold">{{ $comment->user->name }}</span>
                <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
            </div>
            <div class="card-body">
                <p class="mb-0" style="white-space: pre-line;">{{ $comment->message }}</p>
            </div>
        </div>
    @endforeach

    <!-- فرم ارسال پاسخ کاربر -->
    @if($ticket->status !== 'closed')
        <div class="card shadow-sm border-0 mt-4">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold">ارسال پاسخ جدید</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('user.tickets.reply', $ticket->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <textarea name="message" rows="4" class="form-control" placeholder="متن پیام خود را بنویسید..." required>{{ old('message') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">فایل پیوست (اختیاری):</label>
                        <input type="file" name="attachment" class="form-control form-control-sm">
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary px-4">ارسال پاسخ</button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <div class="alert alert-dark text-center">
            این تیکت بسته شده است و امکان ارسال پاسخ جدید وجود ندارد.
        </div>
    @endif
</div>
@endsection