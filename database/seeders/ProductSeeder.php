<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criando Categorias
        $eletrônicos = Category::create([
            'name' => 'Eletrônicos',
            'description' => 'Produtos eletrônicos em geral'
        ]);

        $roupas = Category::create([
            'name' => 'Roupas e Acessórios',
            'description' => 'Vestuário e acessórios de moda'
        ]);

        $casa = Category::create([
            'name' => 'Casa e Jardim',
            'description' => 'Produtos para casa e jardim'
        ]);

        $livros = Category::create([
            'name' => 'Livros',
            'description' => 'Livros e materiais educativos'
        ]);

        // Produtos de Eletrônicos
        Product::create([
            'name' => 'Smartphone Samsung Galaxy S24',
            'description' => 'Smartphone top de linha com câmera profissional e tela AMOLED',
            'price' => 2599.99,
            'promotional_price' => 2199.99,
            'sku' => 'SAMS24001',
            'stock_quantity' => 25,
            'min_stock' => 5,
            'active' => true,
            'category_id' => $eletrônicos->id,
        ]);

        Product::create([
            'name' => 'Notebook Dell Inspiron 15',
            'description' => 'Notebook para trabalho e estudos, Intel i5, 8GB RAM, SSD 256GB',
            'price' => 2899.99,
            'promotional_price' => 2499.99,
            'sku' => 'DELL15001',
            'stock_quantity' => 15,
            'min_stock' => 3,
            'active' => true,
            'category_id' => $eletrônicos->id,
        ]);

        Product::create([
            'name' => 'Fone Bluetooth JBL Tune 760NC',
            'description' => 'Fone de ouvido com cancelamento de ruído e bateria de 50h',
            'price' => 399.99,
            'promotional_price' => 299.99,
            'sku' => 'JBL760001',
            'stock_quantity' => 40,
            'min_stock' => 10,
            'active' => true,
            'category_id' => $eletrônicos->id,
        ]);

        Product::create([
            'name' => 'Smart TV LG 55" 4K',
            'description' => 'Smart TV 55 polegadas 4K com WebOS e HDR',
            'price' => 2199.99,
            'promotional_price' => 1899.99,
            'sku' => 'LG55001',
            'stock_quantity' => 12,
            'min_stock' => 2,
            'active' => true,
            'category_id' => $eletrônicos->id,
        ]);

        // Produtos de Roupas
        Product::create([
            'name' => 'Camiseta Polo Masculina',
            'description' => 'Camiseta polo 100% algodão, disponível em várias cores',
            'price' => 89.99,
            'promotional_price' => 69.99,
            'sku' => 'POLO001M',
            'stock_quantity' => 50,
            'min_stock' => 10,
            'active' => true,
            'category_id' => $roupas->id,
        ]);

        Product::create([
            'name' => 'Jeans Feminino Skinny',
            'description' => 'Calça jeans feminina modelo skinny, elastano para conforto',
            'price' => 129.99,
            'promotional_price' => 99.99,
            'sku' => 'JEANS001F',
            'stock_quantity' => 35,
            'min_stock' => 8,
            'active' => true,
            'category_id' => $roupas->id,
        ]);

        Product::create([
            'name' => 'Tênis Esportivo Nike',
            'description' => 'Tênis para corrida e caminhada, tecnologia Air Max',
            'price' => 399.99,
            'promotional_price' => 349.99,
            'sku' => 'NIKE001',
            'stock_quantity' => 28,
            'min_stock' => 6,
            'active' => true,
            'category_id' => $roupas->id,
        ]);

        // Produtos Casa e Jardim
        Product::create([
            'name' => 'Conjunto de Panelas Antiaderente',
            'description' => 'Kit com 5 panelas antiaderentes com cabos ergonômicos',
            'price' => 299.99,
            'promotional_price' => 249.99,
            'sku' => 'PANELA001',
            'stock_quantity' => 20,
            'min_stock' => 4,
            'active' => true,
            'category_id' => $casa->id,
        ]);

        Product::create([
            'name' => 'Aspirador de Pó Vertical',
            'description' => 'Aspirador sem fio com bateria recarregável, filtro HEPA',
            'price' => 599.99,
            'promotional_price' => 499.99,
            'sku' => 'ASPIR001',
            'stock_quantity' => 18,
            'min_stock' => 3,
            'active' => true,
            'category_id' => $casa->id,
        ]);

        Product::create([
            'name' => 'Conjunto de Ferramentas 100 Peças',
            'description' => 'Kit completo de ferramentas para casa com maleta organizadora',
            'price' => 199.99,
            'promotional_price' => 159.99,
            'sku' => 'FERR001',
            'stock_quantity' => 25,
            'min_stock' => 5,
            'active' => true,
            'category_id' => $casa->id,
        ]);

        // Livros
        Product::create([
            'name' => 'Livro: Clean Code',
            'description' => 'Guia completo para escrever código limpo e eficiente',
            'price' => 89.99,
            'sku' => 'BOOK001',
            'stock_quantity' => 30,
            'min_stock' => 5,
            'active' => true,
            'category_id' => $livros->id,
        ]);

        Product::create([
            'name' => 'Livro: Laravel - Do Básico ao Avançado',
            'description' => 'Aprenda Laravel desde o básico até conceitos avançados',
            'price' => 79.99,
            'promotional_price' => 59.99,
            'sku' => 'BOOK002',
            'stock_quantity' => 25,
            'min_stock' => 5,
            'active' => true,
            'category_id' => $livros->id,
        ]);

        // Produtos em promoção
        Product::create([
            'name' => 'Mouse Gamer RGB',
            'description' => 'Mouse gamer com iluminação RGB e 6 botões programáveis',
            'price' => 149.99,
            'promotional_price' => 89.99,
            'sku' => 'MOUSE001',
            'stock_quantity' => 45,
            'min_stock' => 10,
            'active' => true,
            'category_id' => $eletrônicos->id,
        ]);

        Product::create([
            'name' => 'Teclado Mecânico RGB',
            'description' => 'Teclado mecânico com switches blue e iluminação RGB',
            'price' => 299.99,
            'promotional_price' => 199.99,
            'sku' => 'TECL001',
            'stock_quantity' => 22,
            'min_stock' => 5,
            'active' => true,
            'category_id' => $eletrônicos->id,
        ]);

        // Produto sem estoque (para testar)
        Product::create([
            'name' => 'Produto Esgotado - Teste',
            'description' => 'Produto para testar funcionalidades sem estoque',
            'price' => 99.99,
            'sku' => 'TEST001',
            'stock_quantity' => 0,
            'min_stock' => 1,
            'active' => true,
            'category_id' => $eletrônicos->id,
        ]);

        $this->command->info('Produtos criados com sucesso!');
        $this->command->info('- 4 Categorias criadas');
        $this->command->info('- 15 Produtos adicionados');
        $this->command->info('- Produtos com e sem promoção');
        $this->command->info('- Estoque variado para testes');
    }
}
