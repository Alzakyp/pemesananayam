<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        User::create([
            'nama' => 'Admin User',
            'email' => 'admin@pemesananayam.test',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'no_hp' => '081234567890'
        ]);
    }
}
