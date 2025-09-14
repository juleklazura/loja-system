<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'João Silva',
                'email' => 'joao@example.com',
                'password' => Hash::make('password'),
                'type' => 'customer',
            ],
            [
                'name' => 'Maria Santos',
                'email' => 'maria@example.com',
                'password' => Hash::make('password'),
                'type' => 'customer',
            ],
            [
                'name' => 'Pedro Oliveira',
                'email' => 'pedro@example.com',
                'password' => Hash::make('password'),
                'type' => 'customer',
            ],
            [
                'name' => 'Ana Costa',
                'email' => 'ana@example.com',
                'password' => Hash::make('password'),
                'type' => 'customer',
            ],
            [
                'name' => 'Carlos Mendes',
                'email' => 'carlos@example.com',
                'password' => Hash::make('password'),
                'type' => 'customer',
            ],
        ];

        foreach ($customers as $customer) {
            User::create($customer);
        }
    }
}
