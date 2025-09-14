<?php

namespace App\Exceptions\Auth;

class UnauthorizedAccessException extends AuthException
{
    protected $statusCode = 403;

    public function __construct(string $resource = '')
    {
        $message = $resource 
            ? "Acesso negado ao recurso: {$resource}"
            : 'Você não tem permissão para acessar este recurso';
            
        parent::__construct($message, 2001);
    }
}
