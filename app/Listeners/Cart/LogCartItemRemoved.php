<?php

namespace App\Listeners\Cart;

use App\Events\Cart\CartItemRemoved;
use App\Services\AuditService;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogCartItemRemoved implements ShouldQueue
{
    public function __construct(
        private AuditService $auditService
    ) {}

    public function handle(CartItemRemoved $event): void
    {
        $this->auditService->logCartAction('item_removed', [
            'user_id' => $event->user->id,
            'product_id' => $event->productId,
            'removed_quantity' => $event->removedQuantity,
            'context' => $event->context
        ]);
    }
}
