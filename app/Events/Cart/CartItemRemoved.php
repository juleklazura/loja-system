<?php

namespace App\Events\Cart;

use App\Models\CartItem;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CartItemRemoved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $productId,
        public readonly User $user,
        public readonly int $removedQuantity,
        public readonly array $context = []
    ) {}
}
