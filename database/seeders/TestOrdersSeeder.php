<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;

class TestOrdersSeeder extends Seeder
{
    public function run()
    {
        $customer = User::where('user_type', 'customer')->first();
        $products = Product::take(3)->get();
        
        if (!$customer || $products->isEmpty()) {
            $this->command->info('Usuário cliente ou produtos não encontrados.');
            return;
        }

        // Criar 3 pedidos de teste das últimas 24 horas
        for ($i = 0; $i < 3; $i++) {
            $order = Order::create([
                'user_id' => $customer->id,
                'status' => 'pending',
                'total_amount' => 0,
                'shipping_address' => 'Endereço de teste, 123',
                'payment_method' => 'credit_card',
                'created_at' => now()->subHours(rand(1, 23)),
                'updated_at' => now(),
            ]);

            $totalAmount = 0;
            $product = $products->random();
            
            $quantity = rand(1, 2);
            $unitPrice = $product->price;
            $totalPrice = $unitPrice * $quantity;
            
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
            ]);
            
            $totalAmount += $totalPrice;
            
            $order->update(['total_amount' => $totalAmount]);
            
            $this->command->info("Pedido {$order->id} criado: R$ " . number_format($totalAmount, 2, ',', '.'));
        }
        
        $this->command->info('TestOrdersSeeder executado com sucesso!');
    }
}
