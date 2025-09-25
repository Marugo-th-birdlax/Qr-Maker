<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (!User::where('email','admin@example.com')->exists()) {
            User::create([
                'employee_id' => 'mrt239',
                'first_name'  => 'System',
                'last_name'   => 'Admin',
                'email'       => 'admin@example.com',
                'phone'       => '080-000-0000',
                'password'    => Hash::make('password'), // เปลี่ยนทันที!
                'role'        => 'admin',
                'is_active'   => true,
            ]);
        }
    }
}
