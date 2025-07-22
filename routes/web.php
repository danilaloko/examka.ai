<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LkController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentGenerationController;
use App\Http\Controllers\NewDocumentController;
use App\Http\Controllers\FilesController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentTestController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\YookassaWebhookController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\TelegramLinkController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('v5');
});
Route::get('/v2', function () {
    return view('v2');
});
Route::get('/v3', function () {
    return view('v3');
});

// Временные маршруты для просмотра дизайнов главной страницы
Route::get('/design1', function () {
    return view('v4-design1');
})->name('design1');

Route::get('/design2', function () {
    return view('v4-design2');
})->name('design2');

Route::get('/design3', function () {
    return view('v4-design3');
})->name('design3');

Route::get('/design4', function () {
    return view('v4-design4');
})->name('design4');

Route::get('/design5', function () {
    return view('v4-design5');
})->name('design5');

// API роут для получения IP адреса пользователя
Route::get('/api/user-ip', function () {
    $ip = request()->ip();
    
    // Попытка получить реальный IP через заголовки
    $realIp = request()->header('X-Forwarded-For') 
        ?? request()->header('X-Real-IP') 
        ?? request()->header('CF-Connecting-IP') 
        ?? $ip;
    
    // Если IP содержит несколько адресов через запятую, берем первый
    if (strpos($realIp, ',') !== false) {
        $realIp = trim(explode(',', $realIp)[0]);
    }
    
    return response()->json([
        'ip' => $realIp
    ]);
})->name('user-ip');

Route::get('/dashboard', [LkController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/lk', [LkController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('lk');

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Страница создания документа
    Route::get('/new', NewDocumentController::class)->name('documents.new');

    // Группа маршрутов для документов
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::post('/', [DocumentController::class, 'quickCreate'])->name('quick-create');
        Route::get('/', [DocumentController::class, 'index'])->name('index');
        Route::get('/{document}', [DocumentController::class, 'show'])->name('show');
        Route::get('/{document}/status', [DocumentController::class, 'checkStatus'])->name('status');
        Route::get('/{document}/edit', [DocumentController::class, 'edit'])->name('edit');
        Route::put('/{document}', [DocumentController::class, 'update'])->name('update');
        Route::delete('/{document}', [DocumentController::class, 'destroy'])->name('destroy');
        Route::post('/{document}/download-word', [DocumentController::class, 'downloadWord'])->name('download-word');
        
        // Маршруты для редактирования отдельных частей структуры
        Route::patch('/{document}/topic', [DocumentController::class, 'updateTopic'])->name('update-topic');
        Route::patch('/{document}/title', [DocumentController::class, 'updateTitle'])->name('update-title');
        Route::patch('/{document}/document-title', [DocumentController::class, 'updateDocumentTitle'])->name('update-document-title');
        Route::patch('/{document}/description', [DocumentController::class, 'updateDescription'])->name('update-description');
        Route::patch('/{document}/objectives', [DocumentController::class, 'updateObjectives'])->name('update-objectives');
        Route::patch('/{document}/theses', [DocumentController::class, 'updateTheses'])->name('update-theses');
        Route::patch('/{document}/contents', [DocumentController::class, 'updateContents'])->name('update-contents');
        
        // Маршруты для управления генерацией
        Route::post('/{document}/start-generation', [DocumentController::class, 'startGeneration'])->name('start-generation');
        Route::delete('/{document}/generation-jobs', [DocumentController::class, 'deleteGenerationJobs'])->name('delete-generation-jobs');
        
        // Маршруты для полной генерации
        Route::post('/{document}/generate-full', [DocumentGenerationController::class, 'startFullGeneration'])->name('generate-full');
        Route::get('/{document}/generation-progress', [DocumentGenerationController::class, 'getGenerationProgress'])->name('generation-progress');
    });

    // Маршруты для работы с файлами
    Route::get('/files/example', function () {
        return Inertia::render('files/FileExample');
    })->name('files.example')->middleware(['auth', 'web']);

    Route::post('/files/upload', [FilesController::class, 'upload'])->name('files.upload')->middleware(['auth', 'web']);
    Route::get('/files/{file}', [FilesController::class, 'show'])->name('files.show')->middleware(['auth', 'web']);
    Route::get('/files/{file}/download', [FilesController::class, 'download'])->name('files.download')->middleware(['auth', 'web']);
    Route::get('/files/{file}/view', [FilesController::class, 'view'])->name('files.view')->middleware(['auth', 'web']);
    Route::put('/files/{file}', [FilesController::class, 'update'])->name('files.update')->middleware(['auth', 'web']);
    Route::delete('/files/{file}', [FilesController::class, 'destroy'])->name('files.destroy')->middleware(['auth', 'web']);

    // Маршруты для заказов
    Route::post('/orders/{document}/process', [OrderController::class, 'processOrder'])->name('orders.process');
    // Универсальный маршрут для заказа без документа
    Route::post('/orders/process', [OrderController::class, 'processOrder'])->name('orders.process-without-document');

    // Управление веб-хуками и связка с Telegram (авторизованные пользователи)
    Route::prefix('telegram')->group(function () {
        // Телеграм веб-хуки (для админов)
        Route::post('/set-webhook', [TelegramController::class, 'setWebhook'])->name('telegram.set-webhook');
        Route::post('/delete-webhook', [TelegramController::class, 'deleteWebhook'])->name('telegram.delete-webhook');
        Route::get('/me', [TelegramController::class, 'getMe'])->name('telegram.me');
        
        // Связка с Telegram
        Route::post('/link', [TelegramLinkController::class, 'generateLink'])->name('telegram.link');
        Route::post('/auth-link', [TelegramLinkController::class, 'generateAuthLink'])->name('telegram.auth-link');
        Route::post('/unlink', [TelegramLinkController::class, 'unlink'])->name('telegram.unlink');
        Route::get('/status', [TelegramLinkController::class, 'status'])->name('telegram.status');
    })->middleware(['auth', 'verified']);

    // Новые маршруты для ЮКасса
    Route::post('/payment/yookassa/create/{orderId}', [PaymentController::class, 'createYookassaPayment'])
        ->name('payment.yookassa.create');

    // Роут для создания тестового заказа
    Route::post('/payment/test/create-order', [PaymentTestController::class, 'createTestOrder'])
        ->name('payment.test.create-order');
});

