@extends('layouts.admin')

@section('title', 'ویرایش دسترسی‌های کاربر')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" dir="rtl">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 md:p-8">
        <div class="flex justify-between items-center pb-4 mb-6 border-b border-slate-200">
            <div>
                <h1 class="text-lg font-bold text-slate-800">مدیریت دسترسی و نقش‌ها</h1>
                <p class="text-xs text-slate-500 mt-1">کاربر: <strong class="text-slate-700">{{ $user->name }}</strong> ({{ $user->email }})</p>
            </div>
            <a href="{{ route('panel.users.index') }}" class="text-sm text-slate-500 hover:text-slate-700">بازگشت</a>
        </div>

        <form action="{{ route('panel.users.update', $user->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- انتخاب سطح دسترسی اصلی (فقط کاربر عادی و ادمین) -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">سطح دسترسی اصلی (Level) <span class="text-red-500">*</span></label>
                <select name="level" id="user-level-select" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/25 text-sm bg-white">
                    <option value="user" {{ old('level', $user->level) === 'user' ? 'selected' : '' }}>کاربر عادی (User)</option>
                    <option value="admin" {{ old('level', $user->level) === 'admin' ? 'selected' : '' }}>مدیر / ادمین (Admin)</option>
                </select>
                @error('level') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- انتخاب نقش‌های چندگانه (Roles) - پویا بر اساس سطح -->
            <div id="roles-section" class="{{ old('level', $user->level) === 'admin' ? '' : 'hidden' }}">
                <label class="block text-sm font-medium text-slate-700 mb-2">تخصیص نقش‌های سیستمی (چند انتخابی)</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 p-4 bg-slate-50 rounded-xl border border-slate-200">
                    @forelse($roles as $role)
                        <label class="flex items-center gap-3 p-3 bg-white rounded-lg border border-slate-200 cursor-pointer hover:border-blue-300 transition-colors">
                            <input type="checkbox" name="roles[]" value="{{ $role->id }}" 
                                {{ in_array($role->id, old('roles', $user->roles->pluck('id')->toArray())) ? 'checked' : '' }}
                                class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 w-4 h-4">
                            <div>
                                <span class="text-sm font-bold text-slate-800 block">{{ $role->name }}</span>
                                <span class="text-xs text-slate-400 font-mono">{{ $role->slug ?? '' }}</span>
                            </div>
                        </label>
                    @empty
                        <p class="text-xs text-slate-400 col-span-2 text-center py-4">هیچ نقشی در سیستم تعریف نشده است.</p>
                    @endforelse
                </div>
                @error('roles') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition-colors">ذخیره تغییرات دسترسی</button>
            </div>
        </form>
    </div>
</div>

<!-- اسکریپت نمایش/مخفی کردن بخش نقش‌ها بر اساس انتخاب سطح -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const levelSelect = document.getElementById('user-level-select');
        const rolesSection = document.getElementById('roles-section');

        levelSelect.addEventListener('change', function () {
            if (this.value === 'admin') {
                rolesSection.classList.remove('hidden');
            } else {
                rolesSection.classList.add('hidden');
            }
        });
    });
</script>
@endsection