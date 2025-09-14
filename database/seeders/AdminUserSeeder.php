<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        // Create admin user
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@loja.com',
            'password' => Hash::make('admin123'),
            'user_type' => 'admin',
            'phone' => '(11) 99999-9999',
            'address' => 'Endereço do Administrador',
            'email_verified_at' => now(),
        ]);

        // Create sample customer
        User::create([
            'name' => 'Cliente Teste',
            'email' => 'cliente@teste.com',
            'password' => Hash::make('123456'),
            'user_type' => 'customer',
            'phone' => '(11) 88888-8888',
            'address' => 'Rua Teste, 123',
            'email_verified_at' => now(),
        ]);
    }
}
