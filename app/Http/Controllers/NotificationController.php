<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // ۱. نمایش صفحه کامل تمام اعلان‌ها (تاریخچه)
    public function index()
    {
        $user = auth()->user();
        $notifications = $user->notifications()->paginate(15);
        
        return view('user.notifications.index', compact('notifications'));
    }

    // ۲. علامت‌گذاری همه اعلان‌ها به عنوان خوانده‌شده
    public function readAll()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'تمام اعلان‌ها به عنوان خوانده‌شده علامت‌گذاری شدند.');
    }
}