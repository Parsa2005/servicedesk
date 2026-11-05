<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'admins_count' => User::whereIn('level', ['admin', 'creator'])->count(),
            'roles_count' => Role::count(),
        ];

        // اصلاح شد: ارسال متغیر stats به فایل resources/views/panel/menu.blade.php
        return view('panel.menu', compact('stats'));
    }
}