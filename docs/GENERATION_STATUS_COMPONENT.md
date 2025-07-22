# DocumentGenerationStatus - Компонент отображения статуса генерации

## 📋 Описание

Компонент `DocumentGenerationStatus` предназначен для отображения процесса генерации документов с красивой анимацией, обратным отсчетом времени и интеграцией с существующей системой отслеживания статусов.

## 🎯 Основные возможности

- ✅ Интеграция с `useDocumentStatus` композаблом
- ⏰ Обратный отсчет времени с настраиваемой продолжительностью
- 🔄 Анимированная иконка загрузки
- 📊 Прогресс-бар с этапами генерации
- 📝 12 предустановленных этапов процесса
- 🎯 Автоматическое скрытие после завершения генерации
- 🔔 Уведомления Quasar при смене статуса
- 💡 Сообщение при превышении времени ожидания

## 🚀 Использование

### Базовый пример
```vue
<template>
    <document-generation-status
        :document-id="documentId"
        :estimated-time="30"
        title="Создаем ваш документ"
        @complete="handleComplete"
        @full-complete="handleFullComplete"
        @approved="handleApproved"
        @error="handleError"
    />
</template>

<script setup>
import DocumentGenerationStatus from '@/modules/gpt/components/DocumentGenerationStatus.vue';

const documentId = ref(123);

const handleComplete = (status) => {
    console.log('Базовая генерация завершена:', status);
};

const handleFullComplete = (status) => {
    console.log('Полная генерация завершена:', status);
};

const handleApproved = (status) => {
    router.visit('/documents');
};

const handleError = (error) => {
    console.error('Ошибка:', error);
};
</script>
```

### Расширенный пример
```vue
<template>
    <document-generation-status
        :document-id="() => props.document.id"
        :estimated-time="45"
        title="Генерируем полное содержимое"
        :auto-start="false"
        ref="generationStatus"
    />
</template>

<script setup>
import { ref, onMounted } from 'vue';

const generationStatus = ref(null);

onMounted(() => {
    // Ручной запуск отслеживания
    generationStatus.value?.startAll();
});

// Сброс компонента при необходимости
const resetGeneration = () => {
    generationStatus.value?.reset();
};
</script>
```

## 📋 Props

| Prop | Тип | По умолчанию | Описание |
|------|-----|--------------|----------|
| `documentId` | `Number\|Function` | - | **Обязательный.** ID документа для отслеживания |
| `estimatedTime` | `Number` | `15` | Предполагаемое время завершения в секундах |
| `title` | `String` | `'Документ создается'` | Заголовок процесса |
| `autoStart` | `Boolean` | `true` | Автоматически запускать отслеживание |

## 🎪 События

| Событие | Параметры | Описание |
|---------|-----------|----------|
| `complete` | `status` | Базовая генерация завершена (pre_generated) |
| `fullComplete` | `status` | Полная генерация завершена (full_generated) |
| `approved` | `status` | Документ утвержден (approved) |
| `error` | `error` | Ошибка при отслеживании статуса |

## 🎛️ Методы (через ref)

| Метод | Описание |
|-------|----------|
| `startAll()` | Запустить все таймеры и отслеживание |
| `stop()` | Остановить все таймеры |
| `reset()` | Сбросить состояние и перезапустить |
| `shouldShowGenerationStatus` | Computed-свойство для проверки видимости |

## 🎨 Этапы процесса

Компонент отображает следующие этапы генерации:

1. Готовим запрос к системе генерации
2. Анализируем тему документа
3. Формируем цели и задачи документа
4. Создаем структуру содержания
5. Генерируем разделы и подразделы
6. Формируем детальное содержимое
7. Проверяем корректность данных
8. Добавляем ключевые моменты
9. Форматируем структуру документа
10. Финализируем результат
11. Проверяем качество генерации
12. Подготавливаем документ к просмотру

## 🔧 Интеграция с существующей системой

Компонент полностью интегрирован с:

- **`useDocumentStatus`** - использует существующий композабл для отслеживания
- **API endpoints** - работает через `/documents/{id}/status`
- **Enum статусов** - поддерживает все статусы из `DocumentStatus`
- **Quasar уведомления** - показывает toast-уведомления при смене статуса

## 📱 Условия отображения

Компонент автоматически показывается только когда:
- `isGenerating() === true` (статусы: pre_generating, full_generating)
- `isLoading === true` (идет запрос к API)

И автоматически скрывается когда:
- Генерация завершена (pre_generated, full_generated)
- Произошла ошибка (pre_generation_failed, full_generation_failed)
- Документ утвержден (approved)
- Документ отклонен (rejected)

## 🎯 Пример интеграции в ShowDocument

```vue
<template>
    <page-layout title="Просмотр документа">
        <div class="q-pa-md">
            <!-- Показываем статус генерации -->
            <document-generation-status
                :document-id="() => document.id"
                :estimated-time="30"
                title="Документ создается"
            />

            <!-- Показываем обычный вид когда генерация не идет -->
            <div v-if="!isGenerating()">
                <document-view :document="document" />
                <document-status-panel ... />
            </div>
        </div>
    </page-layout>
</template>
```

## 🎨 Стилизация

Компонент использует Quasar компоненты и поддерживает:
- Responsive дизайн
- Темную/светлую тему
- Анимации CSS
- Quasar цветовую палитру

## 🧪 Тестирование

Для тестирования можно использовать команды:

```bash
# Создать тестовый документ
php artisan document:test-create

# Утвердить документ для завершения генерации
php artisan document:approve {document_id}
``` 