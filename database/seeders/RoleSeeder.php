<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // تمام پرمیسن‌های زیرمجموعه (غیر از والدهای صرفاً دسته‌بندی اگر parent_id دارند)
        $allPermissionIds = Permission::pluck('id');

        $companies = Company::all();

        foreach ($companies as $company) {
            $role = Role::updateOrCreate(
                [
                    'slug'       => 'company-manager',
                    'company_id' => $company->id,
                ],
                [
                    'name'         => 'company-manager',
                ]
            );

            if ($allPermissionIds->isNotEmpty()) {
                $role->permissions()->sync($allPermissionIds);
            }
        }

        // نقش پایه سراسری برای زمانی که company_id هنوز ست نشده یا عمومی است
        $globalManagerRole = Role::updateOrCreate(
            [
                'slug'       => 'company-manager',
                'company_id' => null,
            ],
            [
                'name'         => 'مدیر شرکت',
            ]
        );

        if ($allPermissionIds->isNotEmpty()) {
            $globalManagerRole->permissions()->sync($allPermissionIds);
        }
    }
}