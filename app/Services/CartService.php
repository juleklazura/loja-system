<?php

namespace App\Services;

use App\Contracts\CartRepositoryInterface;
use App\DTOs\Cart\AddToCartDTO;
use App\DTOs\Cart\CartResponseDTO;
use App\Models\Product;
use App\Models\User;
use App\Models\CartItem;
use App\Exceptions\Cart\CartException;
use App\Exceptions\Product\ProductException;
use App\Exceptions\Cart\InvalidQuantityException;
use App\Exceptions\Cart\ProductNotAvailableException;
use App\Exceptions\Product\ProductNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CartService
{
    private const CACHE_TTL = 300; // 5 minutos
    private const MAX_QUANTITY = 99;

    private CartRepositoryInterface $cartRepository;
    private AuditService $auditService;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        AuditService $auditService
    ) {
        $this->cartRepository = $cartRepository;
        $this->auditService = $auditService;
    }

    /**
     * Adicionar item ao carrinho com validações centralizadas
     */
    public function addItem(User $user, Product $product, int $quantity): array
    {
        $this->validateProduct($product);
        $this->validateQuantity($quantity);
        $this->validateStock($product, $quantity);

        return DB::transaction(function() use ($user, $product, $quantity) {
            $cartItem = $this->findExistingCartItem($user, $product);

            if ($cartItem) {
                $newQuantity = $cartItem->quantity + $quantity;
                $this->validateStock($product, $newQuantity);
                $this->cartRepository->updateCartItem($cartItem, ['quantity' => $newQuantity]);
            } else {
                $cartItem = $this->cartRepository->createCartItem([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity
                ]);
            }

            $this->auditService->logCartAction('product_added', [
                'user_id' => $user->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'cart_item_id' => $cartItem->id
            ]);

            // Disparar evento
            event(new \App\Events\Cart\CartItemAdded($cartItem, $user, $quantity, [
                'source' => 'web',
                'ip' => request()->ip()
            ]));

            return [
                'success' => true,
                'message' => 'Produto adicionado ao carrinho',
                'cart_count' => $this->getCartCount($user),
                'item_id' => $cartItem->id
            ];
        });
    }

    /**
     * Adicionar item ao carrinho usando DTO
     */
    public function addToCart(AddToCartDTO $dto): CartResponseDTO
    {
        try {
            $user = User::find($dto->userId);
            $product = Product::find($dto->productId);
            
            if (!$user || !$product) {
                return CartResponseDTO::error('Usuário ou produto não encontrado');
            }

            $result = $this->addItem($user, $product, $dto->quantity);

            return CartResponseDTO::success(
                $result['message'],
                $result['cart_count'],
                $result['item_id'],
                $this->cartRepository->getCartTotal($dto->userId)
            );

        } catch (\Exception $e) {
            Log::channel('cart')->error('Erro ao adicionar produto ao carrinho', [
                'user_id' => $dto->userId,
                'product_id' => $dto->productId,
                'error' => $e->getMessage()
            ]);

            return CartResponseDTO::error($e->getMessage());
        }
    }

    /**
     * Atualizar quantidade do item
     */
    public function updateQuantity(CartItem $cartItem, int $quantity): array
    {
        $this->validateQuantity($quantity);
        $this->validateStock($cartItem->product, $quantity);

        $this->cartRepository->updateCartItem($cartItem, ['quantity' => $quantity]);

        $this->auditService->logCartAction('quantity_updated', [
            'user_id' => $cartItem->user_id,
            'product_id' => $cartItem->product_id,
            'old_quantity' => $cartItem->getOriginal('quantity'),
            'new_quantity' => $quantity
        ]);

        // Disparar evento
        event(new \App\Events\Cart\CartItemUpdated(
            $cartItem, 
            $cartItem->user, 
            $cartItem->getOriginal('quantity'), 
            $quantity,
            ['source' => 'web', 'ip' => request()->ip()]
        ));

        return [
            'success' => true,
            'message' => 'Quantidade atualizada',
            'cart_count' => $this->getCartCount($cartItem->user),
            'total_price' => $cartItem->fresh()->total_price ?? 0
        ];
    }

    /**
     * Remover item do carrinho
     */
    public function removeItem(CartItem $cartItem): array
    {
        $user = $cartItem->user;
        $this->cartRepository->deleteCartItem($cartItem);

        $this->auditService->logCartAction('product_removed', [
            'user_id' => $user->id,
            'product_id' => $cartItem->product_id,
            'quantity_removed' => $cartItem->quantity
        ]);

        // Disparar evento
        event(new \App\Events\Cart\CartItemRemoved(
            $cartItem->product_id, 
            $user, 
            $cartItem->quantity,
            ['source' => 'web', 'ip' => request()->ip()]
        ));

        return [
            'success' => true,
            'message' => 'Item removido do carrinho',
            'cart_count' => $this->getCartCount($user)
        ];
    }

    /**
     * Obter itens do carrinho com cache
     */
    public function getCartItems(User $user): Collection
    {
        return $this->cartRepository->getCartItemsByUser($user->id);
    }

    /**
     * Calcular total do carrinho baseado em collection
     */
    public function calculateCartTotal(Collection $cartItems): float
    {
        return $cartItems->sum(function ($item) {
            return $item->product->effective_price * $item->quantity;
        });
    }

    /**
     * Obter total do carrinho via repository
     */
    public function getCartTotal(User $user): float
    {
        return $this->cartRepository->getCartTotal($user->id);
    }

    /**
     * Obter contagem de itens do carrinho
     */
    public function getCartCount(User $user): int
    {
        return $this->cartRepository->getCartCount($user->id);
    }

    /**
     * Limpar carrinho do usuário
     */
    public function clearCart(User $user): void
    {
        $this->cartRepository->clearUserCart($user->id);

        $this->auditService->logCartAction('cart_cleared', [
            'user_id' => $user->id
        ]);

        // Disparar evento
        $cartItems = $this->cartRepository->getCartItemsByUser($user->id);
        event(new \App\Events\Cart\CartCleared(
            $user, 
            $cartItems->count(),
            ['source' => 'web', 'ip' => request()->ip()]
        ));
    }

    // ====== MÉTODOS PRIVADOS DE VALIDAÇÃO ======

    /**
     * Validar se produto está ativo e disponível
     */
    private function validateProduct(Product $product): void
    {
        if (!$product->is_active && !$product->active) {
            throw new ProductNotAvailableException($product->name, 0);
        }
    }

    /**
     * Validar quantidade solicitada
     */
    private function validateQuantity(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new InvalidQuantityException($quantity);
        }

        if ($quantity > self::MAX_QUANTITY) {
            throw new InvalidQuantityException($quantity, self::MAX_QUANTITY);
        }
    }

    /**
     * Validar estoque disponível
     */
    private function validateStock(Product $product, int $requestedQuantity): void
    {
        if ($product->stock_quantity < $requestedQuantity) {
            throw new ProductNotAvailableException($product->name, $product->stock_quantity);
        }
    }

    /**
     * Encontrar item existente no carrinho
     */
    private function findExistingCartItem(User $user, Product $product): ?CartItem
    {
        return $this->cartRepository->findCartItem($user->id, $product->id);
    }
}
