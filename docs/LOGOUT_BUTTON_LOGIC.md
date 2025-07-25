# 🚪 Логика отображения кнопки выхода

## 📋 Обзор

Система автоматически скрывает кнопку выхода для "пустых" аккаунтов, которые только что автосгенерировались и не показывают признаков активности пользователя.

## 🎯 Цель

Убрать кнопку выхода у пользователей, которые:
- Только что зарегистрированы автоматически
- Не выполняли никаких действий в системе
- Не имеют документов или платежей

## ✅ Критерии показа кнопки выхода

Кнопка выхода **ПОКАЗЫВАЕТСЯ**, если выполняется **ЛЮБОЕ** из условий:

### 1. Активность в системе
- ✅ Есть хотя бы 1 документ
- ✅ Баланс больше 0 (был пополнен)

### 2. Данные авторизации
- ✅ Авторизация через Telegram (есть `telegram_auth_user_id` в localStorage)
- ✅ Есть токен авторизации в localStorage (`auto_auth_token`)

### 3. Информация о пользователе
- ✅ Email НЕ является автогенерированным (не заканчивается на `@auto.user`)
- ✅ Есть согласие на обработку персональных данных (`privacy_consent`)
- ✅ Пользователь связан с Telegram (`telegram_id` или `telegram_linked_at`)

### 4. Время существования аккаунта
- ✅ Аккаунт существует более 1 часа

### 5. Дополнительные признаки активности
- ✅ Аккаунт создан НЕ автоматически (`person.telegram.auto_created !== true`)
- ✅ Есть пользовательские настройки или статистика
- ✅ Есть данные магазина в localStorage
- ✅ Есть сохраненные настройки в localStorage
- ✅ Выбран язык, отличный от русского
- ✅ Найдены куки Telegram
- ✅ Пользователь пришел через Telegram WebApp (параметры в URL)

## ❌ Критерии сокрытия кнопки

Кнопка выхода **СКРЫВАЕТСЯ** только если:

- Пользователь авторизован
- НО **НИ ОДИН** из критериев показа не выполняется

Это означает, что аккаунт является "пустым":
- Email автогенерированный (`@auto.user`)
- Нет документов
- Баланс равен 0
- Аккаунт создан менее часа назад
- Нет признаков активности пользователя

## 🧪 Тестирование

### Команды для тестирования

```bash
# Создать тестового пустого пользователя
php artisan create:empty-user --name="Тестовый Пользователь"

# Проверить логику для конкретного пользователя
php artisan test:logout-button --user-id=29

# Проверить логику для всех пользователей
php artisan test:logout-button
```

### Отладка в браузере

При работе в development режиме или при добавлении `?debug=1` к URL в правом нижнем углу появляется панель отладки, показывающая:

- Должна ли показываться кнопка выхода
- Причину принятого решения
- Все проверенные критерии
- Подробную информацию о пользователе

## 🔧 Техническая реализация

### Frontend (JavaScript)

Основная логика находится в `resources/js/composables/auth.js`:

- `shouldShowLogoutButtonWithData(documentsCount, balance)` - основная функция
- `debugLogoutButtonCriteria(documentsCount, balance)` - функция отладки

### Backend (PHP)

Серверная логика в команде `app/Console/Commands/TestLogoutButtonLogic.php` для тестирования.

### Компоненты

- `PageHeader.vue` - использует логику для показа/скрытия кнопки
- `LogoutButtonDebug.vue` - компонент отладки (только в development)

## 📊 Статистика

По состоянию на момент внедрения:
- Из 29 пользователей в системе
- 22 пользователя видят кнопку выхода (активные)
- 7 пользователей не видят кнопку (пустые аккаунты)

## 🚀 Преимущества

1. **Улучшенный UX**: Пустые аккаунты не видят ненужную кнопку выхода
2. **Автоматичность**: Логика работает без вмешательства администратора
3. **Гибкость**: Множество критериев определения активности
4. **Безопасность**: Активные пользователи всегда могут выйти
5. **Отладочность**: Есть инструменты для тестирования и отладки

## ⚠️ Важные замечания

- Логика применяется только к авторизованным пользователям
- Критерии проверяются в порядке важности (документы и баланс в первую очередь)
- Время жизни аккаунта проверяется только если другие критерии не сработали
- При сомнениях система всегда показывает кнопку выхода (безопасность превыше всего)

## 🔮 Возможные улучшения

1. Настройка времени "новизны" аккаунта через конфиг
2. Дополнительные критерии активности (просмотры страниц, клики)
3. Статистика по скрытию кнопки в админ-панели
4. A/B тестирование эффективности скрытия 