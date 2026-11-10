@extends('layouts.admin')

@section('title', 'گزارش‌ها و آمارهای مدیریتی')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h2 class="text-xl font-bold text-slate-800">گزارش‌ها و آمارهای سیستم</h2>
        <p class="text-slate-500 text-xs mt-1">مشاهده وضعیت کلی تیکت‌ها، عملکرد واحدها و شاخص‌های SLA</p>
    </div>
    <a href="{{ route('panel.menu') }}" class="bg-white border border-slate-200 text-slate-700 px-4 py-2 rounded-xl text-sm font-semibold hover:bg-slate-50 transition-all">
        ← بازگشت به داشبورد
    </a>
</div>

<!-- کارت‌های آماری تیکت‌های امروز و SLA -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <span class="text-slate-400 text-xs font-semibold">تیکت‌های باز امروز</span>
        <h4 class="text-3xl font-black text-slate-800 mt-1">{{ $ticketStats['open_today'] ?? 0 }}</h4>
    </div>
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <span class="text-slate-400 text-xs font-semibold">در حال انجام امروز</span>
        <h4 class="text-3xl font-black text-blue-600 mt-1">{{ $ticketStats['in_progress_today'] ?? 0 }}</h4>
    </div>
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <span class="text-slate-400 text-xs font-semibold">عقب‌افتاده (نقض SLA)</span>
        <h4 class="text-3xl font-black text-rose-600 mt-1">{{ $ticketStats['overdue_count'] ?? 0 }}</h4>
    </div>
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <span class="text-slate-400 text-xs font-semibold">درصد موفقیت SLA</span>
        <h4 class="text-3xl font-black text-emerald-600 mt-1">{{ $ticketStats['sla_met_percentage'] ?? 0 }}%</h4>
    </div>
</div>

<!-- جدول اطلاعات تکمیلی و وضعیت‌های کلان -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <h5 class="font-bold text-slate-800 text-sm mb-4 pb-2 border-b border-slate-100">توزیع وضعیت کل تیکت‌ها</h5>
        <div class="space-y-3 text-sm text-slate-600">
            <div class="flex justify-between p-3 bg-slate-50 rounded-xl">
                <span>باز (Open):</span>
                <span class="font-bold text-slate-800">{{ $ticketStats['by_status']['open'] ?? 0 }}</span>
            </div>
            <div class="flex justify-between p-3 bg-slate-50 rounded-xl">
                <span>در حال بررسی (In Progress):</span>
                <span class="font-bold text-blue-600">{{ $ticketStats['by_status']['in_progress'] ?? 0 }}</span>
            </div>
            <div class="flex justify-between p-3 bg-slate-50 rounded-xl">
                <span>حل شده (Resolved):</span>
                <span class="font-bold text-emerald-600">{{ $ticketStats['by_status']['resolved'] ?? 0 }}</span>
            </div>
            <div class="flex justify-between p-3 bg-slate-50 rounded-xl">
                <span>بسته شده (Closed):</span>
                <span class="font-bold text-slate-500">{{ $ticketStats['by_status']['closed'] ?? 0 }}</span>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <h5 class="font-bold text-slate-800 text-sm mb-4 pb-2 border-b border-slate-100">توزیع اولویت تیکت‌ها</h5>
        <div class="space-y-3 text-sm text-slate-600">
            <div class="flex justify-between p-3 bg-slate-50 rounded-xl">
                <span>کم (Low):</span>
                <span class="font-bold text-slate-800">{{ $ticketStats['by_priority']['low'] ?? 0 }}</span>
            </div>
            <div class="flex justify-between p-3 bg-slate-50 rounded-xl">
                <span>عادی (Normal):</span>
                <span class="font-bold text-blue-600">{{ $ticketStats['by_priority']['normal'] ?? 0 }}</span>
            </div>
            <div class="flex justify-between p-3 bg-slate-50 rounded-xl">
                <span>بالا (High):</span>
                <span class="font-bold text-amber-600">{{ $ticketStats['by_priority']['high'] ?? 0 }}</span>
            </div>
            <div class="flex justify-between p-3 bg-slate-50 rounded-xl">
                <span>بحرانی / فوری (Critical):</span>
                <span class="font-bold text-rose-600">{{ $ticketStats['by_priority']['critical'] ?? 0 }}</span>
            </div>
        </div>
    </div>
</div>
@endsection