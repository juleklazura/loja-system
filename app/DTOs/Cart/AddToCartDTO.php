<?php

namespace App\DTOs\Cart;

use App\Http\Requests\CartAddRequest;
use Illuminate\Support\Facades\Auth;

class AddToCartDTO
{
    public function __construct(
        public readonly int $productId,
        public readonly int $quantity,
        public readonly int $userId
    ) {}

    public static function fromRequest(CartAddRequest $request): self
    {
        $validated = $request->validated();
        
        return new self(
            productId: $validated['product_id'],
            quantity: $validated['quantity'],
            userId: Auth::id()
        );
    }

    public function toArray(): array
    {
        return [
            'product_id' => $this->productId,
            'quantity' => $this->quantity,
            'user_id' => $this->userId,
        ];
    }
}
