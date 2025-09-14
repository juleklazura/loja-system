<?php

return [
    App\Providers\AppServiceProvider::class,
    // App\Providers\ErrorHandlingServiceProvider::class, // Reativado gradualmente
    // App\Providers\PerformanceServiceProvider::class,   // Reativado gradualmente
    App\Providers\DatabaseLoggingServiceProvider::class,  // Seguro para reativar
];
