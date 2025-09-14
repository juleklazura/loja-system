<?php

namespace Tests\Feature\Exceptions;

use App\Exceptions\Cart\InvalidQuantityException;
use App\Exceptions\Cart\ProductNotAvailableException;
use App\Exceptions\Product\ProductNotFoundException;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExceptionHandlingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create([
            'name' => 'Produto Teste',
            'stock' => 5,
            'is_active' => true
        ]);
    }

    /** @test */
    public function test_product_not_found_exception_json_response()
    {
        $this->actingAs($this->user);
        
        $response = $this->postJson('/carrinho/adicionar', [
            'product_id' => 999,
            'quantity' => 1
        ]);

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'error_type' => 'product_error',
                    'message' => 'Produto #999 não encontrado',
                    'code' => 3001
                ]);
    }

    /** @test */
    public function test_product_not_available_exception()
    {
        $this->actingAs($this->user);
        
        // Criar produto inativo
        $inactiveProduct = Product::factory()->create([
            'is_active' => false,
            'name' => 'Produto Inativo'
        ]);

        $response = $this->postJson('/carrinho/adicionar', [
            'product_id' => $inactiveProduct->id,
            'quantity' => 1
        ]);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                    'error_type' => 'cart_error',
                    'code' => 1001
                ])
                ->assertJsonFragment([
                    'message' => "O produto 'Produto Inativo' não está disponível ou tem estoque insuficiente. Estoque disponível: 0"
                ]);
    }

    /** @test */
    public function test_invalid_quantity_exception_negative()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/carrinho/adicionar', [
            'product_id' => $this->product->id,
            'quantity' => -1
        ]);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                    'error_type' => 'cart_error',
                    'code' => 1003
                ])
                ->assertJsonFragment([
                    'message' => 'Quantidade inválida: -1. A quantidade deve ser maior que zero'
                ]);
    }

    /** @test */
    public function test_invalid_quantity_exception_too_high()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/carrinho/adicionar', [
            'product_id' => $this->product->id,
            'quantity' => 150
        ]);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                    'error_type' => 'cart_error',
                    'code' => 1003
                ])
                ->assertJsonFragment([
                    'message' => 'Quantidade inválida: 150. Quantidade máxima permitida: 99'
                ]);
    }

    /** @test */
    public function test_insufficient_stock_exception()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/carrinho/adicionar', [
            'product_id' => $this->product->id,
            'quantity' => 10 // Produto tem apenas 5 em estoque
        ]);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                    'error_type' => 'cart_error',
                    'code' => 1001
                ])
                ->assertJsonFragment([
                    'message' => "O produto 'Produto Teste' não está disponível ou tem estoque insuficiente. Estoque disponível: 5"
                ]);
    }

    /** @test */
    public function test_successful_cart_addition()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/carrinho/adicionar', [
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true
                ]);
    }
}
