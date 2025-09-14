<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheService
{
    private const DEFAULT_TTL = 300; // 5 minutos
    private const SHORT_TTL = 60;    // 1 minuto
    private const LONG_TTL = 3600;   // 1 hora

    /**
     * Tags hierárquicas para cache
     */
    private const CACHE_TAGS = [
        'cart' => ['user_data', 'dynamic'],
        'product' => ['catalog', 'static'],
        'category' => ['catalog', 'static'],
        'user' => ['user_data', 'dynamic'],
        'promotion' => ['catalog', 'dynamic'],
        'session' => ['user_data', 'temporary']
    ];

    /**
     * Cache com tags hierárquicas
     */
    public function remember(string $key, int $ttl, callable $callback, array $tags = []): mixed
    {
        try {
            $cacheKey = $this->buildCacheKey($key);
            $cacheTags = $this->resolveTags($tags);

            return Cache::tags($cacheTags)->remember($cacheKey, $ttl, $callback);
        } catch (\Exception $e) {
            Log::channel('performance')->warning('Cache remember failed', [
                'key' => $key,
                'tags' => $tags,
                'error' => $e->getMessage()
            ]);

            // Fallback: executar callback sem cache
            return $callback();
        }
    }

    /**
     * Armazenar no cache com tags
     */
    public function put(string $key, mixed $value, int $ttl = self::DEFAULT_TTL, array $tags = []): bool
    {
        try {
            $cacheKey = $this->buildCacheKey($key);
            $cacheTags = $this->resolveTags($tags);

            return Cache::tags($cacheTags)->put($cacheKey, $value, $ttl);
        } catch (\Exception $e) {
            Log::channel('performance')->error('Cache put failed', [
                'key' => $key,
                'tags' => $tags,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Obter do cache
     */
    public function get(string $key, mixed $default = null): mixed
    {
        try {
            $cacheKey = $this->buildCacheKey($key);
            return Cache::get($cacheKey, $default);
        } catch (\Exception $e) {
            Log::channel('performance')->warning('Cache get failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);

            return $default;
        }
    }

    /**
     * Invalidar cache por tags
     */
    public function invalidateByTags(array $tags): bool
    {
        try {
            $resolvedTags = $this->resolveTags($tags);
            Cache::tags($resolvedTags)->flush();

            Log::channel('performance')->info('Cache invalidated by tags', [
                'tags' => $resolvedTags
            ]);

            return true;
        } catch (\Exception $e) {
            Log::channel('performance')->error('Cache invalidation failed', [
                'tags' => $tags,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Invalidar cache específico
     */
    public function forget(string $key): bool
    {
        try {
            $cacheKey = $this->buildCacheKey($key);
            return Cache::forget($cacheKey);
        } catch (\Exception $e) {
            Log::channel('performance')->error('Cache forget failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Cache para carrinho do usuário
     */
    public function cacheUserCart(int $userId, callable $callback): mixed
    {
        $key = "cart:user:{$userId}";
        $tags = ['cart', "user:{$userId}", 'user_data'];

        return $this->remember($key, self::DEFAULT_TTL, $callback, $tags);
    }

    /**
     * Cache para contagem do carrinho
     */
    public function cacheCartCount(int $userId, callable $callback): int
    {
        $key = "cart:count:user:{$userId}";
        $tags = ['cart', "user:{$userId}", 'user_data'];

        return $this->remember($key, self::SHORT_TTL, $callback, $tags);
    }

    /**
     * Cache para total do carrinho
     */
    public function cacheCartTotal(int $userId, callable $callback): float
    {
        $key = "cart:total:user:{$userId}";
        $tags = ['cart', "user:{$userId}", 'user_data'];

        return $this->remember($key, self::SHORT_TTL, $callback, $tags);
    }

    /**
     * Cache para produtos
     */
    public function cacheProduct(int $productId, callable $callback): mixed
    {
        $key = "product:{$productId}";
        $tags = ['product', 'catalog'];

        return $this->remember($key, self::LONG_TTL, $callback, $tags);
    }

    /**
     * Cache para categorias
     */
    public function cacheCategory(int $categoryId, callable $callback): mixed
    {
        $key = "category:{$categoryId}";
        $tags = ['category', 'catalog'];

        return $this->remember($key, self::LONG_TTL, $callback, $tags);
    }

    /**
     * Invalidar cache do usuário
     */
    public function invalidateUserCache(int $userId): bool
    {
        return $this->invalidateByTags(["user:{$userId}"]);
    }

    /**
     * Invalidar cache do carrinho
     */
    public function invalidateCartCache(int $userId): bool
    {
        return $this->invalidateByTags(['cart', "user:{$userId}"]);
    }

    /**
     * Invalidar cache de produtos
     */
    public function invalidateProductCache(): bool
    {
        return $this->invalidateByTags(['product', 'catalog']);
    }

    /**
     * Construir chave de cache padronizada
     */
    private function buildCacheKey(string $key): string
    {
        $prefix = config('cache.prefix', 'loja_system');
        return "{$prefix}:{$key}";
    }

    /**
     * Resolver tags hierárquicas
     */
    private function resolveTags(array $tags): array
    {
        $resolvedTags = [];

        foreach ($tags as $tag) {
            $resolvedTags[] = $tag;

            // Adicionar tags hierárquicas se existirem
            if (isset(self::CACHE_TAGS[$tag])) {
                $resolvedTags = array_merge($resolvedTags, self::CACHE_TAGS[$tag]);
            }
        }

        return array_unique($resolvedTags);
    }

    /**
     * Obter estatísticas do cache
     */
    public function getStats(): array
    {
        // Implementação básica - pode ser expandida com Redis
        return [
            'driver' => config('cache.default'),
            'prefix' => config('cache.prefix'),
            'status' => 'active'
        ];
    }
}
