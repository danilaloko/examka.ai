<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Auth;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
        ];
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, \Closure $next)
    {
        // Проверяем, нужна ли аутентификация для этого маршрута
        if ($this->shouldRedirectToLogin($request)) {
            $intendedUrl = $request->fullUrl();
            
            // Не сохраняем URL логина как intended
            if (!$request->is('login') && !str_starts_with($request->path(), 'auto-login/')) {
                return redirect()->route('login', ['intended_url' => $intendedUrl]);
            }
        }

        return parent::handle($request, $next);
    }

    /**
     * Проверить, нужно ли перенаправить на страницу логина
     */
    private function shouldRedirectToLogin(Request $request): bool
    {
        // Если пользователь уже авторизован, перенаправление не нужно
        if (Auth::check()) {
            return false;
        }

        // Проверяем, требует ли маршрут аутентификации
        $protectedRoutes = ['/lk', '/new', '/documents', '/profile', '/dashboard'];
        
        foreach ($protectedRoutes as $route) {
            if (str_starts_with($request->path(), ltrim($route, '/'))) {
                return true;
            }
        }

        return false;
    }
}
