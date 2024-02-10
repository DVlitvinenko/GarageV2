<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Park;

class CheckApiKey
{
    public function handle($request, Closure $next)
    {
        $apiKey = $request->header('X-API-Key');

        // Проверка ключа авторизации
        $park = Park::where('API_key', $apiKey)->first();
        if (!$park) {
            return response()->json(['message' => 'Неверный ключ авторизации'], 401);
        }

        return $next($request);
    }
}
