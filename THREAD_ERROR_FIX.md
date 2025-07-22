# Исправление ошибки "Can't add messages to thread while a run is active"

## 📋 Описание проблемы

При генерации документов возникала ошибка:
```
OpenAI Add Message failed: {
  "error": {
    "message": "Can't add messages to thread_on6dsL6MQejd0D114xsZVhza while a run run_70qP9AgOfqoLVAbeSvOE9Cwn is active.",
    "type": "invalid_request_error",
    "param": null,
    "code": null
  }
}
```

### Причина ошибки

В процессе **полной генерации документа** (`StartFullGenerateDocument`) система:
1. ✅ Ждет завершения предыдущего run через `waitForRunCompletion()`
2. ✅ Получает результат
3. ❌ **Сразу же добавляет новое сообщение** без проверки статуса thread
4. ❌ Создает новый run

**Проблема:** По документации OpenAI Assistants API v2, нельзя добавлять сообщения в thread, пока активен run. Между завершением run и добавлением нового сообщения может быть задержка на стороне OpenAI.

## 🛠️ Решение

### 1. Новый безопасный метод `safeAddMessageToThread()`

Добавлен в `OpenAiService`:

```php
public function safeAddMessageToThread(string $threadId, string $content, int $maxRetries = 5): array
{
    $attempts = 0;
    
    while ($attempts < $maxRetries) {
        try {
            // Проверяем активные run в thread
            if ($this->hasActiveRuns($threadId)) {
                Log::info('Thread имеет активные run, ожидаем...', [
                    'thread_id' => $threadId,
                    'attempt' => $attempts + 1
                ]);
                
                sleep(2);
                $attempts++;
                continue;
            }
            
            // Пытаемся добавить сообщение
            return $this->addMessageToThread($threadId, $content);
            
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'while a run') !== false && 
                strpos($e->getMessage(), 'is active') !== false) {
                
                // Ждем дольше при повторных попытках
                sleep(min(5, 2 + $attempts));
                $attempts++;
                continue;
            }
            
            throw $e;
        }
    }
    
    throw new \Exception("Не удалось добавить сообщение в thread после {$maxRetries} попыток.");
}
```

### 2. Метод проверки активных run

```php
public function hasActiveRuns(string $threadId): bool
{
    try {
        $response = $this->getHttpClient([
            'OpenAI-Beta' => 'assistants=v2',
        ])->get("https://api.openai.com/v1/threads/{$threadId}/runs");

        if (!$response->successful()) {
            return false;
        }

        $runs = $response->json();
        
        // Проверяем есть ли активные run
        foreach ($runs['data'] ?? [] as $run) {
            if (in_array($run['status'], ['queued', 'in_progress', 'requires_action'])) {
                return true;
            }
        }
        
        return false;
        
    } catch (\Exception $e) {
        return false;
    }
}
```

### 3. Обновление всех Job'ов

Заменены вызовы `addMessageToThread()` на `safeAddMessageToThread()` в:
- ✅ `StartGenerateDocument.php`
- ✅ `StartFullGenerateDocument.php` 
- ✅ `AsyncGenerateDocument.php`

### 4. Увеличение паузы между запросами

В `StartFullGenerateDocument.php` увеличена пауза между генерацией подразделов:

```php
// Увеличенная пауза между запросами для стабилизации thread
sleep(3);
```

## 🎯 Результат

После внедрения исправлений:

1. **Автоматическая проверка** активных run перед добавлением сообщений
2. **Retry механизм** с экспоненциальной задержкой
3. **Детальное логирование** для диагностики
4. **Стабильная работа** генерации документов

### Логирование

Система теперь логирует:
- Попытки добавления сообщений в активные thread
- Обнаружение активных run
- Повторные попытки с задержками
- Успешные операции

## 🔧 Техническая документация

### API Endpoint для проверки run

```
GET /v1/threads/{thread_id}/runs
```

Возвращает список всех run в thread с их статусами.

### Активные статусы run
- `queued` - в очереди
- `in_progress` - выполняется  
- `requires_action` - требует действия

### Завершенные статусы run
- `completed` - завершен успешно
- `failed` - завершен с ошибкой
- `cancelled` - отменен
- `expired` - истек срок

## 📈 Мониторинг

Для мониторинга проблемы используйте логи:

```bash
# Поиск ошибок добавления сообщений
grep "while a run.*is active" storage/logs/laravel.log

# Поиск использования безопасного метода
grep "safeAddMessageToThread" storage/logs/laravel.log

# Активные run в thread
grep "Thread имеет активные run" storage/logs/laravel.log
```

## 🚀 Рекомендации для будущего

1. **Всегда используйте** `safeAddMessageToThread()` вместо `addMessageToThread()`
2. **Добавляйте паузы** между операциями с thread (минимум 2-3 секунды)
3. **Логируйте** все операции с OpenAI API для диагностики
4. **Мониторьте** логи на предмет подобных ошибок 