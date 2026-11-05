<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\TicketCategory;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // ۱. دسته‌بندی‌های گلوبال (سراسری) - برای تمام شرکت‌ها مشترک
        $globalCategories = [
            [
                'name'        => 'پشتیبانی فنی و زیرساخت عمومی',
                'slug'        => 'global-technical-support',
                'description' => 'مشکلات عمومی شبکه، سخت‌افزار و ابزارهای مشترک'
            ],
            [
                'name'        => 'عمومی و پیشنهادات',
                'slug'        => 'global-general',
                'description' => 'ثبت نظرات، پیشنهادات و هماهنگی‌های کلی'
            ],
        ];

        foreach ($globalCategories as $cat) {
            TicketCategory::updateOrCreate(
                [
                    'slug'       => $cat['slug'],
                    'company_id' => null, // 👈 گلوبال (بدون شرکت خاص)
                ],
                [
                    'name'        => $cat['name'],
                    'description' => $cat['description'],
                ]
            );
        }

        // ۲. دسته‌بندی‌های اختصاصی برای هر شرکت
        $companies = Company::all();

        $companySpecificCategories = [
            [
                'name'        => 'امور مالی و حسابداری اختصاصی',
                'slug'        => 'company-financial',
                'description' => 'درخواست‌های مالی داخلی مربوط به این شرکت'
            ],
            [
                'name'        => 'منابع انسانی و اداری داخلی',
                'slug'        => 'company-hr',
                'description' => 'امور پرسنلی و اداری مختص همین سازمان'
            ],
        ];

        foreach ($companies as $company) {
            foreach ($companySpecificCategories as $cat) {
                TicketCategory::updateOrCreate(
                    [
                        'slug'       => $cat['slug'],
                        'company_id' => $company->id, // 👈 اختصاصی این شرکت
                    ],
                    [
                        'name'        => $cat['name'],
                        'description' => $cat['description'],
                    ]
                );
            }
        }
    }
}