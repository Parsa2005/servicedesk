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

       
        Route::get('/menu', [DashboardController::class, 'index'])->name('menu');

      
        Route::resource('roles', RoleController::class)->except(['show']);

     
        Route::get('/users', [AdminController::class, 'index'])->name('users.index');
        Route::get('/users/{user}/edit', [AdminController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'update'])->name('users.update');

        
        Route::get('/tickets', [AdminTicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/{ticket}', [AdminTicketController::class, 'show'])->name('tickets.show');
        Route::post('/tickets/{ticket}/reply', [AdminTicketController::class, 'reply'])->name('tickets.reply');
        Route::patch('/tickets/{ticket}/status', [AdminTicketController::class, 'status'])->name('tickets.status'); 
        Route::put('/tickets/{ticket}/close', [AdminTicketController::class, 'close'])->name('tickets.close');   

        
        Route::resource('companies', CompanyController::class)->except(['destroy']);
       
        Route::delete('companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');

        Route::resource('departments', DepartmentController::class)->except(['destroy']);

        Route::delete('departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');

        
        Route::get('/membership', [MembershipController::class, 'index'])->name('membership.index');
        Route::get('/membership/{user}/edit', [MembershipController::class, 'edit'])->name('membership.edit');
        Route::put('/membership/{user}', [MembershipController::class, 'update'])->name('membership.update');

        Route::resource('categories', TicketCategoryController::class)->except(['destroy']);
        Route::delete('categories/{category}', [TicketCategoryController::class, 'destroy'])->name('categories.destroy');
    });