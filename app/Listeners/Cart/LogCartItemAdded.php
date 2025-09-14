<?php

namespace App\Listeners\Cart;

use App\Events\Cart\CartItemAdded;
use App\Services\AuditService;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogCartItemAdded implements ShouldQueue
{
    public function __construct(
        private AuditService $auditService
    ) {}

    public function handle(CartItemAdded $event): void
    {
        $this->auditService->logCartAction('item_added', [
            'user_id' => $event->user->id,
            'product_id' => $event->cartItem->product_id,
            'quantity' => $event->quantity,
            'cart_item_id' => $event->cartItem->id,
            'product_name' => $event->cartItem->product->name ?? 'Unknown',
            'product_price' => $event->cartItem->product->price ?? 0,
            'context' => $event->context
        ]);
    }
}
