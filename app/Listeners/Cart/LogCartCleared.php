<?php

namespace App\Listeners\Cart;

use App\Events\Cart\CartCleared;
use App\Services\AuditService;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogCartCleared implements ShouldQueue
{
    public function __construct(
        private AuditService $auditService
    ) {}

    public function handle(CartCleared $event): void
    {
        $this->auditService->logCartAction('cart_cleared', [
            'user_id' => $event->user->id,
            'items_removed' => $event->itemsRemoved,
            'context' => $event->context
        ]);
    }
}
