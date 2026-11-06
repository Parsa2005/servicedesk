@extends('layouts.admin')

@section('title', 'افزودن شرکت جدید')

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6" dir="rtl">
    <div class="flex justify-between items-center mb-6 border-b border-slate-100 pb-4">
        <h2 class="text-xl font-bold text-slate-800">افزودن شرکت جدید</h2>
        <a href="{{ route('panel.companies.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← بازگشت به لیست</a>
    </div>

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('panel.companies.store') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">نام شرکت <span class="text-red-500">*</span></label>
            <input type="text" name="name" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:border-blue-500 text-sm" value="{{ old('name') }}" required>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">اسلاگ (URL - اختیاری)</label>
            <input type="text" name="slug" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:border-blue-500 text-sm bg-slate-50" value="{{ old('slug') }}" placeholder="اگر خالی بگذارید خودکار ساخته می‌شود">
        </div>

        <!-- 👈 فیلد انتخاب شرکت مادر (ساختار هرمی) -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">شرکت مادر (هرم سازمانی)</label>
            <select name="parent_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:border-blue-500 text-sm bg-white">
                <option value="">-- بدون شرکت مادر (به عنوان شرکت مستقل / مادر اصلی) --</option>
                @foreach($companies as $comp)
                    <option value="{{ $comp->id }}" {{ old('parent_id') == $comp->id ? 'selected' : '' }}>
                        {{ $comp->name }}
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-slate-500 mt-1">اگر این شرکت زیرمجموعه شرکت دیگری است، والد آن را انتخاب کنید.</p>
        </div>

        <div class="mb-6 flex items-center gap-3">
            <input type="checkbox" name="is_active" id="is_active" value="1" class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500" {{ old('is_active', true) ? 'checked' : '' }}>
            <label for="is_active" class="text-sm font-medium text-slate-700 cursor-pointer">شرکت فعال باشد</label>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors text-sm shadow-sm">ذخیره شرکت</button>
            <a href="{{ route('panel.companies.index') }}" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl transition-colors text-sm">انصراف</a>
        </div>
    </form>
</div>
@endsection