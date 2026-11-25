@extends('layouts.admin')

@section('title', 'گزارش‌ها و آمارهای مدیریتی')

@section('content')
<div class="mb-6 flex flex-wrap justify-between items-center gap-3">
    <div>
        <h2 class="text-xl font-bold text-slate-800">گزارش‌ها و آمارهای سیستم</h2>
        <p class="text-slate-500 text-xs mt-1">مشاهده وضعیت کلی تیکت‌ها، عملکرد واحدها و شاخص‌های SLA</p>
    </div>
    <a href="{{ route('panel.menu') }}" class="bg-white border border-slate-200 text-slate-700 px-4 py-2 rounded-xl text-sm font-semibold hover:bg-slate-50 transition-all">
        ← بازگشت به داشبورد
    </a>
</div>

<!-- فیلتر شرکت و وضعیت -->
<form method="GET" action="{{ route('panel.reports.index') }}" class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs mb-6 flex flex-wrap items-center gap-3">
    <select name="company_id" class="border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-700 bg-slate-50 focus:outline-none">
        <option value="">همه شرکت‌های مجاز</option>
        @foreach($companies ?? [] as $company)
            <option value="{{ $company->id }}" {{ (string)($filters['company_id'] ?? '') === (string)$company->id ? 'selected' : '' }}>
                {{ $company->name }}
            </option>
        @endforeach
    </select>

    <select name="status" class="border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-700 bg-slate-50 focus:outline-none">
        <option value="">همه وضعیت‌ها</option>
        <option value="open" {{ ($filters['status'] ?? '') === 'open' ? 'selected' : '' }}>باز (Open)</option>
        <option value="in_progress" {{ ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' }}>در حال بررسی (In Progress)</option>
        <option value="resolved" {{ ($filters['status'] ?? '') === 'resolved' ? 'selected' : '' }}>حل شده (Resolved)</option>
        <option value="closed" {{ ($filters['status'] ?? '') === 'closed' ? 'selected' : '' }}>بسته شده (Closed)</option>
    </select>

    <button type="submit" class="bg-slate-800 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-slate-700 transition-all">
        اعمال فیلتر
    </button>

    @if(!empty($filters['company_id']) || !empty($filters['status']))
        <a href="{{ route('panel.reports.index') }}" class="text-xs text-slate-500 hover:text-slate-700 underline">
            پاک‌سازی فیلتر
        </a>
    @endif
</form>

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

<!-- توزیع وضعیت کل تیکت‌ها و اولویت‌ها -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
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

<!-- جدول تفصیلی صفحه‌بندی‌شده -->
@if(isset($reportTickets))
<div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
        <h5 class="font-bold text-slate-800 text-sm">جزئیات تیکت‌ها در محدوده مجاز</h5>
        <span class="text-xs text-slate-400">نمایش {{ $reportTickets->count() }} از {{ $reportTickets->total() }} مورد</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-right text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-500 text-xs border-b border-slate-100">
                <tr>
                    <th class="py-3 px-4">#</th>
                    <th class="py-3 px-4">موضوع</th>
                    <th class="py-3 px-4">شرکت</th>
                    <th class="py-3 px-4">وضعیت</th>
                    <th class="py-3 px-4">اولویت</th>
                    <th class="py-3 px-4">مهلت SLA</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($reportTickets as $item)
                    <tr class="hover:bg-slate-50/70">
                        <td class="py-3 px-4 font-semibold text-slate-800">#{{ $item->id }}</td>
                        <td class="py-3 px-4">{{ $item->subject }}</td>
                        <td class="py-3 px-4">{{ optional($item->company)->name ?? '-' }}</td>
                        <td class="py-3 px-4">{{ $item->status }}</td>
                        <td class="py-3 px-4">{{ $item->priority }}</td>
                        <td class="py-3 px-4 text-xs text-slate-500">
                            {{ $item->sla_due_at ? \Carbon\Carbon::parse($item->sla_due_at)->format('Y/m/d H:i') : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-slate-400">تیکتی در این محدوده یافت نشد.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t border-slate-100">
        {{ $reportTickets->links() }}
    </div>
</div>
@endif
@endsection