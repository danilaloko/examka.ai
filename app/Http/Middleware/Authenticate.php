<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        // Получаем current URL как intended URL
        $intendedUrl = $request->fullUrl();
        
        // Формируем URL логина с intended_url параметром
        $loginUrl = route('login');
        
        // Добавляем intended_url только если это не сама страница логина
        if (!$request->is('login') && !str_starts_with($request->path(), 'auto-login/')) {
            $loginUrl .= '?' . http_build_query(['intended_url' => $intendedUrl]);
        }
        
        return $loginUrl;
    }
} 