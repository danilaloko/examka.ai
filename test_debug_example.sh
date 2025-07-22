#!/bin/bash

# Скрипт для демонстрации инструментов дебага полной генерации документов
# Использование: ./test_debug_example.sh [document_id]

set -e

DOCUMENT_ID=${1:-29}
LOG_FILE="storage/logs/debug_generation.log"

echo "🔧 ТЕСТИРОВАНИЕ ИНСТРУМЕНТОВ ДЕБАГА"
echo "===================================="
echo "Документ ID: $DOCUMENT_ID"
echo "Лог файл: $LOG_FILE"
echo ""

# Функция для вывода разделителя
print_separator() {
    echo ""
    echo "------------------------------------"
    echo "$1"
    echo "------------------------------------"
}

# Функция для проверки существования документа
check_document() {
    print_separator "📋 Проверка документа #$DOCUMENT_ID"
    
    if php artisan tinker --execute="
        \$doc = App\\Models\\Document::find($DOCUMENT_ID);
        if (\$doc) {
            echo 'Документ найден: ' . \$doc->title . PHP_EOL;
            echo 'Статус: ' . \$doc->status->value . PHP_EOL;
            echo 'Thread ID: ' . (\$doc->thread_id ?: 'НЕТ') . PHP_EOL;
            echo 'Структура: ' . (!empty(\$doc->structure) ? 'ЕСТЬ' : 'НЕТ') . PHP_EOL;
        } else {
            echo 'Документ не найден!' . PHP_EOL;
            exit(1);
        }
    "; then
        echo "✅ Документ готов для тестирования"
    else
        echo "❌ Ошибка при проверке документа"
        exit 1
    fi
}

# Функция для быстрого теста
quick_test() {
    print_separator "⚡ Быстрый тест одного подраздела"
    
    echo "Запускаем быстрый тест с созданием нового thread..."
    if php artisan debug:single-subtopic $DOCUMENT_ID --create-new-thread; then
        echo "✅ Быстрый тест завершен успешно"
    else
        echo "❌ Быстрый тест завершен с ошибкой"
        echo "Проверьте лог файл: $LOG_FILE"
    fi
}

# Функция для мониторинга существующего thread
monitor_thread() {
    print_separator "📊 Мониторинг thread (если есть)"
    
    THREAD_ID=$(php artisan tinker --execute="
        \$doc = App\\Models\\Document::find($DOCUMENT_ID);
        echo \$doc->thread_id ?: '';
    ")
    
    if [[ -n "$THREAD_ID" ]]; then
        echo "Мониторинг thread: $THREAD_ID"
        echo "Продолжительность: 30 секунд, интервал: 5 секунд"
        
        if php artisan debug:thread-monitor $THREAD_ID --duration=30 --interval=5; then
            echo "✅ Мониторинг завершен"
        else
            echo "❌ Ошибка при мониторинге"
        fi
    else
        echo "🔶 Thread не найден, пропускаем мониторинг"
    fi
}

# Функция для полной генерации (опционально)
full_generation_test() {
    print_separator "🚀 Полная генерация (ОСТОРОЖНО!)"
    
    echo "⚠️  ВНИМАНИЕ: Полная генерация может занять много времени и потратить токены!"
    echo "Хотите запустить полную генерацию? (y/N)"
    
    read -r response
    if [[ "$response" =~ ^[Yy]$ ]]; then
        echo "Запускаем полную генерацию с максимальным логированием..."
        
        if php artisan debug:full-generation $DOCUMENT_ID; then
            echo "✅ Полная генерация завершена успешно"
        else
            echo "❌ Полная генерация завершена с ошибкой"
            echo "Проверьте лог файл: $LOG_FILE"
        fi
    else
        echo "🔶 Полная генерация пропущена"
    fi
}

# Функция для анализа логов
analyze_logs() {
    print_separator "📄 Анализ логов"
    
    if [[ -f "$LOG_FILE" ]]; then
        echo "Размер лог файла: $(du -h $LOG_FILE | cut -f1)"
        echo ""
        echo "Последние 10 записей:"
        tail -n 10 "$LOG_FILE"
        echo ""
        echo "Поиск ошибок:"
        grep -i "error\|ошибка" "$LOG_FILE" | tail -n 5 || echo "Ошибок не найдено"
        echo ""
        echo "Поиск активных run:"
        grep -i "активные run\|active run" "$LOG_FILE" | tail -n 5 || echo "Активных run не найдено"
    else
        echo "❌ Лог файл не найден: $LOG_FILE"
    fi
}

# Основной поток выполнения
main() {
    # Проверяем документ
    check_document
    
    # Быстрый тест
    quick_test
    
    # Мониторинг thread
    monitor_thread
    
    # Полная генерация (опционально)
    full_generation_test
    
    # Анализ логов
    analyze_logs
    
    print_separator "🎉 Тестирование завершено"
    echo "📄 Полные логи: $LOG_FILE"
    echo "📊 Команды для дальнейшего анализа:"
    echo "   tail -f $LOG_FILE"
    echo "   grep 'thread_' $LOG_FILE"
    echo "   grep -i 'error' $LOG_FILE"
    echo ""
    echo "🔧 Доступные команды:"
    echo "   php artisan debug:single-subtopic $DOCUMENT_ID"
    echo "   php artisan debug:thread-monitor [thread_id]"
    echo "   php artisan debug:full-generation $DOCUMENT_ID"
}

# Запуск основной функции
main "$@" 