// Webhook для ЮКасса (без middleware auth)
Route::post('/payment/yookassa/webhook', [YookassaWebhookController::class, 'handleWebhook'])
    ->name('payment.yookassa.webhook');

// Маршруты для автоматической авторизации
Route::post('/login/auto', [App\Http\Controllers\Auth\AutoAuthController::class, 'autoLogin'])->name('login.auto');
Route::post('/register/auto', [App\Http\Controllers\Auth\AutoAuthController::class, 'autoRegister'])->name('register.auto');
Route::get('/logout', [App\Http\Controllers\Auth\AutoAuthController::class, 'logout'])->name('logout.get');
Route::get('/auto-login/{auth_token}', [App\Http\Controllers\Auth\AutoAuthController::class, 'autoLoginByToken'])->name('auto.login');

// Специальный маршрут для Telegram WebApp авторизации
Route::post('/telegram/auth', [App\Http\Controllers\Auth\AutoAuthController::class, 'telegramAuth'])->name('telegram.webapp.auth');

// Маршруты для платежей
Route::get('/payment/complete/{orderId}', [PaymentController::class, 'handlePaymentComplete'])
    ->name('payment.complete');

// Тестовая страница оплаты
Route::get('/payment/test', [PaymentTestController::class, 'show'])->name('payment.test');

// Тестовая страница ожидания оплаты (для просмотра дизайна)
Route::get('/payment/waiting-test', function () {
    return Inertia::render('payment/PaymentWaiting', [
        'orderId' => 12345,
        'orderInfo' => [
            'id' => 12345,
            'amount' => 500
        ],
        'isDocument' => true,
        'documentId' => 67890
    ]);
})->name('payment.waiting.test');

// Временный роут для отладки (удалить после решения проблемы)
Route::get('/debug/telegram-config', function () {
    return response()->json([
        'use_proxy' => config('services.telegram.use_proxy'),
        'proxy_url' => config('services.telegram.proxy_url') ? 'configured' : 'not configured',
        'bot_token' => config('services.telegram.bot_token') ? 'configured' : 'not configured',
        'env_proxy' => env('TELEGRAM_USE_PROXY'),
        'env_proxy_url' => env('TELEGRAM_PROXY_URL') ? 'configured' : 'not configured',
    ]);
})->name('debug.telegram.config');

// Telegram бот роуты (веб-хук должен быть без auth middleware)
Route::post('/telegram/webhook', [TelegramController::class, 'webhook'])->name('telegram.webhook');

