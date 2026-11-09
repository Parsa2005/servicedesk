@extends('layouts.app')

@section('content')
<div class="container my-5" dir="rtl">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card shadow border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-white py-4 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <h4 class="m-0 fw-bold text-dark">ویرایش تیکت: #{{ $ticket->id }}</h4>
                    <a href="{{ route('user.tickets.show', $ticket) }}" class="btn btn-light border text-secondary fw-bold btn-sm px-3 rounded-pill shadow-xs">بازگشت</a>
                </div>
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('user.tickets.update', $ticket) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- موضوع تیکت -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small">موضوع تیکت <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control rounded-pill px-4 py-2 shadow-xs @error('subject') is-invalid @enderror" value="{{ old('subject', $ticket->subject) }}" required>
                            @error('subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- انتخاب شرکت و دپارتمان مقصد -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label fw-bold text-dark small">شرکت مقصد <span class="text-danger">*</span></label>
                                <select name="target_company_id" id="target_company_select" class="form-select rounded-pill px-4 py-2 shadow-xs @error('target_company_id') is-invalid @enderror" required>
                                    <option value="">انتخاب شرکت مقصد...</option>
                                    @foreach($allowedCompanies as $comp)
                                        <option value="{{ $comp->id }}" {{ old('target_company_id', $ticket->target_company_id) == $comp->id ? 'selected' : '' }}>
                                            {{ $comp->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('target_company_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">دپارتمان مقصد <span class="text-danger">*</span></label>
                                <select name="department_id" id="department_select" class="form-select rounded-pill px-4 py-2 shadow-xs @error('department_id') is-invalid @enderror" required>
                                    @foreach($targetDepartments as $dept)
                                        <option value="{{ $dept->id }}" data-company="{{ $dept->company_id }}" {{ old('department_id', $ticket->department_id) == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <!-- انتخاب شخص مسئول -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small">ارجاع به شخص مسئول (اختیاری)</label>
                            <select name="assigned_to" id="assigned_user_select" class="form-select rounded-pill px-4 py-2 shadow-xs">
                                <option value="">انتخاب کارشناس / مسئول مربوطه...</option>
                                @foreach($departmentUsers as $usr)
                                    <option value="{{ $usr->id }}" data-department="{{ $usr->department_id }}" {{ old('assigned_to', $ticket->assigned_to) == $usr->id ? 'selected' : '' }}>
                                        {{ $usr->name }} | {{ optional($usr->department)->name }} | {{ optional($usr->company)->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- دسته‌بندی و اولویت -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label fw-bold text-dark small">دسته‌بندی <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select rounded-pill px-4 py-2 shadow-xs @error('category_id') is-invalid @enderror" required>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id', $ticket->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">اولویت درخواست <span class="text-danger">*</span></label>
                                <select name="priority" class="form-select rounded-pill px-4 py-2 shadow-xs @error('priority') is-invalid @enderror" required>
                                    <option value="low" {{ old('priority', $ticket->priority) == 'low' ? 'selected' : '' }}>کم</option>
                                    <option value="normal" {{ old('priority', $ticket->priority) == 'normal' ? 'selected' : '' }}>عادی</option>
                                    <option value="high" {{ old('priority', $ticket->priority) == 'high' ? 'selected' : '' }}>بالا</option>
                                    <option value="critical" {{ old('priority', $ticket->priority) == 'critical' ? 'selected' : '' }}>بحرانی / فوری</option>
                                </select>
                                @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <!-- شرح درخواست -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small">شرح درخواست <span class="text-danger">*</span></label>
                            <textarea name="message" rows="5" class="form-control rounded-4 p-3 shadow-xs @error('message') is-invalid @enderror" required>{{ old('message', $ticket->description) }}</textarea>
                            @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- افزودن فایل‌های پیوست جدید -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small">افزودن پیوست‌های جدید (چندگانه)</label>
                            <input type="file" name="attachments[]" class="form-control rounded-pill px-4 py-2 shadow-xs" multiple>
                        </div>

                        <div class="text-end d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('user.tickets.show', $ticket) }}" class="btn btn-light border rounded-pill px-4 fw-bold">انصراف</a>
                            <button type="submit" class="btn btn-warning text-white rounded-pill px-5 py-2 fw-bold shadow-sm">بروزرسانی تیکت</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection