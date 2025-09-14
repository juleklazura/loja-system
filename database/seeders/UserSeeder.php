<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuário Admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@loja.com',
            'email_verified_at' => now(),
            'password' => Hash::make('admin123'),
            'user_type' => 'admin',
        ]);

        // Usuário Normal
        User::create([
            'name' => 'João Silva',
            'email' => 'joao@email.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
            'user_type' => 'customer',
        ]);

        // Usuário Normal 2
        User::create([
            'name' => 'Maria Santos',
            'email' => 'maria@email.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
            'user_type' => 'customer',
        ]);

        // Usuário Normal 3
        User::create([
            'name' => 'Pedro Costa',
            'email' => 'pedro@email.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
            'user_type' => 'customer',
        ]);

        $this->command->info('Usuários criados com sucesso!');
        $this->command->info('Admin: admin@loja.com / admin123 (user_type: admin)');
        $this->command->info('Usuários: joao@email.com, maria@email.com, pedro@email.com / password123 (user_type: customer)');
    }
}
