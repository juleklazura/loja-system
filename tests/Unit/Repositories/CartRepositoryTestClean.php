<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Repositories\CartRepository;
use App\Services\CacheService;
use App\Models\CartItem;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class CartRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private CartRepository $repository;
    private $mockCacheService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockCacheService = Mockery::mock(CacheService::class);
        $this->repository = new CartRepository($this->mockCacheService);
    }

    /** @test */
    public function can_get_cart_items_by_user()
    {
        // Arrange
        $category = $this->getDefaultCategory();
        $user = User::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        
        CartItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        // Act
        $result = $this->repository->getCartItemsByUser($user->id);

        // Assert
        $this->assertCount(1, $result);
        $this->assertEquals(2, $result->first()->quantity);
    }

    /** @test */
    public function can_find_cart_item()
    {
        // Arrange
        $category = $this->getDefaultCategory();
        $user = User::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        
        CartItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        // Act
        $result = $this->repository->findCartItem($user->id, $product->id);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals($user->id, $result->user_id);
        $this->assertEquals($product->id, $result->product_id);
    }

    /** @test */
    public function can_create_cart_item()
    {
        // Arrange
        $category = $this->getDefaultCategory();
        $user = User::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->mockCacheService
            ->shouldReceive('invalidateCartCache')
            ->once()
            ->with($user->id);

        // Act
        $result = $this->repository->createCartItem([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        // Assert
        $this->assertInstanceOf(CartItem::class, $result);
        $this->assertEquals($user->id, $result->user_id);
        $this->assertEquals($product->id, $result->product_id);
        $this->assertEquals(2, $result->quantity);
    }

    /** @test */
    public function can_get_cart_count()
    {
        // Arrange
        $category = $this->getDefaultCategory();
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['category_id' => $category->id]);
        $product2 = Product::factory()->create(['category_id' => $category->id]);

        CartItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product1->id,
            'quantity' => 2
        ]);
        CartItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product2->id,
            'quantity' => 3
        ]);

        // Act
        $result = $this->repository->getCartCount($user->id);

        // Assert
        $this->assertEquals(5, $result);
    }

    /** @test */
    public function can_clear_user_cart()
    {
        // Arrange
        $category = $this->getDefaultCategory();
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['category_id' => $category->id]);
        $product2 = Product::factory()->create(['category_id' => $category->id]);

        CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product1->id, 'quantity' => 1]);
        CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product2->id, 'quantity' => 2]);

        $this->mockCacheService
            ->shouldReceive('invalidateCartCache')
            ->with($user->id)
            ->once();

        // Act
        $result = $this->repository->clearUserCart($user->id);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('cart_items', ['user_id' => $user->id]);
    }

    private function getDefaultCategory(): Category
    {
        return Category::factory()->create([
            'name' => 'Categoria Teste',
            'description' => 'Categoria para testes',
            'active' => true
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
