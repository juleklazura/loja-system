<?php

namespace App\DTOs\Cart;

class CartResponseDTO
{
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly int $cartCount,
        public readonly ?int $itemId = null,
        public readonly ?float $totalPrice = null,
        public readonly ?array $errors = null
    ) {}

    public static function success(string $message, int $cartCount, ?int $itemId = null, ?float $totalPrice = null): self
    {
        return new self(
            success: true,
            message: $message,
            cartCount: $cartCount,
            itemId: $itemId,
            totalPrice: $totalPrice
        );
    }

    public static function error(string $message, ?array $errors = null): self
    {
        return new self(
            success: false,
            message: $message,
            cartCount: 0,
            errors: $errors
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'success' => $this->success,
            'message' => $this->message,
            'cart_count' => $this->cartCount,
            'item_id' => $this->itemId,
            'total_price' => $this->totalPrice,
            'errors' => $this->errors,
        ], fn($value) => $value !== null);
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
