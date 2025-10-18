<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@test.com'], // find by email
            [
                'first_name' => 'Admin',
                'last_name'  => 'User',
                'mobile'     => '0000000000',
                'address'    => 'System Generated',
                'is_verified' => true,
                'email_verified_at' => now(),
                'status'     => 'active',
                'password'   => Hash::make('admin123'),
            ]
        );
    }
}