// Роуты для страниц ошибок
Route::get('/errors/403', [App\Http\Controllers\ErrorController::class, 'error403'])->name('errors.403');
Route::get('/errors/404', [App\Http\Controllers\ErrorController::class, 'error404'])->name('errors.404');
Route::get('/errors/419', [App\Http\Controllers\ErrorController::class, 'error419'])->name('errors.419');
Route::get('/errors/429', [App\Http\Controllers\ErrorController::class, 'error429'])->name('errors.429');
Route::get('/errors/500', [App\Http\Controllers\ErrorController::class, 'error500'])->name('errors.500');
Route::get('/errors/502', [App\Http\Controllers\ErrorController::class, 'error502'])->name('errors.502');
Route::get('/errors/503', [App\Http\Controllers\ErrorController::class, 'error503'])->name('errors.503');

// Тестовые роуты для демонстрации ошибок (удалить в продакшене)
Route::get('/test/403', function () { abort(403); })->name('test.403');
Route::get('/test/404', function () { abort(404); })->name('test.404');
Route::get('/test/419', function () { throw new \Illuminate\Session\TokenMismatchException(); })->name('test.419');
Route::get('/test/500', function () { throw new \Exception('Тестовая ошибка сервера'); })->name('test.500');
Route::get('/telegram/test-mode', [TelegramController::class, 'testMode'])->name('telegram.test-mode');

// Временный роут для тестирования переноса документов (можно удалить позже)
Route::get('/test/transfer-documents/{fromUserId}/{toUserId}', function ($fromUserId, $toUserId) {
    $transferService = new \App\Services\Documents\DocumentTransferService();
    
    $fromUser = \App\Models\User::find($fromUserId);
    $toUser = \App\Models\User::find($toUserId);
    
    if (!$fromUser || !$toUser) {
        return response()->json(['error' => 'Пользователи не найдены'], 404);
    }
    
    $result = $transferService->transferDocuments($fromUser, $toUser);
    
    return response()->json([
        'result' => $result,
        'from_user' => $fromUser->only(['id', 'name', 'email']),
        'to_user' => $toUser->only(['id', 'name', 'email']),
        'documents_after_transfer' => \App\Models\Document::where('user_id', $toUser->id)
            ->get(['id', 'title', 'user_id', 'created_at'])
    ]);
})->name('test.transfer-documents');

// Тестовый роут для симуляции Telegram авторизации (можно удалить позже)
Route::get('/test/telegram-auth/{userId}', function ($userId) {
    $user = \App\Models\User::find($userId);
    
    if (!$user) {
        return response()->json(['error' => 'Пользователь не найден'], 404);
    }
    
    // Генерируем токен авторизации
    $telegramService = new \App\Services\Telegram\TelegramBotService();
    $authToken = $telegramService->generateAuthToken($user);
    
    // Симулируем данные пользователя Telegram
    $telegramUser = [
        'id' => 123456789,
        'first_name' => 'Тестовый',
        'last_name' => 'Пользователь',
        'username' => 'test_user',
    ];
    
    // Симулируем обработку авторизации через рефлексию
    $reflection = new \ReflectionClass($telegramService);
    $method = $reflection->getMethod('handleTelegramAuth');
    $method->setAccessible(true);
    
    $result = $method->invoke($telegramService, 123456789, $telegramUser, $authToken);
    
    return response()->json([
        'auth_token' => $authToken,
        'telegram_user' => $telegramUser,
        'auth_result' => $result,
        'user_before' => $user->only(['id', 'name', 'email', 'telegram_id']),
        'user_after' => \App\Models\User::find($user->id)->only(['id', 'name', 'email', 'telegram_id']),
        'documents_count' => \App\Models\Document::where('user_id', $user->id)->count()
    ]);
})->name('test.telegram-auth');

