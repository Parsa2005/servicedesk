@extends('layouts.app')

@section('content')
<div class="container my-4" dir="rtl">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0 fw-bold text-dark">ایجاد دسته‌بندی جدید</h5>
                    <a href="{{ route('panel.categories.index') }}" class="btn btn-outline-secondary btn-sm">بازگشت</a>
                </div>
                <div class="card-body">
                    <form action="{{ route('panel.categories.store') }}" method="POST">
                        @csrf

                        @if(auth()->user()->level === 'creator')
                            <div class="mb-3">
                                <label for="company_id" class="form-label fw-bold">انتخاب شرکت <span class="text-danger">*</span></label>
                                <select name="company_id" id="company_id" class="form-select @error('company_id') is-invalid @enderror" required>
                                    <option value="">انتخاب کنید...</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                            {{ $company->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('company_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @endif

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-bold">نام دسته‌بندی <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="مثلاً: مشکلات شبکه" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="slug" class="form-label fw-bold">شناسه لاتین (Slug) <span class="text-muted small">(اختیاری)</span></label>
                                <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" placeholder="network-issues">
                                @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label fw-bold">توضیحات (اختیاری)</label>
                            <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror" placeholder="توضیح مختصر در مورد این دسته‌بندی...">{{ old('description') }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-4">ذخیره دسته‌بندی</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection