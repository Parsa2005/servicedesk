@extends('layouts.admin')

@section('title', 'ویرایش دپارتمان')

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6">
    <div class="flex justify-between items-center mb-6 border-b border-slate-100 pb-4">
        <h2 class="text-xl font-bold text-slate-800">ویرایش دپارتمان: {{ $department->name }}</h2>
        <a href="{{ route('panel.departments.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← بازگشت به لیست</a>
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

    <form action="{{ route('panel.departments.update', $department) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">انتخاب شرکت</label>
            <select name="company_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:border-blue-500 text-sm" required>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}" {{ old('company_id', $department->company_id) == $company->id ? 'selected' : '' }}>
                        {{ $company->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">نام دپارتمان</label>
            <input type="text" name="name" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:border-blue-500 text-sm" value="{{ old('name', $department->name) }}" required>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-slate-700 mb-1">اسلاگ (URL)</label>
            <input type="text" name="slug" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:border-blue-500 text-sm bg-slate-50" value="{{ old('slug', $department->slug) }}">
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors text-sm shadow-sm">ذخیره تغییرات</button>
            <a href="{{ route('panel.departments.index') }}" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl transition-colors text-sm">انصراف</a>
        </div>
    </form>
</div>
@endsection