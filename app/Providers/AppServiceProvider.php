<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use App\Models\Permission;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ۱. سازنده سیستم (Creator) همیشه دسترسی کامل دارد (بایپس کردن تمام گیت‌ها)
        Gate::before(function ($user, $ability) {
            if ($user->level === 'creator') {
                return true;
            }
        });

        // ۲. ثبت داینامیک گیت‌ها برای بررسی پرمیشن‌های ادمین‌ها و سایر نقش‌ها
        try {
            if (Schema::hasTable('permissions')) {
                foreach (Permission::all() as $permission) {
                    Gate::define($permission->slug, function ($user) use ($permission) {
                        return $user->hasPermission($permission->slug);
                    });
                }
            }
        } catch (\Exception $e) {
            // مدیریت خطاهای احتمالی اتصال به دیتابیس
        }
    }
}