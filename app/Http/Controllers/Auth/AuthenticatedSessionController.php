<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): Response
    {
        // Получаем intended URL из query параметров
        $intendedUrl = $request->query('intended_url');
        
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
            'intendedUrl' => $intendedUrl
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Получаем intended URL из разных источников
        $intendedUrl = $this->getIntendedUrl($request);
        
        if ($intendedUrl && $intendedUrl !== '/lk') {
            return redirect($intendedUrl);
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Получить intended URL из различных источников
     */
    private function getIntendedUrl(Request $request): ?string
    {
        // 1. Проверяем intended URL, переданный через форму (из localStorage на фронтенде)
        $intendedFromForm = $request->input('intended_url');
        
        // 2. Проверяем referer заголовок, если пришли с login страницы с параметрами
        $referer = $request->header('referer');
        if ($referer) {
            $refererQuery = parse_url($referer, PHP_URL_QUERY);
            if ($refererQuery) {
                parse_str($refererQuery, $queryParams);
                $intendedFromReferer = $queryParams['intended_url'] ?? null;
            }
        }
        
        // 3. Проверяем сессию Laravel (стандартный intended)
        $intendedFromSession = $request->session()->get('url.intended');
        
        // Приоритет: форма -> referer -> сессия
        $intendedUrl = $intendedFromForm ?? $intendedFromReferer ?? $intendedFromSession;
        
        if ($intendedUrl) {
            // Валидация intended URL для безопасности
            return $this->validateIntendedUrl($intendedUrl);
        }
        
        return null;
    }

    /**
     * Валидировать intended URL для безопасности
     */
    private function validateIntendedUrl(string $url): ?string
    {
        // Список разрешенных маршрутов для безопасности
        $allowedRoutes = ['/lk', '/new', '/documents', '/profile', '/dashboard'];
        
        // Парсим URL чтобы получить только path и query
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '';
        $query = $parsedUrl['query'] ?? '';
        
        // Проверяем, что path начинается с одного из разрешенных маршрутов
        foreach ($allowedRoutes as $allowedRoute) {
            if (str_starts_with($path, $allowedRoute)) {
                // Возвращаем только path + query без домена
                return $path . ($query ? '?' . $query : '');
            }
        }
        
        return null; // URL не прошел валидацию
    }
}
