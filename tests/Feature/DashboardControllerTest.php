<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\DashboardController;
use App\Services\DashboardCacheService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Mockery;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $dashboardCacheService;
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock do DashboardCacheService
        $this->dashboardCacheService = Mockery::mock(DashboardCacheService::class);
        $this->controller = new DashboardController($this->dashboardCacheService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_load_dashboard_index_successfully()
    {
        // Arrange
        $mockData = [
            'totalProducts' => 10,
            'totalCategories' => 5,
            'totalOrders' => 15,
            'totalUsers' => 20,
            'recentOrders' => collect([]),
            'lowStockProducts' => collect([]),
            'categoriesWithCounts' => collect([]),
            'revenueData' => ['labels' => [], 'data' => []]
        ];

        $this->dashboardCacheService
            ->shouldReceive('getDashboardData')
            ->once()
            ->andReturn($mockData);

        // Act
        $response = $this->controller->index();

        // Assert
        $this->assertInstanceOf(\Illuminate\View\View::class, $response);
        $this->assertEquals('admin.dashboard', $response->getName());
        
        $viewData = $response->getData();
        $this->assertEquals(10, $viewData['totalProducts']);
        $this->assertEquals(5, $viewData['totalCategories']);
        $this->assertEquals(15, $viewData['totalOrders']);
        $this->assertEquals(20, $viewData['totalUsers']);
    }

    /** @test */
    public function it_can_clear_dashboard_cache()
    {
        // Arrange
        $this->dashboardCacheService
            ->shouldReceive('clearDashboardCache')
            ->once();

        // Act
        $response = $this->controller->clearCache();

        // Assert
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
    }

    /** @test */
    public function dashboard_route_requires_authentication()
    {
        // Act
        $response = $this->get(route('admin.dashboard'));

        // Assert
        $response->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_admin_can_access_dashboard()
    {
        // Arrange
        $admin = User::factory()->create(['user_type' => 'admin']);
        
        // Mock service data
        $this->app->instance(DashboardCacheService::class, $this->dashboardCacheService);
        
        $this->dashboardCacheService
            ->shouldReceive('getDashboardData')
            ->once()
            ->andReturn([
                'totalProducts' => 0,
                'totalCategories' => 0,
                'totalOrders' => 0,
                'totalUsers' => 0,
                'recentOrders' => collect([]),
                'lowStockProducts' => collect([]),
                'categoriesWithCounts' => collect([]),
                'revenueData' => ['labels' => [], 'data' => []]
            ]);

        // Act
        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        // Assert
        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
        $response->assertSee('Dashboard Administrativo');
    }

    /** @test */
    public function dashboard_handles_empty_data_gracefully()
    {
        // Arrange
        $emptyData = [
            'totalProducts' => 0,
            'totalCategories' => 0,
            'totalOrders' => 0,
            'totalUsers' => 0,
            'recentOrders' => collect([]),
            'lowStockProducts' => collect([]),
            'categoriesWithCounts' => collect([]),
            'revenueData' => ['labels' => [], 'data' => []]
        ];

        $this->dashboardCacheService
            ->shouldReceive('getDashboardData')
            ->once()
            ->andReturn($emptyData);

        // Act
        $response = $this->controller->index();

        // Assert
        $viewData = $response->getData();
        $this->assertEquals(0, $viewData['totalProducts']);
        $this->assertTrue($viewData['recentOrders']->isEmpty());
        $this->assertTrue($viewData['lowStockProducts']->isEmpty());
    }

    /** @test */
    public function dashboard_variables_are_properly_extracted()
    {
        // Arrange
        $testData = [
            'totalProducts' => 25,
            'totalCategories' => 8,
            'totalOrders' => 100,
            'totalUsers' => 50,
            'recentOrders' => collect(['order1', 'order2']),
            'lowStockProducts' => collect(['product1']),
            'categoriesWithCounts' => collect(['cat1', 'cat2']),
            'revenueData' => ['labels' => ['Jan', 'Feb'], 'data' => [100, 200]]
        ];

        $this->dashboardCacheService
            ->shouldReceive('getDashboardData')
            ->once()
            ->andReturn($testData);

        // Act
        $response = $this->controller->index();
        $viewData = $response->getData();

        // Assert - Verificar se todas as variáveis foram extraídas corretamente
        $expectedKeys = [
            'totalProducts', 'totalCategories', 'totalOrders', 'totalUsers',
            'recentOrders', 'lowStockProducts', 'categoriesWithCounts', 'revenueData'
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $viewData, "Key '{$key}' not found in view data");
        }

        // Verificar valores específicos
        $this->assertEquals(25, $viewData['totalProducts']);
        $this->assertEquals(8, $viewData['totalCategories']);
        $this->assertCount(2, $viewData['revenueData']['labels']);
    }
}
