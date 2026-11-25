<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $allPermissionIds = Permission::pluck('id');

        // یک نقش استاندارد و مشترک برای مدیر شرکت (محدوده داده از روی company_id کاربر و زیرمجموعه‌ها اعمال می‌شود)
        $managerRole = Role::updateOrCreate(
            [
                'slug'       => 'company-manager',
                'company_id' => null,
            ],
            [
                'name' => 'مدیر شرکت',
            ]
        );

        if ($allPermissionIds->isNotEmpty()) {
            $managerRole->permissions()->sync($allPermissionIds);
        }
    }
}