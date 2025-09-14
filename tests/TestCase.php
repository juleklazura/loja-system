<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Limpar cache entre testes
        Cache::flush();
        
        // Fake events e queues por padrão
        Event::fake();
        Queue::fake();
        
        // Nota: Chame $this->seedBasicData() manualmente nos testes que precisarem
    }

    /**
     * Criar dados básicos para testes
     */
    protected function seedBasicData(): void
    {
        // Criar categoria padrão apenas se não existir
        if (!Category::where('slug', 'eletronicos')->exists()) {
            Category::factory()->create([
                'name' => 'Eletrônicos',
                'slug' => 'eletronicos'
            ]);
        }

        // Criar produto padrão apenas se não existir
        if (!Product::where('name', 'Produto Teste')->exists()) {
            $category = Category::where('slug', 'eletronicos')->first();
            Product::factory()->create([
                'name' => 'Produto Teste',
                'price' => 100.00,
                'stock_quantity' => 10,
                'active' => true,
                'category_id' => $category->id
            ]);
        }
    }

    /**
     * Obter ou criar categoria padrão para testes
     */
    protected function getDefaultCategory(): Category
    {
        return Category::firstOrCreate(
            ['slug' => 'eletronicos'],
            [
                'name' => 'Eletrônicos',
                'slug' => 'eletronicos'
            ]
        );
    }

    /**
     * Obter ou criar produto padrão para testes
     */
    protected function getDefaultProduct(): Product
    {
        $category = $this->getDefaultCategory();
        
        return Product::firstOrCreate(
            ['name' => 'Produto Teste'],
            [
                'name' => 'Produto Teste',
                'price' => 100.00,
                'stock_quantity' => 10,
                'active' => true,
                'category_id' => $category->id
            ]
        );
    }

    /**
     * Criar usuário autenticado para testes
     */
    protected function createAuthenticatedUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $this->actingAs($user);
        return $user;
    }

    /**
     * Criar usuário admin para testes
     */
    protected function createAdminUser(): User
    {
        return $this->createAuthenticatedUser([
            'is_admin' => true,
            'email_verified_at' => now()
        ]);
    }

    /**
     * Criar produto para testes
     */
    protected function createProduct(array $attributes = []): Product
    {
        return Product::factory()->create(array_merge([
            'category_id' => 1,
            'active' => true,
            'stock_quantity' => 10
        ], $attributes));
    }

    /**
     * Assert que uma exceção específica foi lançada
     */
    protected function assertExceptionThrown(string $exceptionClass, callable $callback): void
    {
        $thrown = false;
        try {
            $callback();
        } catch (\Exception $e) {
            $this->assertInstanceOf($exceptionClass, $e);
            $thrown = true;
        }
        
        $this->assertTrue($thrown, "Expected exception {$exceptionClass} was not thrown");
    }

    /**
     * Assert que cache foi invalidado
     */
    protected function assertCacheInvalidated(string $key): void
    {
        $this->assertNull(Cache::get($key));
    }

    /**
     * Assert que evento foi disparado
     */
    protected function assertEventDispatched(string $eventClass, callable $callback = null): void
    {
        if ($callback) {
            Event::assertDispatched($eventClass, $callback);
        } else {
            Event::assertDispatched($eventClass);
        }
    }
}
