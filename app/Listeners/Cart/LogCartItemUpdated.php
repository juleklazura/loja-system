<?php

namespace App\Listeners\Cart;

use App\Events\Cart\CartItemUpdated;
use App\Services\AuditService;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogCartItemUpdated implements ShouldQueue
{
    public function __construct(
        private AuditService $auditService
    ) {}

    public function handle(CartItemUpdated $event): void
    {
        $this->auditService->logCartAction('item_updated', [
            'user_id' => $event->user->id,
            'product_id' => $event->cartItem->product_id,
            'old_quantity' => $event->oldQuantity,
            'new_quantity' => $event->newQuantity,
            'cart_item_id' => $event->cartItem->id,
            'product_name' => $event->cartItem->product->name ?? 'Unknown',
            'context' => $event->context
        ]);
    }
}
