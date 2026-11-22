@extends('layouts.app')

@section('content')
<div class="container my-5" dir="rtl">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- هدر و دکمه‌های بالا -->
            <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-4 rounded-4 shadow-sm border border-light-subtle">
                <div>
                    <h4 class="fw-bold text-dark mb-1">جزئیات تیکت: {{ $ticket->subject }}</h4>
                    <span class="text-muted small">کد رهگیری: <span class="badge bg-light text-dark border">#{{ $ticket->tracking_code }}</span> | تاریخ ایجاد: {{ $ticket->created_at->format('Y/m/d H:i') }}</span>
                </div>
                <div class="d-flex gap-2">
                    @if($ticket->canBeEditedBy(auth()->user()))
                        <a href="{{ route('user.tickets.edit', $ticket) }}" class="btn btn-warning text-white fw-bold btn-sm px-3 shadow-sm d-flex align-items-center gap-1 rounded-pill">
                            ویرایش تیکت
                        </a>
                    @endif
                    <a href="{{ route('user.tickets.index') }}" class="btn btn-light border text-secondary fw-bold btn-sm px-3 rounded-pill shadow-sm">بازگشت به لیست</a>
                </div>
                <div>
                    @php
                        $user = auth()->user();
                        $isAuthor = ($ticket->user_id === $user->id);
                        $canCloseBtn = false;
                        
                        if ($ticket->company_id !== null) {
                            $canCloseBtn = $isAuthor || $user->level === 'creator';
                        } else {
                            $canCloseBtn = $isAuthor || $ticket->assigned_to === $user->id || $ticket->department_id === $user->department_id || $user->level === 'creator';
                        }
                    @endphp

                    @if($canCloseBtn && $ticket->status != 'closed')
                        <form action="{{ route('user.tickets.close', $ticket) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PUT') <!-- 👈 این خط را اضافه کنید -->
                            <button type="submit" class="btn btn-danger btn-sm px-3 rounded-pill shadow-sm fw-bold" onclick="return confirm('آیا از بستن این تیکت اطمینان دارید؟')">
                                🔒 بستن تیکت
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm rounded-4">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger border-0 shadow-sm rounded-4">{{ session('error') }}</div>
            @endif

            <!-- کارت اطلاعات اصلی تیکت -->
            <div class="card shadow border-0 rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                    <div class="d-flex gap-2">
                        <span class="badge {{ $ticket->status_badge_attribute }} px-3 py-2 rounded-pill shadow-xs">{{ $ticket->status_label }}</span>
                        <span class="badge {{ $ticket->priority_badge_class_attribute }} px-3 py-2 rounded-pill shadow-xs">{{ $ticket->priority_label }}</span>
                    </div>
                    <span class="text-muted small fw-semibold">دسته‌بندی: {{ $ticket->category->name ?? '-' }}</span>
                </div>
                <div class="card-body p-4">
                    <!-- شرکت مبدأ، مقصد اولیه و شخص مسئول فعلی -->
                    <div class="row bg-body-tertiary p-3 rounded-4 mb-4 border g-3">
                        <div class="col-md-4">
                            <span class="text-muted d-block small fw-bold mb-1">ثبت‌کننده (مبدأ):</span>
                            <strong class="text-dark">{{ $ticket->company->name ?? 'مشتری آزاد' }}</strong> 
                            <span class="text-muted small">({{ $ticket->user->name }})</span>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted d-block small fw-bold mb-1">دپارتمان و شرکت مقصد:</span>
                            <strong class="text-primary">{{ $ticket->targetCompany->name ?? '-' }}</strong>
                            <span class="text-muted small">({{ $ticket->department->name ?? '-' }})</span>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted d-block small fw-bold mb-1">مسئول فعلی رسیدگی:</span>
                            <span class="badge bg-primary text-white px-3 py-2 rounded-pill shadow-xs">
                                👤 {{ $ticket->assigned->name ?? 'تعیین نشده' }} 
                                <small>({{ optional($ticket->assigned->department)->name }} - {{ optional($ticket->assigned->company)->name }})</small>
                            </span>
                        </div>
                    </div>

                    <!-- متن پیام اصلی تیکت -->
                    <div class="mb-3 p-4 rounded-4 border border-primary border-opacity-25 bg-primary bg-opacity-10 shadow-xs">
                        <h5 class="fw-bold text-dark mt-2 mb-2">{{ $ticket->subject }}</h5>
                        <p class="text-secondary mb-2 text-break lh-base" style="white-space: pre-line;">{{ $ticket->description }}</p>
                        
                        <!-- فایل‌های پیوست تیکت اصلی -->
                        @if($ticket->attachments->count() > 0)
                            <div class="mt-3 pt-3 border-top border-primary border-opacity-10">
                                <span class="text-muted small d-block mb-2 fw-semibold">فایل‌های پیوست:</span>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($ticket->attachments as $attachment)
                                        {{-- 🔒 استفاده از روت امن دانلود به جای asset مستقیم --}}
                                        <a href="{{ route('ticket.attachments.download', $attachment) }}" class="btn btn-white bg-white border border-primary border-opacity-25 text-primary btn-sm rounded-pill px-3 shadow-xs d-flex align-items-center gap-1">
                                            📎 {{ $attachment->file_name }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- تاریخچه گفتگوها -->
            <div class="mb-4">
                <h5 class="fw-bold text-secondary mb-3 px-2">تاریخچه گفتگوها</h5>
                
                @forelse($ticket->comments as $comment)
                    @php
                        $isMe = $comment->user_id == auth()->id();
                    @endphp

                    <div class="card shadow-sm border-0 rounded-4 mb-3 {{ $isMe ? 'ms-md-4 border-start border-primary border-5' : 'me-md-4 border-start border-success border-5' }}">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom border-light">
                                <div>
                                    <strong class="{{ $isMe ? 'text-primary' : 'text-success' }} fw-bold">{{ $comment->user->name ?? 'کاربر' }}</strong>
                                    <span class="badge bg-light text-dark border ms-2 small rounded-pill">
                                        {{ $comment->user->company->name ?? 'مشتری آزاد' }}
                                    </span>
                                </div>
                                <span class="text-muted small">
                                    {{ $comment->created_at->diffForHumans() }} ({{ $comment->created_at->format('Y/m/d H:i') }})
                                </span>
                            </div>

                            <p class="text-secondary mb-3 text-break lh-base" style="white-space: pre-line;">{{ $comment->message }}</p>
                            
                            <!-- فایل‌های پیوست پاسخ -->
                            @if($comment->attachments->count() > 0)
                                <div class="pt-3 border-top border-light">
                                    <span class="text-muted small d-block mb-2 fw-semibold">پیوست‌های پاسخ:</span>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($comment->attachments as $att)
                                            {{-- 🔒 استفاده از روت امن دانلود برای پیوست‌های کامنت --}}
                                            <a href="{{ route('ticket.attachments.download', $att) }}" class="btn btn-light border btn-sm rounded-pill px-3 shadow-xs d-flex align-items-center gap-1 text-dark">
                                                📎 {{ $att->file_name }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4 bg-white rounded-4 shadow-sm border">
                        هنوز پاسخی برای این تیکت ثبت نشده است.
                    </div>
                @endforelse
            </div>

            <!-- فرم ارسال پاسخ جدید و تغییر مسئول (ارجاع گردش کار) -->
            @if($ticket->status != 'closed')
                <div class="card shadow border-0 rounded-4 mt-4 overflow-hidden">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3 text-dark">ارسال پاسخ و ارجاع تیکت</h5>
                        <form action="{{ route('user.tickets.comments.store', $ticket) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">ارجاع به شخص مسئول (اختیاری - تغییر کارشناس رسیدگی)</label>
                                <select name="assigned_to" class="form-select rounded-pill px-3 shadow-xs">
                                    <option value="">-- عدم تغییر مسئول فعلی --</option>
                                    @foreach($assignableUsers as $usr)
                                        <option value="{{ $usr->id }}" {{ $ticket->assigned_to == $usr->id ? 'selected' : '' }}>
                                            {{ $usr->name }} | {{ $usr->department->name }} | {{ $usr->company->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted ms-2 mt-1 d-block">می‌توانید تیکت را به کارشناس دیگری در شرکت مادر ارجاع دهید.</small>
                            </div>

                            <div class="mb-3">
                                <textarea name="body" rows="4" class="form-control rounded-4 p-3 shadow-xs @error('body') is-invalid @enderror" required placeholder="پاسخ خود را بنویسید...">{{ old('body') }}</textarea>
                                @error('body') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold small text-muted">فایل‌های پیوست (چندگانه)</label>
                                <input type="file" name="attachments[]" class="form-control rounded-pill px-3 shadow-xs" multiple>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary px-5 py-2 rounded-pill fw-bold shadow-sm">ارسال پاسخ و ارجاع</button>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <div class="alert alert-warning text-center border-0 shadow-sm rounded-4 py-3 fw-semibold">این تیکت بسته شده است و امکان ارسال پاسخ وجود ندارد.</div>
            @endif
        </div>
    </div>
</div>
@endsection