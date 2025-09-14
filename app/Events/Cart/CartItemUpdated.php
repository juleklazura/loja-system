<?php

namespace App\Events\Cart;

use App\Models\CartItem;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CartItemUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly CartItem $cartItem,
        public readonly User $user,
        public readonly int $oldQuantity,
        public readonly int $newQuantity,
        public readonly array $context = []
    ) {}
}
