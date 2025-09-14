<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\CacheService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Cache Service Tests - Corrigido com assinaturas reais
 */
class CacheServiceCorrectTest extends TestCase
{
    private CacheService $cacheService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheService = app(CacheService::class);
    }

    /** @test */
    public function can_remember_with_basic_key(): void
    {
        $key = 'test_key';
        $ttl = 3600;
        $expectedValue = 'test_value';
        
        // Mock the Cache::tags()->remember() chain
        $taggedCache = \Mockery::mock();
        
        Cache::shouldReceive('tags')
            ->once()
            ->with(\Mockery::type('array'))
            ->andReturn($taggedCache);
            
        $taggedCache->shouldReceive('remember')
            ->once()
            ->with(\Mockery::type('string'), $ttl, \Mockery::type('callable'))
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });
            
        $result = $this->cacheService->remember($key, $ttl, fn() => $expectedValue);
        
        $this->assertEquals($expectedValue, $result);
    }

    /** @test */
    public function can_remember_with_custom_tags(): void
    {
        $key = 'tagged_key';
        $ttl = 1800;
        $tags = ['custom', 'test'];
        $expectedValue = 'tagged_value';
        
        $taggedCache = \Mockery::mock();
        
        Cache::shouldReceive('tags')
            ->once()
            ->with(\Mockery::type('array'))
            ->andReturn($taggedCache);
            
        $taggedCache->shouldReceive('remember')
            ->once()
            ->with(\Mockery::type('string'), $ttl, \Mockery::type('callable'))
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });
            
        $result = $this->cacheService->remember($key, $ttl, fn() => $expectedValue, $tags);
        
        $this->assertEquals($expectedValue, $result);
    }

    /** @test */
    public function can_put_value_with_tags(): void
    {
        $key = 'put_key';
        $value = 'put_value';
        $ttl = 7200;
        $tags = ['test'];
        
        $taggedCache = \Mockery::mock();
        
        Cache::shouldReceive('tags')
            ->once()
            ->with(\Mockery::type('array'))
            ->andReturn($taggedCache);
            
        $taggedCache->shouldReceive('put')
            ->once()
            ->with(\Mockery::type('string'), $value, $ttl)
            ->andReturn(true);
            
        $result = $this->cacheService->put($key, $value, $ttl, $tags);
        
        $this->assertTrue($result);
    }

    /** @test */
    public function can_get_value(): void
    {
        $key = 'get_key';
        $expectedValue = 'cached_value';
        
        Cache::shouldReceive('get')
            ->once()
            ->with(\Mockery::type('string'), null)
            ->andReturn($expectedValue);
            
        $result = $this->cacheService->get($key);
        
        $this->assertEquals($expectedValue, $result);
    }

    /** @test */
    public function can_invalidate_by_tags(): void
    {
        $tags = ['products', 'inventory'];
        
        $taggedCache = \Mockery::mock();
        
        Cache::shouldReceive('tags')
            ->once()
            ->with(\Mockery::type('array'))
            ->andReturn($taggedCache);
            
        $taggedCache->shouldReceive('flush')
            ->once()
            ->andReturn(true);
            
        $result = $this->cacheService->invalidateByTags($tags);
        
        $this->assertTrue($result);
    }

    /** @test */
    public function handles_cache_failure_gracefully(): void
    {
        Log::shouldReceive('channel')
            ->with('performance')
            ->andReturnSelf();
            
        Log::shouldReceive('warning')
            ->once()
            ->with('Cache remember failed', \Mockery::type('array'));
        
        Cache::shouldReceive('tags')
            ->once()
            ->andThrow(new \Exception('Cache error'));
            
        $fallbackValue = 'fallback';
        $result = $this->cacheService->remember('fail_key', 3600, fn() => $fallbackValue);
        
        $this->assertEquals($fallbackValue, $result);
    }

    /** @test */
    public function can_cache_user_cart(): void
    {
        $userId = 123;
        $cartData = ['items' => [], 'total' => 0];
        
        $taggedCache = \Mockery::mock();
        
        Cache::shouldReceive('tags')
            ->once()
            ->with(\Mockery::type('array'))
            ->andReturn($taggedCache);
            
        $taggedCache->shouldReceive('remember')
            ->once()
            ->with(\Mockery::type('string'), \Mockery::type('int'), \Mockery::type('callable'))
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });
            
        $result = $this->cacheService->cacheUserCart($userId, fn() => $cartData);
        
        $this->assertEquals($cartData, $result);
    }

    /** @test */
    public function can_cache_cart_count(): void
    {
        $userId = 456;
        $count = 5;
        
        $taggedCache = \Mockery::mock();
        
        Cache::shouldReceive('tags')
            ->once()
            ->with(\Mockery::type('array'))
            ->andReturn($taggedCache);
            
        $taggedCache->shouldReceive('remember')
            ->once()
            ->with(\Mockery::type('string'), \Mockery::type('int'), \Mockery::type('callable'))
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });
            
        $result = $this->cacheService->cacheCartCount($userId, fn() => $count);
        
        $this->assertEquals($count, $result);
    }

    /** @test */
    public function can_cache_cart_total(): void
    {
        $userId = 789;
        $total = 99.99;
        
        $taggedCache = \Mockery::mock();
        
        Cache::shouldReceive('tags')
            ->once()
            ->with(\Mockery::type('array'))
            ->andReturn($taggedCache);
            
        $taggedCache->shouldReceive('remember')
            ->once()
            ->with(\Mockery::type('string'), \Mockery::type('int'), \Mockery::type('callable'))
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });
            
        $result = $this->cacheService->cacheCartTotal($userId, fn() => $total);
        
        $this->assertEquals($total, $result);
    }

    /** @test */
    public function can_invalidate_user_cache(): void
    {
        $userId = 111;
        
        $taggedCache = \Mockery::mock();
        
        Cache::shouldReceive('tags')
            ->once()
            ->with(\Mockery::type('array'))
            ->andReturn($taggedCache);
            
        $taggedCache->shouldReceive('flush')
            ->once()
            ->andReturn(true);
            
        $result = $this->cacheService->invalidateUserCache($userId);
        
        $this->assertTrue($result);
    }

    /** @test */
    public function can_invalidate_cart_cache(): void
    {
        $userId = 222;
        
        $taggedCache = \Mockery::mock();
        
        Cache::shouldReceive('tags')
            ->once()
            ->with(\Mockery::type('array'))
            ->andReturn($taggedCache);
            
        $taggedCache->shouldReceive('flush')
            ->once()
            ->andReturn(true);
            
        $result = $this->cacheService->invalidateCartCache($userId);
        
        $this->assertTrue($result);
    }

    /** @test */
    public function forget_removes_cache_key(): void
    {
        $key = 'forget_key';
        
        Cache::shouldReceive('forget')
            ->once()
            ->with(\Mockery::type('string'))
            ->andReturn(true);
            
        $result = $this->cacheService->forget($key);
        
        $this->assertTrue($result);
    }
}
