# API: Быстрое создание документа (quickCreate)

## Описание
Эндпоинт для быстрого создания документа с минимальными данными или тестовыми данными.

## URL
```
POST /documents
```

## Параметры

| Параметр | Тип | Обязательный | Описание |
|----------|-----|--------------|----------|
| `document_type_id` | integer | ✅ | ID типа документа |
| `topic` | string | ✅ | Тема документа (макс. 255 символов) |
| `test` | boolean | ❌ | Создать документ с тестовыми данными |

## Логика работы

### 🟢 Обычный режим (test=false или не передан)
Создается **минимальный документ** только с переданными данными:

```json
{
  "document_type_id": 1,
  "topic": "Моя тема документа"
}
```

**Результат:**
- `structure.topic` = переданная тема
- `structure.theses` = пустая строка
- `structure.objectives` = пустой массив
- `structure.contents` = пустой массив
- `structure.references` = пустой массив
- Минимальные настройки GPT

### 🧪 Тестовый режим (test=true)
Создается документ с **фейковыми данными** из фабрики:

```json
{
  "document_type_id": 1,
  "topic": "Моя тема документа",
  "test": true
}
```

**Результат:**
- Полная структура с фейковыми данными из `DocumentFactory`
- Готовые цели, содержание, ссылки
- Тема перезаписывается на переданную

## Примеры запросов

### Создание обычного документа
```bash
curl -X POST http://localhost/documents \
  -H "Content-Type: application/json" \
  -d '{
    "document_type_id": 1,
    "topic": "Анализ рынка недвижимости"
  }'
```

### Создание тестового документа
```bash
curl -X POST http://localhost/documents \
  -H "Content-Type: application/json" \
  -d '{
    "document_type_id": 1,
    "topic": "Анализ рынка недвижимости",
    "test": true
  }'
```

## Ответ API

```json
{
  "message": "Документ успешно создан",
  "document": {
    "id": 123,
    "title": "Анализ рынка недвижимости",
    "status": "draft",
    "structure": {
      "topic": "Анализ рынка недвижимости",
      "theses": "",
      "objectives": [],
      "contents": [],
      "references": []
    },
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

## После создания

1. Документ автоматически ставится в очередь на генерацию (`StartGenerateDocument` Job)
2. Пользователь перенаправляется на страницу просмотра документа
3. На странице просмотра отображается статус генерации

## Статусы генерации

- `draft` → `pre_generating` → `pre_generated` (базовая структура)
- `pre_generated` → `full_generating` → `full_generated` (полное содержимое)

## Использование в коде

### Frontend (NewDocument.vue)
```javascript
const response = await apiClient.post(route('documents.quick-create'), {
  document_type_id: Number(form.value.document_type_id),
  topic: form.value.topic
  // test: true // для тестирования
});

// Сразу переход на просмотр
router.visit(route('documents.show', response.document.id));
```

### Testing (Artisan команды)
```bash
# Создание тестового документа
php artisan test:document-generation --user-id=1 --topic="Тестовая тема"

# Создание минимального документа
php artisan tinker
>>> Document::factory()->minimal()->create(['title' => 'Минимальный документ'])
```

## Преимущества новой логики

✅ **Чистые данные** - по умолчанию нет лишних фейковых данных  
✅ **Гибкость** - можно создавать и тестовые, и продакшн документы  
✅ **Простота** - минимальный набор данных для старта  
✅ **Тестирование** - удобное создание документов с готовыми данными  