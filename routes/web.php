<?php

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

    // داشبورد کاربر عادی
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

// بارگذاری فایل‌های روت مجزا
require __DIR__.'/user.php';
require __DIR__.'/panel.php';

// تغییر روت اصلی برای نمایش صفحه انتخاب
Route::get('/', function () {
    return view('welcome');
});