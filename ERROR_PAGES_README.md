# Система страниц ошибок

Универсальная система для отображения страниц ошибок в Laravel Inertia Vue приложении.

## Компоненты

### ErrorPage.vue
Основной универсальный компонент для отображения любых ошибок:

```vue
<ErrorPage :status="404" />
```

**Пропсы:**
- `status` - HTTP код ошибки (403, 404, 419, 429, 500, 502, 503)

**Примечание:** Тексты ошибок определяются автоматически на основе кода статуса и не могут быть переопределены извне. Это сделано для единообразия интерфейса.

### Особенности дизайна

- **Белый фон** - соответствует дизайну личного кабинета
- **Навигация** - использует компонент PageLayout с навбаром
- **Адаптивность** - корректно отображается на всех устройствах
- **Иконка робота** - для ошибки 404 вместо средней цифры показывается логотип робота
- **Кнопка поддержки** - как в странице логина

## Поддерживаемые ошибки

### 403 - Доступ запрещен
- Заголовок: "Доступ ограничен"
- Описание: "Для доступа к этой странице нужны специальные права"
- Иконка: 🚫

### 404 - Страница не найдена
- Заголовок: "Здесь пусто"
- Описание: "Мы искали эту страницу везде, но она исчезла. Возможно, она никогда и не существовала"
- Иконка: Логотип робота в круге

### 419 - Сессия истекла
- Заголовок: "Время вышло"
- Описание: "Ваша сессия истекла. Обновите страницу или попробуйте позже"
- Иконка: ⏰

### 429 - Слишком много запросов
- Заголовок: "Не торопитесь"
- Описание: "Слишком много запросов за короткое время. Сделайте небольшую паузу"
- Иконка: ⚡

### 500 - Внутренняя ошибка сервера
- Заголовок: "Что-то пошло не так"
- Описание: "Наши роботы уже работают над исправлением этой проблемы"
- Иконка: ⚙️

### 502 - Сервер недоступен
- Заголовок: "Сервер спит"
- Описание: "Сервер временно недоступен. Попробуйте обновить страницу через минуту"
- Иконка: 🔌

### 503 - Сервис недоступен
- Заголовок: "Технические работы"
- Описание: "Мы улучшаем сервис для вас. Вернёмся совсем скоро"
- Иконка: 🔧

## Роуты

### Прямые роуты
```php
Route::get('/errors/403', [ErrorController::class, 'error403'])->name('errors.403');
Route::get('/errors/404', [ErrorController::class, 'error404'])->name('errors.404');
Route::get('/errors/419', [ErrorController::class, 'error419'])->name('errors.419');
Route::get('/errors/429', [ErrorController::class, 'error429'])->name('errors.429');
Route::get('/errors/500', [ErrorController::class, 'error500'])->name('errors.500');
Route::get('/errors/502', [ErrorController::class, 'error502'])->name('errors.502');
Route::get('/errors/503', [ErrorController::class, 'error503'])->name('errors.503');
```

### Тестовые роуты
```php
Route::get('/test/403', function () { abort(403); })->name('test.403');
Route::get('/test/404', function () { abort(404); })->name('test.404');
Route::get('/test/419', function () { throw new \Illuminate\Session\TokenMismatchException(); })->name('test.419');
Route::get('/test/500', function () { throw new \Exception('Тестовая ошибка сервера'); })->name('test.500');
```

## Автоматическая обработка исключений

Система автоматически перехватывает исключения Laravel и отображает соответствующие страницы ошибок:

```php
// bootstrap/app.php
->withExceptions(function (Exceptions $exceptions) {
    // Обработка HTTP исключений
    $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
        if (!$request->expectsJson()) {
            $status = $e->getStatusCode();
            if (in_array($status, [403, 404, 419, 429, 500, 502, 503])) {
                return app(\App\Http\Controllers\ErrorController::class)->showError(
                    $request, $status, $e->getMessage() ?: null
                );
            }
        }
    });
    
    // Обработка 404 ошибок
    $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
        if (!$request->expectsJson()) {
            return app(\App\Http\Controllers\ErrorController::class)->error404($request);
        }
    });
    
    // Обработка 403 ошибок
    $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, $request) {
        if (!$request->expectsJson()) {
            return app(\App\Http\Controllers\ErrorController::class)->error403($request);
        }
    });
});
```

## Использование в контроллерах

### Ручное отображение ошибки
```php
public function someAction()
{
    // Проверка условий
    if (!$user->hasPermission()) {
        return app(\App\Http\Controllers\ErrorController::class)->error403(request());
    }
    
    // Или универсальный метод
    return app(\App\Http\Controllers\ErrorController::class)->showError(request(), 403);
}
```

### Использование abort()
```php
public function someAction()
{
    // Автоматически отобразит страницу ошибки 403
    abort(403, 'Доступ запрещен');
    
    // Автоматически отобразит страницу ошибки 404
    abort(404, 'Ресурс не найден');
}
```

## Кастомизация

### Изменение текстов
Тексты ошибок настраиваются в файле `ErrorPage.vue` в объекте `errorConfig`. Система игнорирует сообщения Laravel и всегда использует собственные тексты:

```javascript
const errorConfig = computed(() => {
    const configs = {
        '404': {
            title: 'Ваш кастомный заголовок',
            description: 'Ваше кастомное описание',
            showRobotIcon: true
        }
    };
    return configs[status] || defaultConfig;
});
```

**Важно:** Система полностью игнорирует стандартные сообщения Laravel и всегда отображает собственные тексты для обеспечения единообразия пользовательского интерфейса.

### Добавление новых ошибок
1. Добавьте конфигурацию в `errorConfig` компонента `ErrorPage.vue`
2. Добавьте метод в `ErrorController.php`
3. Добавьте роут в `routes/web.php`
4. Добавьте обработку в `bootstrap/app.php` (если нужно)

## Стили

Все стили написаны в соответствии с дизайн-системой проекта:
- Шрифты как в личном кабинете
- Цвета соответствуют брендингу
- Адаптивный дизайн для всех устройств
- Анимации и переходы

## Тестирование

Для тестирования используйте тестовые роуты:
- `/test/403` - тест ошибки 403
- `/test/404` - тест ошибки 404
- `/test/419` - тест ошибки 419
- `/test/500` - тест ошибки 500

Или прямые роуты:
- `/errors/403` - прямой доступ к странице ошибки 403
- `/errors/404` - прямой доступ к странице ошибки 404
- И т.д.

## Удаление тестовых роутов

Перед развертыванием в продакшене удалите тестовые роуты из `routes/web.php`:

```php
// Удалить эти строки в продакшене
Route::get('/test/403', function () { abort(403); })->name('test.403');
Route::get('/test/404', function () { abort(404); })->name('test.404');
Route::get('/test/419', function () { throw new \Illuminate\Session\TokenMismatchException(); })->name('test.419');
Route::get('/test/500', function () { throw new \Exception('Тестовая ошибка сервера'); })->name('test.500');
``` 