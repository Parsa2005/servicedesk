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
        
        Route::get('/tickets', [UserTicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/create', [UserTicketController::class, 'create'])->name('tickets.create');
        Route::post('/tickets', [UserTicketController::class, 'store'])->name('tickets.store');
        
        Route::get('/tickets/{ticket}', [UserTicketController::class, 'show'])->name('tickets.show');
       
        Route::get('/tickets/{ticket}/edit', [UserTicketController::class, 'edit'])->name('tickets.edit');
        Route::put('/tickets/{ticket}', [UserTicketController::class, 'update'])->name('tickets.update');
        
        
        Route::post('/tickets/{ticket}/comments', [UserTicketController::class, 'storeComment'])->name('tickets.comments.store');
        
        Route::put('/tickets/{ticket}/close', [UserTicketController::class, 'close'])->name('tickets.close');
    });