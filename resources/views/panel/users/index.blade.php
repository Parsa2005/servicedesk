@extends('layouts.admin')

@section('title', 'مدیریت کاربران و دسترسی‌ها')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" dir="rtl">
    
    <!-- هدر صفحه و جستجو -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col md:flex-row justify-between items-center gap-4">
        <div>
            <h1 class="text-lg font-bold text-slate-800">مدیریت کاربران سیستم</h1>
            <p class="text-xs text-slate-500 mt-1">مشاهده لیست کاربران، تغییر سطح دسترسی و تخصیص نقش‌های ادمین.</p>
        </div>

        <!-- فرم جستجو -->
        <form action="{{ route('panel.users.index') }}" method="GET" class="w-full md:w-auto flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="جستجو بر اساس نام یا ایمیل..." class="px-4 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/25 w-full md:w-64">
            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-sm transition-colors">جستجو</button>
            @if(request('search'))
                <a href="{{ route('panel.users.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm transition-colors">حذف فیلتر</a>
            @endif
        </form>
    </div>

    <!-- پیام موفقیت -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <!-- جدول لیست کاربران -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-600 uppercase">
                        <th class="p-4">کاربر</th>
                        <th class="p-4">ایمیل</th>
                        <th class="p-4">سطح دسترسی (Level)</th>
                        <th class="p-4">نقش‌های اختصاص‌یافته</th>
                        <th class="p-4 text-center">عملیات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="p-4 font-bold text-slate-900">{{ $user->name }}</td>
                            <td class="p-4 text-slate-500 font-mono text-xs">{{ $user->email }}</td>
                            <td class="p-4">
                                @if($user->level === 'creator')
                                    <span class="px-3 py-1 bg-purple-50 text-purple-700 border border-purple-200 rounded-full text-xs font-bold">سازنده (Creator)</span>
                                @elseif($user->level === 'admin')
                                    <span class="px-3 py-1 bg-blue-50 text-blue-700 border border-blue-200 rounded-full text-xs font-bold">مدیر (Admin)</span>
                                @else
                                    <span class="px-3 py-1 bg-slate-100 text-slate-600 border border-slate-200 rounded-full text-xs">کاربر عادی</span>
                                @endif
                            </td>
                            <td class="p-4">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($user->roles as $role)
                                        <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded text-xs border border-slate-200">{{ $role->name }}</span>
                                    @empty
                                        <span class="text-xs text-slate-400">بدون نقش</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="p-4 text-center">
                                @if($user->level !== 'creator')
                                    <a href="{{ route('panel.users.edit', $user->id) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg text-xs font-medium transition-colors">
                                        ویرایش دسترسی
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400 italic">غیرقابل ویرایش</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-400 text-sm">هیچ کاربری یافت نشد.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- صفحه‌بندی -->
        @if($users->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection