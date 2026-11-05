<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [

            'users' => [
                'display_name' => 'کاربران و پرسنل',
                'permissions' => [
                    'users.index'          => 'مشاهده لیست کاربران',
                    'users.view-all'       => 'مشاهده تمام پرسنل شرکت',
                    'users.create'         => 'ایجاد کاربر جدید',
                    'users.edit'           => 'ویرایش کلی اطلاعات کاربر',
                    'users.edit.name'      => 'ویرایش نام کاربر',
                    'users.edit.email'     => 'ویرایش ایمیل کاربر',
                    'users.edit.password'  => 'تغییر رمز عبور کاربر',
                    'users.edit.role'      => 'تغییر نقش سازمانی کاربر',
                    'users.edit.department'=> 'تغییر دپارتمان کاربر',
                    'users.edit.company'   => 'تغییر شرکت کاربر (مدیر کل)',
                    'users.delete'         => 'حذف کاربر',
                ],
            ],

            'roles' => [
                'display_name' => 'نقش‌ها و دسترسی‌ها',
                'permissions' => [
                    'roles.index'  => 'مشاهده لیست نقش‌ها',
                    'roles.create' => 'ایجاد نقش جدید',
                    'roles.edit'   => 'ویرایش نقش و پرمیشن‌ها',
                    'roles.delete' => 'حذف نقش',
                ],
            ],

            'departments' => [
                'display_name' => 'دپارتمان‌ها و واحدها',
                'permissions' => [
                    'departments.index'  => 'مشاهده دپارتمان‌ها',
                    'departments.create' => 'ایجاد دپارتمان جدید',
                    'departments.edit'   => 'ویرایش دپارتمان',
                    'departments.delete' => 'حذف دپارتمان',
                ],
            ],

            'categories' => [
                'display_name' => 'دسته‌بندی تیکت‌ها',
                'permissions' => [
                    'categories.index'  => 'مشاهده دسته‌بندی‌های تیکت',
                    'categories.create' => 'ایجاد دسته‌بندی تیکت',
                    'categories.edit'   => 'ویرایش دسته‌بندی تیکت',
                    'categories.delete' => 'حذف دسته‌بندی تیکت',
                ],
            ],

            'tickets' => [
                'display_name' => 'تیکت‌ها و پشتیبانی داخلی',
                'permissions' => [
                    'tickets.index'          => 'مشاهده تیکت‌ها',
                    'tickets.view-all'       => 'مشاهده تمام تیکت‌های شرکت',
                    'tickets.create'         => 'ثبت تیکت جدید',
                    'tickets.reply'          => 'پاسخ‌گویی به تیکت',
                    'tickets.internal-notes' => 'ثبت یادداشت داخلی (محرمانه)',
                    'tickets.status'         => 'تغییر وضعیت تیکت',
                    'tickets.manage'         => 'مدیریت کامل و حذف تیکت‌ها',
                ],
            ],

            'companies' => [
                'display_name' => 'شرکت‌ها (مخصوص مدیر کل)',
                'permissions' => [
                    'companies.index'  => 'مشاهده لیست شرکت‌ها',
                    'companies.create' => 'ایجاد شرکت جدید',
                    'companies.edit'   => 'ویرایش اطلاعات شرکت',
                    'companies.delete' => 'حذف شرکت',
                ],
            ],

        ];

        foreach ($groups as $group => $data) {

            // ایجاد یا بروزرسانی والدِ گروه در جدول پرمیشن‌ها
            $parent = Permission::updateOrCreate(
                [
                    'slug' => $group,
                ],
                [
                    'name'         => $group,
                    'display_name' => $data['display_name'],
                    'group'        => $group,
                    'parent_id'    => null,
                ]
            );

            // ایجاد یا بروزرسانی زیرشاخه‌های هر گروه با اتصال به parent_id
            foreach ($data['permissions'] as $slug => $displayName) {
                Permission::updateOrCreate(
                    [
                        'slug' => $slug,
                    ],
                    [
                        'name'         => $slug,
                        'display_name' => $displayName,
                        'group'        => $group,
                        'parent_id'    => $parent->id,
                    ]
                );
            }
        }
    }
}