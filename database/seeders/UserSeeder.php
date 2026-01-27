<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if user exists to avoid duplicates
        if (!User::where('user', 'admin')->exists()) {
            User::create([
                'name' => 'Administrador',
                'user' => 'admin',
                'email' => 'admin@sacratech.com',
                'password' => Hash::make('senha123'), // Senha hashada
                'status' => 1,
                'rule' => 'admin',
                'is_pass_change' => 0,
                'login_attempts' => 0,
                'created_at' => now(),
            ]);
        }
    }
}
