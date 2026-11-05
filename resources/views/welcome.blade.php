@extends('layouts.app')

@section('title', 'صفحه اصلی - سیستم تیکتینگ')

@section('content')
<div class="max-w-md w-full bg-white rounded-2xl shadow-sm border border-slate-200 p-8 text-center my-8">
    <!-- آیکون بزرگ -->
    <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl mx-auto flex items-center justify-center text-2xl font-bold mb-4 shadow-sm">
        
    </div>

    <h1 class="text-2xl font-extrabold text-slate-900 mb-2">به سامانه پشتیبانی خوش آمدید</h1>
    <p class="text-slate-500 text-sm mb-8 leading-relaxed">
        برای پیگیری درخواست‌ها، ثبت تیکت جدید یا ارتباط با پشتیبانان، لطفاً وارد حساب خود شوید و یا ثبت‌نام کنید.
    </p>

    @auth
        <a href="{{ auth()->user()->level === 'user' ? route('dashboard') : route('panel.menu') }}" class="block w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition-colors shadow-sm">
            مشاهده داشبورد اختصاصی
        </a>
    @else
        <div class="flex gap-3">
            <a href="{{ route('login') }}" class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition-colors shadow-sm text-center">
                ورود به سیستم
            </a>
            <a href="{{ route('register') }}" class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-semibold transition-colors text-center">
                ثبت‌نام سریع
            </a>
        </div>
    @endauth
</div>
@endsection