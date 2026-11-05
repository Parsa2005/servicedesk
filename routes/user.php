<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\TicketController as UserTicketController;

// --------------------------------------------------------------------------
// مسیرهای مدیریت تیکت‌های عمومی (مخصوص کاربران لاگین شده)
// --------------------------------------------------------------------------
Route::middleware(['auth'])
    ->prefix('user')
    ->name('user.')
    ->group(function () {
        
        // لیست و ثبت تیکت جدید
        Route::get('/tickets', [UserTicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/create', [UserTicketController::class, 'create'])->name('tickets.create');
        Route::post('/tickets', [UserTicketController::class, 'store'])->name('tickets.store');
        
        // مشاهده جزئیات تیکت
        Route::get('/tickets/{ticket}', [UserTicketController::class, 'show'])->name('tickets.show');
        
        // ارسال پاسخ به تیکت توسط کاربر
        Route::post('/tickets/{ticket}/reply', [UserTicketController::class, 'reply'])->name('tickets.reply');
        
        // بستن و به اتمام رساندن تیکت توسط خودِ کاربر
        Route::put('/tickets/{ticket}/close', [UserTicketController::class, 'close'])->name('tickets.close');
    });