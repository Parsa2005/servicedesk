<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreatorSeeder extends Seeder
{
    public function run(): void
    {
        // ۱. ایجاد شرکت پیش‌فرض
        $company = Company::firstOrCreate([
            'name' => 'شرکت پیش‌فرض (سازمان مرکزی)',
            'slug' => 'default-company'
        ]); 

        // ۲. ایجاد دپارتمان پیش‌فرض برای آن شرکت
        $department = Department::firstOrCreate(
            [
                'company_id' => $company->id,
                'name' => 'فناوری اطلاعات (IT)',
                'slug' => 'it-department',
            ]
        );

        // ۳. ایجاد مالک سیستم (Creator) متصل به شرکت و دپارتمان
        User::firstOrCreate(
            ['email' => 'admin'], // یا ایمیل دلخواه شما
            [
                'name' => 'مالک سیستم (Creator)',
                'password' => Hash::make('11092001'), // رمز عبور اولیه
                'level' => 'creator',
                'company_id' => $company->id,
                'department_id' => $department->id,
                'email_verified_at' => now(),
            ]
        );
    }
}