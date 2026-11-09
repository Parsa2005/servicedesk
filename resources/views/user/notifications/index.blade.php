@extends('layouts.app')

@section('content')
<div class="container my-5" dir="rtl">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card shadow border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-white py-4 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <h4 class="m-0 fw-bold text-dark">تمام اعلان‌ها و پیگیری‌ها</h4>
                    <a href="{{ route('user.notifications.readAll') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">علامت‌گذاری همه به عنوان خوانده شده</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse(auth()->user()->notifications as $notification)
                            <a href="{{ $notification->data['url'] ?? '#' }}" class="list-group-item list-group-item-action p-4 border-bottom {{ $notification->unread() ? 'bg-body-tertiary fw-semibold' : '' }}">
                                <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                    <span class="small text-muted">{{ $notification->created_at->diffForHumans() }} ({{ $notification->created_at->format('Y/m/d H:i') }})</span>
                                    @if($notification->unread())
                                        <span class="badge bg-primary rounded-pill">جدید</span>
                                    @endif
                                </div>
                                <p class="mb-1 text-dark">{{ $notification->data['message'] ?? 'اعلان سیستم' }}</p>
                            </a>
                        @empty
                            <div class="text-center py-5 text-muted">هیچ اعلانی در سیستم ثبت نشده است.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection