<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InputSanitizationMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $input = $request->all();
        $sanitized = $this->sanitizeInput($input);
        $request->replace($sanitized);

        return $next($request);
    }

    private function sanitizeInput($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }

        if (is_string($data)) {
            $data = trim($data);
            $data = strip_tags($data, '<p><br><strong><em><ul><ol><li>');
            $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return $data;
    }
}
