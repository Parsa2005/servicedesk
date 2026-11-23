<?php

namespace Database\Seeders;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            CreatorSeeder::class,
            PermissionSeeder::class,
            CategorySeeder::class,
            RoleSeeder::class,
        ]);
    }
}