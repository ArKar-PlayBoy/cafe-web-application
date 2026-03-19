<?php

namespace Database\Seeders;

use App\Services\PermissionService;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PermissionService::initializePermissions();
    }
}
