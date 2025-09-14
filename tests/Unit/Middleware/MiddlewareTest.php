<?php

namespace Tests\Unit\Middleware;

use Tests\TestCase;
use App\Http\Middleware\RequireAuthMiddleware;
use App\Http\Middleware\CartRateLimitMiddleware;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class MiddlewareTest extends TestCase
{
    /** @test */
    public function require_auth_middleware_allows_authenticated_users()
    {
        // Arrange
        $middleware = new RequireAuthMiddleware();
        $user = User::factory()->create();
        $request = Request::create('/test', 'GET');
        
        Auth::login($user);

        // Act
        $response = $middleware->handle($request, function ($req) {
            return new Response('Success');
        });

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Success', $response->getContent());
    }

    /** @test */
    public function require_auth_middleware_redirects_unauthenticated_web_requests()
    {
        // Arrange
        $middleware = new RequireAuthMiddleware();
        $request = Request::create('/test', 'GET');

        // Act
        $response = $middleware->handle($request, function ($req) {
            return new Response('Should not reach here');
        });

        // Assert
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('login', $response->headers->get('Location'));
    }

    /** @test */
    public function require_auth_middleware_returns_json_for_api_requests()
    {
        // Arrange
        $middleware = new RequireAuthMiddleware();
        $request = Request::create('/api/test', 'GET');
        $request->headers->set('Accept', 'application/json');

        // Act
        $response = $middleware->handle($request, function ($req) {
            return new Response('Should not reach here');
        });

        // Assert
        $this->assertEquals(401, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertFalse($content['success']);
        $this->assertStringContainsString('Acesso negado', $content['message']);
        $this->assertArrayHasKey('redirect_to', $content);
    }

    /** @test */
    public function require_auth_middleware_returns_json_for_expects_json_requests()
    {
        // Arrange
        $middleware = new RequireAuthMiddleware();
        $request = Request::create('/test', 'GET');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        // Act
        $response = $middleware->handle($request, function ($req) {
            return new Response('Should not reach here');
        });

        // Assert
        $this->assertEquals(401, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertFalse($content['success']);
    }

    /** @test */
    public function cart_rate_limit_middleware_allows_requests_within_limit()
    {
        // Arrange
        $middleware = new CartRateLimitMiddleware();
        $user = $this->createAuthenticatedUser();
        $request = Request::create('/cart/add', 'POST');

        RateLimiter::clear("cart_rate_limit:{$user->id}:add");

        // Act
        $response = $middleware->handle($request, function ($req) {
            return new Response('Success');
        }, 'add');

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Success', $response->getContent());
    }

    /** @test */
    public function cart_rate_limit_middleware_blocks_excessive_requests()
    {
        // Arrange
        $middleware = new CartRateLimitMiddleware();
        $user = $this->createAuthenticatedUser();
        $request = Request::create('/cart/add', 'POST');

        $key = "cart_rate_limit:{$user->id}:add";
        
        // Simular que já atingiu o limite
        for ($i = 0; $i < 15; $i++) {
            RateLimiter::hit($key, 60);
        }

        // Act
        $response = $middleware->handle($request, function ($req) {
            return new Response('Should not reach here');
        }, 'add');

        // Assert
        $this->assertEquals(429, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertFalse($content['success']);
        $this->assertStringContainsString('Muitas tentativas', $content['message']);
        $this->assertArrayHasKey('retry_after', $content);
    }

    /** @test */
    public function cart_rate_limit_middleware_returns_unauthorized_for_unauthenticated_users()
    {
        // Arrange
        $middleware = new CartRateLimitMiddleware();
        $request = Request::create('/cart/add', 'POST');

        // Act
        $response = $middleware->handle($request, function ($req) {
            return new Response('Should not reach here');
        }, 'add');

        // Assert
        $this->assertEquals(401, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertFalse($content['success']);
        $this->assertStringContainsString('não autenticado', $content['message']);
    }

    /** @test */
    public function cart_rate_limit_middleware_uses_correct_limits_for_operations()
    {
        // Arrange
        $middleware = new CartRateLimitMiddleware();
        $user = $this->createAuthenticatedUser();

        // Test different operations
        $operations = [
            'add' => 10,
            'update' => 20,
            'remove' => 15,
            'general' => 30
        ];

        foreach ($operations as $operation => $expectedLimit) {
            $request = Request::create("/cart/{$operation}", 'POST');
            $key = "cart_rate_limit:{$user->id}:{$operation}";
            
            RateLimiter::clear($key);

            // Hit the limit exactly
            for ($i = 0; $i < $expectedLimit; $i++) {
                $response = $middleware->handle($request, function ($req) {
                    return new Response('Success');
                }, $operation);
                
                $this->assertEquals(200, $response->getStatusCode(), "Failed at attempt {$i} for {$operation}");
            }

            // The next request should be rate limited
            $response = $middleware->handle($request, function ($req) {
                return new Response('Should not reach here');
            }, $operation);

            $this->assertEquals(429, $response->getStatusCode(), "Rate limit not applied for {$operation}");
        }
    }

    /** @test */
    public function cart_rate_limit_middleware_clears_limit_on_successful_non_add_operations()
    {
        // Arrange
        $middleware = new CartRateLimitMiddleware();
        $user = $this->createAuthenticatedUser();
        $request = Request::create('/cart/update', 'PUT');
        
        $key = "cart_rate_limit:{$user->id}:update";
        
        // Add some attempts
        RateLimiter::hit($key, 60);
        RateLimiter::hit($key, 60);
        
        $initialAttempts = RateLimiter::attempts($key);
        $this->assertGreaterThan(0, $initialAttempts);

        // Act - Successful request
        $response = $middleware->handle($request, function ($req) {
            return new Response('Success', 200);
        }, 'update');

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        
        // Para operações que não são 'add', o limite deve ser limpo
        $finalAttempts = RateLimiter::attempts($key);
        $this->assertEquals(0, $finalAttempts);
    }

    /** @test */
    public function cart_rate_limit_middleware_preserves_limit_for_add_operations()
    {
        // Arrange
        $middleware = new CartRateLimitMiddleware();
        $user = $this->createAuthenticatedUser();
        $request = Request::create('/cart/add', 'POST');
        
        $key = "cart_rate_limit:{$user->id}:add";
        
        // Add some attempts
        RateLimiter::hit($key, 60);
        RateLimiter::hit($key, 60);
        
        $initialAttempts = RateLimiter::attempts($key);
        $this->assertGreaterThan(0, $initialAttempts);

        // Act - Successful request
        $response = $middleware->handle($request, function ($req) {
            return new Response('Success', 200);
        }, 'add');

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        
        // Para operações 'add', o limite deve ser mantido
        $finalAttempts = RateLimiter::attempts($key);
        $this->assertEquals($initialAttempts + 1, $finalAttempts); // +1 pela requisição atual
    }
}