// Простой тестовый роут для демонстрации переноса документов при Telegram авторизации
Route::get('/test/full-flow', function () {
    $transferService = new \App\Services\Documents\DocumentTransferService();
    
    // Находим временного пользователя с документами
    $tempUser = \App\Models\User::where('email', 'like', '%@auto.user')
        ->whereHas('documents')
        ->with('documents')
        ->first();
    
    if (!$tempUser) {
        return response()->json(['error' => 'Не найден временный пользователь с документами'], 404);
    }
    
    // Создаем нового "авторизованного" пользователя
    $permanentUser = \App\Models\User::create([
        'name' => 'Тестовый Авторизованный Пользователь',
        'email' => 'auth_test_' . time() . '@example.com',
        'password' => bcrypt('password'),
        'auth_token' => \Illuminate\Support\Str::random(32),
        'role_id' => 0,
        'status' => 1,
        'telegram_id' => 987654321,
        'telegram_username' => 'test_auth_user',
        'telegram_linked_at' => now(),
    ]);
    
    $documentsBeforeTransfer = \App\Models\Document::where('user_id', $tempUser->id)->count();
    $documentsBefore = \App\Models\Document::where('user_id', $permanentUser->id)->count();
    
    // Выполняем перенос
    $result = $transferService->transferDocuments($tempUser, $permanentUser);
    
    $documentsAfter = \App\Models\Document::where('user_id', $permanentUser->id)->count();
    
    return response()->json([
        'status' => 'success',
        'temp_user' => [
            'id' => $tempUser->id,
            'email' => $tempUser->email,
            'documents_before' => $documentsBeforeTransfer
        ],
        'permanent_user' => [
            'id' => $permanentUser->id,
            'email' => $permanentUser->email,
            'documents_before' => $documentsBefore,
            'documents_after' => $documentsAfter
        ],
        'transfer_result' => $result,
        'documents_transferred' => $documentsAfter - $documentsBefore
    ]);
})->name('test.full-flow');

require __DIR__.'/auth.php';


// Админские роуты
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Главная страница админки
    Route::get('/', [App\Http\Controllers\AdminController::class, 'index'])->name('dashboard');
    
    // Управление пользователями
    Route::resource('users', App\Http\Controllers\AdminUserController::class);
    
    // Управление документами
    Route::resource('documents', App\Http\Controllers\AdminDocumentController::class);
    Route::patch('documents/{document}/status', [App\Http\Controllers\AdminDocumentController::class, 'updateStatus'])->name('documents.update-status');
    Route::patch('documents/{document}/transfer', [App\Http\Controllers\AdminDocumentController::class, 'transferToUser'])->name('documents.transfer');
    Route::patch('documents/{id}/restore', [App\Http\Controllers\AdminDocumentController::class, 'restore'])->name('documents.restore');
    Route::delete('documents/{id}/force-delete', [App\Http\Controllers\AdminDocumentController::class, 'forceDelete'])->name('documents.force-delete');
    
    // Управление очередями
    Route::get('queue', [App\Http\Controllers\AdminQueueController::class, 'index'])->name('queue.index');
    Route::get('queue/dashboard-data', [App\Http\Controllers\AdminQueueController::class, 'getDashboardData'])->name('queue.dashboard-data');
    Route::post('queue/start-worker', [App\Http\Controllers\AdminQueueController::class, 'startWorker'])->name('queue.start-worker');
    Route::post('queue/stop-worker', [App\Http\Controllers\AdminQueueController::class, 'stopWorker'])->name('queue.stop-worker');
    Route::post('queue/add-test-job', [App\Http\Controllers\AdminQueueController::class, 'addTestJob'])->name('queue.add-test-job');
    Route::delete('queue/delete-job', [App\Http\Controllers\AdminQueueController::class, 'deleteJob'])->name('queue.delete-job');
    Route::post('queue/retry-failed-job', [App\Http\Controllers\AdminQueueController::class, 'retryFailedJob'])->name('queue.retry-failed-job');
    Route::delete('queue/clear-failed-jobs', [App\Http\Controllers\AdminQueueController::class, 'clearFailedJobs'])->name('queue.clear-failed-jobs');
    Route::post('queue/create-document-job', [App\Http\Controllers\AdminQueueController::class, 'createDocumentJob'])->name('queue.create-document-job');
    Route::post('queue/create-batch-job', [App\Http\Controllers\AdminQueueController::class, 'createBatchJob'])->name('queue.create-batch-job');
    Route::get('queue/documents-for-job', [App\Http\Controllers\AdminQueueController::class, 'getDocumentsForJob'])->name('queue.documents-for-job');
    Route::delete('queue/clear-all-queues', [App\Http\Controllers\AdminQueueController::class, 'clearAllQueues'])->name('queue.clear-all-queues');
    Route::post('queue/stop-all-workers', [App\Http\Controllers\AdminQueueController::class, 'stopAllWorkers'])->name('queue.stop-all-workers');
    Route::post('queue/restart-all-workers', [App\Http\Controllers\AdminQueueController::class, 'restartAllWorkers'])->name('queue.restart-all-workers');
});
