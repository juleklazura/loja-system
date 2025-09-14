<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class TestProductsSeeder extends Seeder
{
    public function run()
    {
        $categories = Category::all();
        
        if ($categories->isEmpty()) {
            $this->command->info('Nenhuma categoria encontrada. Execute o CategorySeeder primeiro.');
            return;
        }

        $products = [
            [
                'name' => 'iPhone 15 Pro',
                'description' => 'Smartphone Apple iPhone 15 Pro com 128GB.',
                'price' => 8999.00,
                'stock_quantity' => 5, // Estoque baixo para notificação
                'min_stock' => 10,
                'sku' => 'IPH15-PRO-128',
                'category' => 'Eletrônicos',
            ],
            [
                'name' => 'Samsung Galaxy S24',
                'description' => 'Smartphone Samsung Galaxy S24 com 256GB.',
                'price' => 6499.00,
                'stock_quantity' => 3, // Estoque baixo para notificação
                'min_stock' => 10,
                'sku' => 'SAM-S24-256',
                'category' => 'Eletrônicos',
            ],
            [
                'name' => 'Camiseta Polo',
                'description' => 'Camiseta polo masculina 100% algodão.',
                'price' => 89.00,
                'stock_quantity' => 8, // Estoque baixo para notificação
                'min_stock' => 10,
                'sku' => 'POLO-001',
                'category' => 'Roupas',
            ],
        ];

        foreach ($products as $productData) {
            $categoryName = $productData['category'];
            unset($productData['category']);
            
            $category = $categories->where('name', $categoryName)->first();
            
            if ($category) {
                $productData['category_id'] = $category->id;
                $productData['active'] = true;
                
                Product::create($productData);
                $this->command->info("Produto '{$productData['name']}' criado com sucesso!");
            }
        }
        
        $this->command->info('TestProductsSeeder executado com sucesso!');
    }
}
