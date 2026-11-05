<?php

use App\Http\Controllers\Panel\TicketCategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Panel\CompanyController;
use App\Http\Controllers\Panel\DepartmentController;
use App\Http\Controllers\Panel\MembershipController;
use App\Http\Controllers\Panel\DashboardController;
use App\Http\Controllers\Panel\AdminController;
use App\Http\Controllers\Panel\RoleController;
use App\Http\Controllers\Panel\TicketController as AdminTicketController;

// --------------------------------------------------------------------------
// مسیرهای پنل مدیریت (مخصوص ادمین و کریتور)
// --------------------------------------------------------------------------
Route::middleware(['auth', 'level:creator,admin'])
    ->prefix('panel')
    ->as('panel.')
    ->group(function () {

        // داشبورد اصلی مدیریت
        Route::get('/menu', [DashboardController::class, 'index'])->name('menu');

        // مدیریت نقش‌ها (CRUD)
        Route::resource('roles', RoleController::class)->except(['show']);

        // مدیریت کاربران و ارتقاء سطح
        Route::get('/users', [AdminController::class, 'index'])->name('users.index');
        Route::get('/users/{user}/edit', [AdminController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'update'])->name('users.update');

        // مدیریت تیکت‌ها در پنل ادمین
        Route::get('/tickets', [AdminTicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/{ticket}', [AdminTicketController::class, 'show'])->name('tickets.show');
        Route::post('/tickets/{ticket}/reply', [AdminTicketController::class, 'reply'])->name('tickets.reply');
        Route::patch('/tickets/{ticket}/status', [AdminTicketController::class, 'status'])->name('tickets.status'); // 👈 برای تغییر وضعیت سریع
        Route::put('/tickets/{ticket}/close', [AdminTicketController::class, 'close'])->name('tickets.close');   // 👈 برای بستن تیکت

        // مدیریت شرکت‌ها و دپارتمان‌ها
        Route::resource('companies', CompanyController::class)->except(['show', 'destroy']);
        Route::resource('departments', DepartmentController::class)->except(['show', 'destroy']);

        // مدیریت عضویت‌های سازمانی
        Route::get('/membership', [MembershipController::class, 'index'])->name('membership.index');
        Route::get('/membership/{user}/edit', [MembershipController::class, 'edit'])->name('membership.edit');
        Route::put('/membership/{user}', [MembershipController::class, 'update'])->name('membership.update');


        Route::resource('categories', TicketCategoryController::class)->except(['show']);
    });