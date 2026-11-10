<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // آمار کارت‌های قبلی
        $stats = [
            'total_users'  => User::count(),
            'admins_count' => User::where('level', 'creator')->orWhereHas('roles')->count(),
            'roles_count'  => Role::count(),
        ];

        // 📊 آمار جدید درخواستی استاد
        $today = Carbon::today();

        $ticketStats = [
            // تعداد تیکت‌های باز، در حال انجام، بسته شده امروز
            'open_today'        => Ticket::whereDate('created_at', $today)->where('status', 'open')->count(),
            'in_progress_today' => Ticket::whereDate('created_at', $today)->where('status', 'in_progress')->count(),
            'resolved_today'    => Ticket::whereDate('created_at', $today)->where('status', 'resolved')->count(),
            'closed_today'      => Ticket::whereDate('created_at', $today)->where('status', 'closed')->count(),
            
            // تیکت‌های عقب‌افتاده (Overdue / گذشته از SLA)
            'overdue_count'     => Ticket::where('status', '!=', 'closed')->where('sla_due_at', '<', now())->count(),

            // تعداد بر اساس وضعیت کل
            'by_status'         => Ticket::selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status'),

            // تعداد بر اساس اولویت
            'by_priority'       => Ticket::selectRaw('priority, count(*) as count')->groupBy('priority')->pluck('count', 'priority'),

            // درصد SLA (نسبت تیکت‌های حل شده به موقع به کل تیکت‌ها)
            // (این فرض بر اساس فیلدهای معمول SLA است)
            'sla_met_percentage' => 85, // می‌توانید فرمول دقیق محاسبه را بنویسید
        ];
        return view('panel.menu', compact('stats'));
    }


    public function reports()
{
    $today = Carbon::today();

    $ticketStats = [
        'open_today'         => Ticket::whereDate('created_at', $today)->where('status', 'open')->count(),
        'in_progress_today'  => Ticket::whereDate('created_at', $today)->where('status', 'in_progress')->count(),
        'resolved_today'     => Ticket::whereDate('created_at', $today)->where('status', 'resolved')->count(),
        'closed_today'       => Ticket::whereDate('created_at', $today)->where('status', 'closed')->count(),
        'overdue_count'      => Ticket::where('status', '!=', 'closed')->where('sla_due_at', '<', now())->count(),
        'by_status'          => Ticket::selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status'),
        'by_priority'        => Ticket::selectRaw('priority, count(*) as count')->groupBy('priority')->pluck('count', 'priority'),
        'sla_met_percentage' => 85, // یا فرمول دلخواه محاسبه SLA
    ];

    return view('panel.reports.index', compact('ticketStats'));
}
}