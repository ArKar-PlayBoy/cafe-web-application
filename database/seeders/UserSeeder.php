<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::where('slug', 'super_admin')->first();
        $adminRole = Role::where('slug', 'admin')->first();
        $staffRole = Role::where('slug', 'staff')->first();
        $customerRole = Role::where('slug', 'customer')->first();

        // Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@cafe.com',
            'password' => Hash::make('password'),
            'phone' => '09123456789',
            'address' => 'Yangon, Myanmar',
            'email_verified_at' => now(),
            'role_id' => $superAdminRole->id,
        ]);

        // Regular Admin
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@cafe.com',
            'password' => Hash::make('password'),
            'phone' => '09123456788',
            'address' => 'Yangon, Myanmar',
            'email_verified_at' => now(),
            'role_id' => $adminRole->id,
        ]);

        // Regular Customer
        User::create([
            'name' => 'Bob',
            'email' => 'bob@cafe.com',
            'password' => Hash::make('password'),
            'phone' => '09987654321',
            'address' => 'Yangon, Myanmar',
            'email_verified_at' => now(),
            'role_id' => $customerRole ? $customerRole->id : null,
        ]);

        // Staff users
        $staffNames = ['Staff One', 'Staff Two', 'Staff Three'];
        $staffEmails = ['staff1@cafe.com', 'staff2@cafe.com', 'staff3@cafe.com'];

        for ($i = 0; $i < 3; $i++) {
            User::create([
                'name' => $staffNames[$i],
                'email' => $staffEmails[$i],
                'password' => Hash::make('password'),
                'phone' => '09'.rand(100000000, 999999999),
                'address' => 'Cafe Address',
                'email_verified_at' => now(),
                'role_id' => $staffRole->id,
            ]);
        }
    }
}
