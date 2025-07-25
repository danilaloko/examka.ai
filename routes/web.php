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
