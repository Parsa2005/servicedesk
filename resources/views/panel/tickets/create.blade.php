@extends('layouts.app')

@section('content')
<div class="container my-4" dir="rtl">
    <div class="card shadow-sm border-0 col-lg-8 mx-auto">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0 fw-bold">ایجاد تیکت پشتیبانی جدید</h5>
            <a href="{{ route('user.tickets.index') }}" class="btn btn-outline-secondary btn-sm">بازگشت</a>
        </div>
        <div class="card-body">
            <form action="{{ route('user.tickets.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="subject" class="form-label fw-bold">موضوع تیکت <span class="text-danger">*</span></label>
                    <input type="text" name="subject" id="subject" class="form-control @error('subject') is-invalid @enderror" value="{{ old('subject') }}" placeholder="عنوان مختصر مشکل یا درخواست">
                    @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="category_id" class="form-label fw-bold">دسته‌بندی <span class="text-danger">*</span></label>
                        <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror">
                            <option value="">انتخاب کنید...</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label for="priority" class="form-label fw-bold">اولویت <span class="text-danger">*</span></label>
                        <select name="priority" id="priority" class="form-select @error('priority') is-invalid @enderror">
                            <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>کم</option>
                            <option value="normal" {{ old('priority', 'normal') == 'normal' ? 'selected' : '' }}>عادی</option>
                            <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>بالا</option>
                            <option value="critical" {{ old('priority') == 'critical' ? 'selected' : '' }}>بحرانی</option>
                        </select>
                        @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="message" class="form-label fw-bold">متن پیام <span class="text-danger">*</span></label>
                    <textarea name="message" id="message" rows="6" class="form-control @error('message') is-invalid @enderror" placeholder="توضیحات کامل درخواست یا مشکل...">{{ old('message') }}</textarea>
                    @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label for="attachment" class="form-label fw-bold">فایل پیوست (اختیاری)</label>
                    <input type="file" name="attachment" id="attachment" class="form-control @error('attachment') is-invalid @enderror">
                    <small class="text-muted">فرمت‌های مجاز: تصاویر، PDF، Zip (حداکثر ۵ مگابایت)</small>
                    @error('attachment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success px-4">ارسال تیکت</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection