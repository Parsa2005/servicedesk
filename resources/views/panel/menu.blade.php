@extends('layouts.admin')

@section('title', 'داشبورد اصلی')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- کارت تعداد کل کاربران -->
    <div class="bg-gradient-to-br from-blue-600 to-blue-700 text-white rounded-2xl p-6 shadow-sm border border-blue-500/20">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-blue-100 text-sm font-medium mb-1">تعداد کل کاربران</p>
                <h2 class="text-3xl font-extrabold tracking-tight">{{ $stats['total_users'] ?? 0 }}</h2>
            </div>
            <span class="p-3 bg-white/10 rounded-xl text-xl backdrop-blur-sm">👥</span>
        </div>
    </div>

    <!-- کارت تعداد مدیران -->
    <div class="bg-gradient-to-br from-emerald-600 to-emerald-700 text-white rounded-2xl p-6 shadow-sm border border-emerald-500/20">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-emerald-100 text-sm font-medium mb-1">مدیران و ادمین‌ها</p>
                <h2 class="text-3xl font-extrabold tracking-tight">{{ $stats['admins_count'] ?? 0 }}</h2>
            </div>
            <span class="p-3 bg-white/10 rounded-xl text-xl backdrop-blur-sm">🛡️</span>
        </div>
    </div>

    <!-- کارت تعداد نقش‌ها -->
    <div class="bg-gradient-to-br from-amber-500 to-amber-600 text-white rounded-2xl p-6 shadow-sm border border-amber-400/20">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-amber-100 text-sm font-medium mb-1">نقش‌های تعریف‌شده</p>
                <h2 class="text-3xl font-extrabold tracking-tight">{{ $stats['roles_count'] ?? 0 }}</h2>
            </div>
            <span class="p-3 bg-white/10 rounded-xl text-xl backdrop-blur-sm">🔑</span>
        </div>
    </div>
</div>

<!-- کارت‌های دسترسی سریع به ماژول‌ها -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- مدیریت شرکت‌ها -->
    <a href="{{ route('panel.companies.index') }}" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 hover:border-blue-500/50 hover:shadow-md transition-all group">
        <div class="flex items-center gap-4">
            <div class="p-3 bg-blue-50 text-blue-600 rounded-xl text-xl group-hover:bg-blue-600 group-hover:text-white transition-colors">🏢</div>
            <div>
                <h4 class="font-bold text-slate-800">مدیریت شرکت‌ها</h4>
                <p class="text-slate-500 text-xs mt-1">افزودن و ویرایش شرکت‌ها</p>
            </div>
        </div>
    </a>

    <!-- مدیریت دپارتمان‌ها -->
    <a href="{{ route('panel.departments.index') }}" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 hover:border-emerald-500/50 hover:shadow-md transition-all group">
        <div class="flex items-center gap-4">
            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl text-xl group-hover:bg-emerald-600 group-hover:text-white transition-colors">🏛️</div>
            <div>
                <h4 class="font-bold text-slate-800">مدیریت دپارتمان‌ها</h4>
                <p class="text-slate-500 text-xs mt-1">واحدهای سازمانی شرکت‌ها</p>
            </div>
        </div>
    </a>

    <!-- مدیریت تیکت‌ها -->
    <a href="{{ route('panel.tickets.index') }}" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 hover:border-purple-500/50 hover:shadow-md transition-all group">
        <div class="flex items-center gap-4">
            <div class="p-3 bg-purple-50 text-purple-600 rounded-xl text-xl group-hover:bg-purple-600 group-hover:text-white transition-colors">🎫</div>
            <div>
                <h4 class="font-bold text-slate-800">مدیریت تیکت‌ها</h4>
                <p class="text-slate-500 text-xs mt-1">مشاهده و پاسخ به تیکت‌ها</p>
            </div>
        </div>
    </a>

    <a href="{{ route('panel.categories.index') }}" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 hover:border-purple-500/50 hover:shadow-md transition-all group">
        <div class="flex items-center gap-4">
            <div class="p-3 bg-purple-50 text-purple-600 rounded-xl text-xl group-hover:bg-purple-600 group-hover:text-white transition-colors">🎫</div>
            <div>
                <h4 class="font-bold text-slate-800">مدیریت دسته بندی ها</h4>
            </div>
        </div>
    </a>

    <!-- مدیریت کاربران -->
    <a href="{{ route('panel.users.index') }}" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 hover:border-indigo-500/50 hover:shadow-md transition-all group">
        <div class="flex items-center gap-4">
            <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl text-xl group-hover:bg-indigo-600 group-hover:text-white transition-colors">👥</div>
            <div>
                <h4 class="font-bold text-slate-800">مدیریت کاربران</h4>
                <p class="text-slate-500 text-xs mt-1">سطوح دسترسی و کاربران</p>
            </div>
        </div>
    </a>

    <a href="{{ route('panel.membership.index') }}" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 hover:border-indigo-500/50 hover:shadow-md transition-all group">
        <div class="flex items-center gap-4">
            <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl text-xl group-hover:bg-indigo-600 group-hover:text-white transition-colors">🏭</div>
            <div>
                <h4 class="font-bold text-slate-800"> مدیریت اعضا</h4>
                <p class="text-slate-500 text-xs mt-1"> عضویت شرکت و دپارتمان</p>
            </div>
        </div>
    </a>
</div>

<!-- بخش راهنما و دسترسی‌های متن‌محور -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6">
    <h5 class="text-base font-bold text-slate-800 mb-2">راهنمای دسترسی سریع</h5>
    <p class="text-slate-500 text-sm mb-4">از طریق کارت‌های بالا یا لینک‌های زیر می‌توانید بخش‌های مختلف سیستم را مدیریت کنید:</p>
    
    <ul class="space-y-3 text-sm text-slate-700">
        <li class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
            <strong><a href="{{ route('panel.roles.index') }}" class="text-blue-600 hover:underline">مدیریت نقش‌ها:</a></strong> تعریف نقش‌های جدید و انتصاب مجوزهای دسترسی.
        </li>
        <li class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
            <strong><a href="{{ route('panel.users.index') }}" class="text-blue-600 hover:underline">مدیریت کاربران:</a></strong> ارتقای کاربران عادی به ادمین و متصل کردن نقش‌ها به آنها.
        </li>
        <li class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
            <strong><a href="{{ route('panel.companies.index') }}" class="text-blue-600 hover:underline">مدیریت شرکت‌ها و دپارتمان‌ها:</a></strong> تنظیم ساختار چندمشتری (Multi-Tenancy) و واحدهای سازمانی.
        </li>
        <li class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
            <strong><a href="{{ route('panel.tickets.index') }}" class="text-blue-600 hover:underline">مدیریت تیکت‌ها:</a></strong> پیگیری، ارجاع و پاسخ به درخواست‌های پشتیبانی کاربران.
        </li>
    </ul>
</div>
@endsection