<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create categories
        $categories = [
            [
                'name' => 'Eletrônicos',
                'description' => 'Produtos eletrônicos e tecnologia',
                'active' => true
            ],
            [
                'name' => 'Roupas',
                'description' => 'Vestuário e acessórios',
                'active' => true
            ],
            [
                'name' => 'Casa e Decoração',
                'description' => 'Produtos para casa e decoração',
                'active' => true
            ],
            [
                'name' => 'Livros',
                'description' => 'Livros e material educativo',
                'active' => true
            ]
        ];

        foreach ($categories as $categoryData) {
            $category = Category::create($categoryData);
            
            // Create products for each category
            $this->createProductsForCategory($category);
        }

        // Create sample promotions
        $this->createSamplePromotions();
    }

    private function createProductsForCategory(Category $category)
    {
        $products = [];

        switch ($category->name) {
            case 'Eletrônicos':
                $products = [
                    [
                        'name' => 'Smartphone Galaxy',
                        'description' => 'Smartphone Android com 128GB',
                        'price' => 899.99,
                        'promotional_price' => 799.99,
                        'stock_quantity' => 50,
                        'min_stock' => 5,
                        'sku' => 'SMART001',
                        'active' => true
                    ],
                    [
                        'name' => 'Notebook Gamer',
                        'description' => 'Notebook para jogos com placa de vídeo dedicada',
                        'price' => 2499.99,
                        'stock_quantity' => 20,
                        'min_stock' => 2,
                        'sku' => 'NOTE001',
                        'active' => true
                    ]
                ];
                break;

            case 'Roupas':
                $products = [
                    [
                        'name' => 'Camiseta Básica',
                        'description' => 'Camiseta de algodão básica',
                        'price' => 29.99,
                        'stock_quantity' => 100,
                        'min_stock' => 10,
                        'sku' => 'CAM001',
                        'active' => true
                    ],
                    [
                        'name' => 'Calça Jeans',
                        'description' => 'Calça jeans masculina',
                        'price' => 89.99,
                        'promotional_price' => 69.99,
                        'stock_quantity' => 75,
                        'min_stock' => 8,
                        'sku' => 'CAL001',
                        'active' => true
                    ]
                ];
                break;

            case 'Casa e Decoração':
                $products = [
                    [
                        'name' => 'Almofada Decorativa',
                        'description' => 'Almofada colorida para decoração',
                        'price' => 39.99,
                        'stock_quantity' => 60,
                        'min_stock' => 6,
                        'sku' => 'ALM001',
                        'active' => true
                    ]
                ];
                break;

            case 'Livros':
                $products = [
                    [
                        'name' => 'Clean Code',
                        'description' => 'Livro sobre programação limpa',
                        'price' => 79.99,
                        'stock_quantity' => 30,
                        'min_stock' => 3,
                        'sku' => 'LIV001',
                        'active' => true
                    ]
                ];
                break;
        }

        foreach ($products as $productData) {
            Product::create(array_merge($productData, [
                'category_id' => $category->id
            ]));
        }
    }

    private function createSamplePromotions()
    {
        $smartphone = Product::where('sku', 'SMART001')->first();
        $calca = Product::where('sku', 'CAL001')->first();

        if ($smartphone) {
            Promotion::create([
                'name' => 'Promoção Smartphone',
                'description' => '10% de desconto no smartphone',
                'type' => 'percentage',
                'value' => 10,
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(10),
                'active' => true,
                'product_id' => $smartphone->id
            ]);
        }

        if ($calca) {
            Promotion::create([
                'name' => 'Promoção Calça Jeans',
                'description' => 'R$ 20 de desconto na calça jeans',
                'type' => 'fixed',
                'value' => 20,
                'start_date' => now()->subDays(3),
                'end_date' => now()->addDays(7),
                'active' => true,
                'product_id' => $calca->id
            ]);
        }
    }
}
