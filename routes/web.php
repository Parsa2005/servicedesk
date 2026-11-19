<?php

use App\Http\Controllers\Panel\AttachmentController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;

// --------------------------------------------------------------------------
// مسیرهای مهمان (Guest)
// --------------------------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
});

// --------------------------------------------------------------------------
// مسیرهای کاربران لاگین شده (Auth)
// --------------------------------------------------------------------------
Route::middleware('auth')->group(function () {
    Route::post('/logout', LogoutController::class)->name('logout');

    Route::get('/ticket-attachments/{attachment}/download', [AttachmentController::class, 'download'])
        ->name('ticket.attachments.download');

    // داشبورد کاربر عادی
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');


    Route::get('/notifications', [NotificationController::class, 'index'])->name('user.notifications.index');
    
    Route::get('/notifications/read-all', [NotificationController::class, 'readAll'])->name('user.notifications.readAll');
});

// بارگذاری فایل‌های روت مجزا
require __DIR__.'/user.php';
require __DIR__.'/panel.php';

// تغییر روت اصلی برای نمایش صفحه انتخاب
Route::get('/', function () {
    return view('welcome');
});