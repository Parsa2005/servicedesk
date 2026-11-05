@extends('layouts.admin')

@section('title', 'مدیریت تیکت‌ها')

@section('content')
<div class="space-y-6" dir="rtl">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex justify-between items-center">
        <h1 class="text-lg font-bold text-slate-800">مدیریت تیکت‌های پشتیبانی</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-xs uppercase border-b border-slate-200">
                        <th class="p-4">کد پیگیری</th>
                        <th class="p-4">کاربر</th>
                        <th class="p-4">موضوع</th>
                        <th class="p-4">دسته‌بندی</th>
                        <th class="p-4">اولویت</th>
                        <th class="p-4">وضعیت</th>
                        <th class="p-4">پشتیبان مسئول</th>
                        <th class="p-4 text-center">عملیات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="p-4 font-mono font-bold text-blue-600">{{ $ticket->tracking_code }}</td>
                            <td class="p-4 font-semibold text-slate-900">{{ $ticket->user->name ?? '-' }}</td>
                            <td class="p-4 font-medium">{{ $ticket->subject }}</td>
                            <td class="p-4 text-slate-500">{{ $ticket->category->name ?? '-' }}</td>
                            <td class="p-4">
                                @php
                                    $pClass = match($ticket->priority) {
                                        'critical' => 'bg-red-100 text-red-700',
                                        'high' => 'bg-amber-100 text-amber-700',
                                        'normal' => 'bg-blue-100 text-blue-700',
                                        default => 'bg-slate-100 text-slate-600'
                                    };
                                    $pName = match($ticket->priority) {
                                        'critical' => 'بحرانی', 'high' => 'بالا', 'normal' => 'عادی', default => 'کم'
                                    };
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $pClass }}">{{ $pName }}</span>
                            </td>
                            <td class="p-4">
                                @php
                                    $sClass = match($ticket->status) {
                                        'open' => 'bg-amber-100 text-amber-700',
                                        'pending' => 'bg-slate-100 text-slate-700',
                                        'in_progress' => 'bg-blue-100 text-blue-700',
                                        'answered' => 'bg-emerald-100 text-emerald-700',
                                        'closed' => 'bg-zinc-800 text-zinc-100',
                                        default => 'bg-slate-100 text-slate-700', // 👈 برای جلوگیری از خطاهای احتمالی بعدی
                                    };
                                    $sName = match($ticket->status) {
                                        'open' => 'باز',
                                        'pending' => 'در انتظار بررسی',
                                        'in_progress' => 'در حال بررسی',
                                        'answered' => 'پاسخ داده شده',
                                        'closed' => 'بسته شده',
                                        default => 'نامشخص', // 👈 برای جلوگیری از خطاهای احتمالی بعدی
                                    };
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $sClass }}">{{ $sName }}</span>
                            </td>
                            <td class="p-4 text-slate-600 text-xs">{{ $ticket->assignedAdmin->name ?? 'تعیین نشده' }}</td>
                            <td class="p-4 text-center">
                                <a href="{{ route('panel.tickets.show', $ticket->id) }}" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-medium transition-colors">بررسی و پاسخ</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-400">تیکتی یافت نشد.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">
            {{ $tickets->links() }}
        </div>
    </div>
</div>
@endsection