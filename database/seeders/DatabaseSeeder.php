<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesPermissionsSeeder::class,
            SchoolSeeder::class,
            SuperAdminSeeder::class,
            DemoSeeder::class,
        ]);
    }
}
