<?php

namespace Tests\Unit\Controllers;

use Tests\TestCase;
use App\Http\Controllers\Frontend\CartController;
use App\Services\CartService;
use App\Services\AuditService;
use App\DTOs\Cart\AddToCartDTO;
use App\Http\Requests\CartAddRequest;
use App\Http\Requests\CartUpdateRequest;
use App\Models\User;
use App\Models\Product;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Mockery;

class CartControllerTest extends TestCase
{
    private $cartService;
    private $auditService;
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->cartService = Mockery::mock(CartService::class);
        $this->auditService = Mockery::mock(AuditService::class);
        $this->controller = new CartController($this->cartService, $this->auditService);
    }

    /** @test */
    public function index_returns_cart_view_with_items()
    {
        // Arrange
        $user = $this->createAuthenticatedUser();
        $cartItems = collect([
            new CartItem(['product_id' => 1, 'quantity' => 2]),
            new CartItem(['product_id' => 2, 'quantity' => 1])
        ]);
        $cartTotal = 150.00;

        $this->cartService
            ->shouldReceive('getCartItems')
            ->once()
            ->with($user)
            ->andReturn($cartItems);

        $this->cartService
            ->shouldReceive('getCartTotal')
            ->once()
            ->with($cartItems)
            ->andReturn($cartTotal);

        $this->auditService
            ->shouldReceive('logPerformance')
            ->once()
            ->with('cart.index', Mockery::any());

        // Act
        $response = $this->controller->index();

        // Assert
        $this->assertInstanceOf(View::class, $response);
        $this->assertEquals('frontend.cart.index', $response->name());
        
        $viewData = $response->getData();
        $this->assertEquals($cartItems, $viewData['cartItems']);
        $this->assertEquals($cartTotal, $viewData['cartTotal']);
    }

    /** @test */
    public function add_item_returns_success_response()
    {
        // Arrange
        $user = $this->createAuthenticatedUser();
        $request = $this->createCartAddRequest();
        
        $this->cartService
            ->shouldReceive('addItem')
            ->once()
            ->with($user, Mockery::type(Product::class), 2)
            ->andReturn([
                'success' => true,
                'message' => 'Produto adicionado ao carrinho',
                'cart_count' => 3,
                'item_id' => 1
            ]);

        $this->auditService
            ->shouldReceive('logCartAction')
            ->once();

        // Act
        $response = $this->controller->add($request);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    protected function createAuthenticatedUser()
    {
        $user = new User(['id' => 1, 'name' => 'Test User']);
        $this->actingAs($user);
        return $user;
    }

    protected function createCartAddRequest()
    {
        $request = Mockery::mock(CartAddRequest::class);
        $request->shouldReceive('validated')->andReturn([
            'product_id' => 1,
            'quantity' => 2
        ]);
        return $request;
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
