<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class MoreProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar categorias existentes
        $categories = Category::all();
        
        if ($categories->isEmpty()) {
            $this->command->info('Nenhuma categoria encontrada. Execute o CategorySeeder primeiro.');
            return;
        }

        $products = [
            // Eletrônicos
            [
                'name' => 'iPhone 15 Pro',
                'description' => 'Smartphone Apple iPhone 15 Pro com 128GB, câmera tripla de 48MP e chip A17 Pro.',
                'price' => 8999.00,
                'stock_quantity' => 15,
                'min_stock' => 5,
                'sku' => 'IPH15-PRO-128',
                'category' => 'Eletrônicos',
            ],
            [
                'name' => 'Samsung Galaxy S24',
                'description' => 'Smartphone Samsung Galaxy S24 com 256GB, câmera de 50MP e processador Snapdragon 8 Gen 3.',
                'price' => 6499.00,
                'stock_quantity' => 20,
                'min_stock' => 3,
                'sku' => 'SAM-S24-256',
                'category' => 'Eletrônicos',
            ],
            [
                'name' => 'MacBook Air M2',
                'description' => 'Notebook Apple MacBook Air com chip M2, 13 polegadas, 512GB SSD e 16GB RAM.',
                'price' => 12999.00,
                'stock_quantity' => 8,
                'min_stock' => 2,
                'category' => 'Eletrônicos',
            ],
            [
                'name' => 'PlayStation 5',
                'description' => 'Console PlayStation 5 com 825GB SSD, controle DualSense e suporte a jogos 4K.',
                'price' => 4999.00,
                'stock_quantity' => 12,
                'min_stock' => 3,
                'category' => 'Eletrônicos',
            ],
            [
                'name' => 'Smart TV Samsung 55"',
                'description' => 'Smart TV Samsung 55 polegadas 4K UHD, HDR10+ e sistema Tizen.',
                'price' => 3299.00,
                'stock_quantity' => 10,
                'min_stock' => 2,
                'category' => 'Eletrônicos',
            ],

            // Roupas
            [
                'name' => 'Camiseta Polo Lacoste',
                'description' => 'Camiseta polo masculina Lacoste 100% algodão, disponível em várias cores.',
                'price' => 289.00,
                'stock_quantity' => 50,
                'min_stock' => 10,
                'category' => 'Roupas',
            ],
            [
                'name' => 'Jeans Levi\'s 501',
                'description' => 'Calça jeans masculina Levi\'s modelo 501, corte reto clássico.',
                'price' => 349.00,
                'stock_quantity' => 35,
                'min_stock' => 8,
                'category' => 'Roupas',
            ],
            [
                'name' => 'Vestido Floral Zara',
                'description' => 'Vestido feminino floral Zara, tecido leve e confortável para o verão.',
                'price' => 199.00,
                'stock_quantity' => 25,
                'min_stock' => 5,
                'category' => 'Roupas',
            ],
            [
                'name' => 'Tênis Nike Air Max',
                'description' => 'Tênis Nike Air Max com tecnologia de amortecimento e design moderno.',
                'price' => 599.00,
                'stock_quantity' => 40,
                'min_stock' => 8,
                'category' => 'Roupas',
            ],
            [
                'name' => 'Jaqueta de Couro',
                'description' => 'Jaqueta de couro legítimo, estilo casual e elegante.',
                'price' => 799.00,
                'stock_quantity' => 15,
                'min_stock' => 3,
                'category' => 'Roupas',
            ],

            // Casa e Jardim
            [
                'name' => 'Sofá 3 Lugares',
                'description' => 'Sofá de 3 lugares em tecido suede, cor cinza, design moderno e confortável.',
                'price' => 1899.00,
                'stock_quantity' => 8,
                'min_stock' => 2,
                'category' => 'Casa e Jardim',
            ],
            [
                'name' => 'Mesa de Jantar 6 Lugares',
                'description' => 'Mesa de jantar em madeira maciça para 6 pessoas, acabamento natural.',
                'price' => 1299.00,
                'stock_quantity' => 5,
                'min_stock' => 1,
                'category' => 'Casa e Jardim',
            ],
            [
                'name' => 'Conjunto de Panelas Tramontina',
                'description' => 'Conjunto de panelas antiaderente Tramontina com 5 peças e tampas de vidro.',
                'price' => 299.00,
                'stock_quantity' => 25,
                'min_stock' => 5,
                'category' => 'Casa e Jardim',
            ],
            [
                'name' => 'Aspirador de Pó Robô',
                'description' => 'Aspirador de pó robô inteligente com mapeamento e controle por app.',
                'price' => 1499.00,
                'stock_quantity' => 12,
                'min_stock' => 3,
                'category' => 'Casa e Jardim',
            ],
            [
                'name' => 'Liquidificador Osterizer',
                'description' => 'Liquidificador Osterizer com 12 velocidades e jarra de vidro de 1,25L.',
                'price' => 189.00,
                'stock_quantity' => 30,
                'min_stock' => 6,
                'category' => 'Casa e Jardim',
            ],

            // Livros
            [
                'name' => 'Dom Casmurro - Machado de Assis',
                'description' => 'Clássico da literatura brasileira em edição especial com capa dura.',
                'price' => 45.00,
                'stock_quantity' => 100,
                'min_stock' => 20,
                'category' => 'Livros',
            ],
            [
                'name' => 'O Alquimista - Paulo Coelho',
                'description' => 'Bestseller mundial sobre a jornada de autoconhecimento de um jovem pastor.',
                'price' => 39.90,
                'stock_quantity' => 80,
                'min_stock' => 15,
                'category' => 'Livros',
            ],
            [
                'name' => 'Sapiens - Yuval Noah Harari',
                'description' => 'Uma breve história da humanidade que revolucionou nossa compreensão sobre nós mesmos.',
                'price' => 54.90,
                'stock_quantity' => 60,
                'min_stock' => 10,
                'category' => 'Livros',
            ],
            [
                'name' => 'Clean Code - Robert Martin',
                'description' => 'Manual de boas práticas para desenvolvimento de software limpo e eficiente.',
                'price' => 89.90,
                'stock_quantity' => 25,
                'min_stock' => 5,
                'category' => 'Livros',
            ],
            [
                'name' => 'O Pequeno Príncipe',
                'description' => 'Clássico atemporal de Antoine de Saint-Exupéry em edição ilustrada.',
                'price' => 29.90,
                'stock_quantity' => 150,
                'min_stock' => 25,
                'category' => 'Livros',
            ],
        ];

        foreach ($products as $productData) {
            // Buscar categoria pelo nome
            $category = $categories->where('name', $productData['category'])->first();
            
            if ($category) {
                Product::create([
                    'name' => $productData['name'],
                    'description' => $productData['description'],
                    'price' => $productData['price'],
                    'stock_quantity' => $productData['stock_quantity'],
                    'min_stock' => $productData['min_stock'],
                    'category_id' => $category->id,
                    'active' => true,
                ]);
            }
        }

        $this->command->info('Produtos adicionais criados com sucesso!');
    }
}
