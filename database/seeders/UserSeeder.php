<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user_one = User::create([
            'name' => 'superadmin',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password'),
        ]);
        $user_one->assignRole('superadmin');
    }
}
