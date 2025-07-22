#!/bin/bash

# Скрипт для автоматизированного тестирования многопоточности с несколькими документами
# Автор: Система тестирования OpenAI API
# Дата: $(date)

set -e

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Функция для логирования
log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

# Проверка зависимостей
check_dependencies() {
    log "Проверка зависимостей..."
    
    if ! command -v php &> /dev/null; then
        error "PHP не найден"
        exit 1
    fi
    
    if [ ! -f "artisan" ]; then
        error "Файл artisan не найден. Запустите скрипт из корневой директории Laravel"
        exit 1
    fi
    
    success "Все зависимости найдены"
}

# Создание директории для результатов
setup_results_directory() {
    local results_dir="multi_document_test_results"
    
    if [ ! -d "$results_dir" ]; then
        mkdir -p "$results_dir"
        log "Создана директория для результатов: $results_dir"
    fi
    
    # Создание директории для текущего сеанса
    export TEST_SESSION_DIR="$results_dir/session_$(date +'%Y%m%d_%H%M%S')"
    mkdir -p "$TEST_SESSION_DIR"
    log "Директория сеанса: $TEST_SESSION_DIR"
}

# Сценарий 1: Базовое тестирование с несколькими документами
scenario_basic() {
    log "🧪 Сценарий 1: Базовое тестирование многопоточности"
    log "   - 5 документов"
    log "   - 3 воркера"
    log "   - 3 итерации на воркера"
    log "   - Задержка 2 секунды"
    
    php artisan debug:multi-doc-parallel \
        --documents=5 \
        --workers=3 \
        --iterations=3 \
        --delay=2 \
        --create-new 2>&1 | tee "$TEST_SESSION_DIR/scenario_basic.log"
    
    if [ $? -eq 0 ]; then
        success "Базовое тестирование завершено успешно"
    else
        error "Базовое тестирование завершилось с ошибкой"
        return 1
    fi
}

# Сценарий 2: Интенсивное тестирование
scenario_intensive() {
    log "🔥 Сценарий 2: Интенсивное тестирование"
    log "   - 8 документов"
    log "   - 5 воркеров"
    log "   - 5 итераций на воркера"
    log "   - Задержка 1 секунда"
    
    php artisan debug:multi-doc-parallel \
        --documents=8 \
        --workers=5 \
        --iterations=5 \
        --delay=1 \
        --create-new 2>&1 | tee "$TEST_SESSION_DIR/scenario_intensive.log"
    
    if [ $? -eq 0 ]; then
        success "Интенсивное тестирование завершено успешно"
    else
        error "Интенсивное тестирование завершилось с ошибкой"
        return 1
    fi
}

# Сценарий 3: Тестирование с большим количеством документов
scenario_many_documents() {
    log "📚 Сценарий 3: Тестирование с большим количеством документов"
    log "   - 15 документов"
    log "   - 4 воркера"
    log "   - 2 итерации на воркера"
    log "   - Задержка 3 секунды"
    
    php artisan debug:multi-doc-parallel \
        --documents=15 \
        --workers=4 \
        --iterations=2 \
        --delay=3 \
        --create-new 2>&1 | tee "$TEST_SESSION_DIR/scenario_many_docs.log"
    
    if [ $? -eq 0 ]; then
        success "Тестирование с большим количеством документов завершено успешно"
    else
        error "Тестирование с большим количеством документов завершилось с ошибкой"
        return 1
    fi
}

# Сценарий 4: Быстрое тестирование без задержек
scenario_fast() {
    log "⚡ Сценарий 4: Быстрое тестирование без задержек"
    log "   - 6 документов"
    log "   - 6 воркеров"
    log "   - 3 итерации на воркера"
    log "   - Без задержек"
    
    php artisan debug:multi-doc-parallel \
        --documents=6 \
        --workers=6 \
        --iterations=3 \
        --delay=0 \
        --create-new 2>&1 | tee "$TEST_SESSION_DIR/scenario_fast.log"
    
    if [ $? -eq 0 ]; then
        success "Быстрое тестирование завершено успешно"
    else
        error "Быстрое тестирование завершилось с ошибкой"
        return 1
    fi
}

# Сценарий 5: Тестирование с использованием существующих документов
scenario_existing_docs() {
    log "🔄 Сценарий 5: Тестирование с существующими документами"
    log "   - 10 существующих документов"
    log "   - 3 воркера"
    log "   - 4 итерации на воркера"
    log "   - Задержка 1 секунда"
    
    php artisan debug:multi-doc-parallel \
        --documents=10 \
        --workers=3 \
        --iterations=4 \
        --delay=1 2>&1 | tee "$TEST_SESSION_DIR/scenario_existing.log"
    
    if [ $? -eq 0 ]; then
        success "Тестирование с существующими документами завершено успешно"
    else
        error "Тестирование с существующими документами завершилось с ошибкой"
        return 1
    fi
}

# Анализ результатов всех сценариев
analyze_all_results() {
    log "📈 Анализ результатов всех сценариев..."
    
    local analysis_file="$TEST_SESSION_DIR/combined_analysis.md"
    
    cat > "$analysis_file" << EOF
# Анализ результатов многопоточного тестирования

## Информация о сеансе
- **Дата и время:** $(date)
- **Директория результатов:** $TEST_SESSION_DIR
- **Количество сценариев:** 5

## Сценарии тестирования

### 1. Базовое тестирование
- Документы: 5
- Воркеры: 3
- Итерации: 3
- Задержка: 2с

### 2. Интенсивное тестирование
- Документы: 8
- Воркеры: 5
- Итерации: 5
- Задержка: 1с

### 3. Большое количество документов
- Документы: 15
- Воркеры: 4
- Итерации: 2
- Задержка: 3с

### 4. Быстрое тестирование
- Документы: 6
- Воркеры: 6
- Итерации: 3
- Задержка: 0с

### 5. Существующие документы
- Документы: 10
- Воркеры: 3
- Итерации: 4
- Задержка: 1с

## Анализ логов

EOF

    # Анализ каждого сценария
    for scenario in basic intensive many_docs fast existing; do
        local log_file="$TEST_SESSION_DIR/scenario_${scenario}.log"
        
        if [ -f "$log_file" ]; then
            echo "### Сценарий: $scenario" >> "$analysis_file"
            echo "" >> "$analysis_file"
            
            # Подсчет успешных операций
            local success_count=$(grep -c "успешно\|successfully\|completed" "$log_file" || echo "0")
            local error_count=$(grep -c "ошибка\|error\|failed" "$log_file" || echo "0")
            local active_run_errors=$(grep -c "active run" "$log_file" || echo "0")
            
            echo "- **Успешных операций:** $success_count" >> "$analysis_file"
            echo "- **Ошибок:** $error_count" >> "$analysis_file"
            echo "- **Ошибок активных run:** $active_run_errors" >> "$analysis_file"
            echo "" >> "$analysis_file"
        else
            echo "### Сценарий: $scenario - ПРОПУЩЕН" >> "$analysis_file"
            echo "" >> "$analysis_file"
        fi
    done
    
    # Общие выводы
    cat >> "$analysis_file" << EOF

## Общие выводы

### Производительность
- Лучший сценарий: Определяется по соотношению успешных операций к общему количеству
- Худший сценарий: Определяется по количеству ошибок

### Рекомендации
1. **Если много ошибок активных run:** Улучшить синхронизацию между воркерами
2. **Если неравномерное распределение:** Оптимизировать алгоритм распределения документов
3. **Если проблемы с производительностью:** Рассмотреть увеличение задержек между операциями

### Следующие шаги
1. Анализ детальных логов в storage/logs/
2. Оптимизация кода на основе найденных проблем
3. Повторное тестирование после внесения изменений
EOF

    success "Анализ результатов сохранен в: $analysis_file"
}

# Создание отчета
generate_report() {
    log "📊 Создание итогового отчета..."
    
    local report_file="$TEST_SESSION_DIR/final_report.html"
    
    cat > "$report_file" << EOF
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отчет о многопоточном тестировании</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { background: #f4f4f4; padding: 20px; border-radius: 5px; }
        .scenario { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🧪 Отчет о многопоточном тестировании</h1>
        <p><strong>Дата:</strong> $(date)</p>
        <p><strong>Директория результатов:</strong> $TEST_SESSION_DIR</p>
    </div>
    
    <h2>📋 Сценарии тестирования</h2>
    
    <div class="scenario">
        <h3>1. Базовое тестирование</h3>
        <p>Тестирование базовой функциональности с умеренной нагрузкой</p>
        <table>
            <tr><th>Параметр</th><th>Значение</th></tr>
            <tr><td>Документы</td><td>5</td></tr>
            <tr><td>Воркеры</td><td>3</td></tr>
            <tr><td>Итерации</td><td>3</td></tr>
            <tr><td>Задержка</td><td>2с</td></tr>
        </table>
    </div>
    
    <div class="scenario">
        <h3>2. Интенсивное тестирование</h3>
        <p>Тестирование под повышенной нагрузкой</p>
        <table>
            <tr><th>Параметр</th><th>Значение</th></tr>
            <tr><td>Документы</td><td>8</td></tr>
            <tr><td>Воркеры</td><td>5</td></tr>
            <tr><td>Итерации</td><td>5</td></tr>
            <tr><td>Задержка</td><td>1с</td></tr>
        </table>
    </div>
    
    <div class="scenario">
        <h3>3. Большое количество документов</h3>
        <p>Тестирование масштабируемости</p>
        <table>
            <tr><th>Параметр</th><th>Значение</th></tr>
            <tr><td>Документы</td><td>15</td></tr>
            <tr><td>Воркеры</td><td>4</td></tr>
            <tr><td>Итерации</td><td>2</td></tr>
            <tr><td>Задержка</td><td>3с</td></tr>
        </table>
    </div>
    
    <div class="scenario">
        <h3>4. Быстрое тестирование</h3>
        <p>Тестирование максимальной скорости</p>
        <table>
            <tr><th>Параметр</th><th>Значение</th></tr>
            <tr><td>Документы</td><td>6</td></tr>
            <tr><td>Воркеры</td><td>6</td></tr>
            <tr><td>Итерации</td><td>3</td></tr>
            <tr><td>Задержка</td><td>0с</td></tr>
        </table>
    </div>
    
    <div class="scenario">
        <h3>5. Существующие документы</h3>
        <p>Тестирование с реальными данными</p>
        <table>
            <tr><th>Параметр</th><th>Значение</th></tr>
            <tr><td>Документы</td><td>10</td></tr>
            <tr><td>Воркеры</td><td>3</td></tr>
            <tr><td>Итерации</td><td>4</td></tr>
            <tr><td>Задержка</td><td>1с</td></tr>
        </table>
    </div>
    
    <h2>📁 Файлы результатов</h2>
    <ul>
        <li><a href="scenario_basic.log">Базовое тестирование</a></li>
        <li><a href="scenario_intensive.log">Интенсивное тестирование</a></li>
        <li><a href="scenario_many_docs.log">Большое количество документов</a></li>
        <li><a href="scenario_fast.log">Быстрое тестирование</a></li>
        <li><a href="scenario_existing.log">Существующие документы</a></li>
        <li><a href="combined_analysis.md">Общий анализ</a></li>
    </ul>
    
    <h2>🔍 Для детального анализа</h2>
    <p>Проверьте логи Laravel в директории <code>storage/logs/</code> для получения подробной информации о каждой операции.</p>
    
    <footer style="margin-top: 50px; padding-top: 20px; border-top: 1px solid #ddd;">
        <p><small>Отчет создан автоматически системой тестирования OpenAI API</small></p>
    </footer>
</body>
</html>
EOF

    success "HTML отчет создан: $report_file"
}

# Очистка старых результатов
cleanup_old_results() {
    log "🧹 Очистка старых результатов..."
    
    # Удаление результатов старше 7 дней
    find multi_document_test_results -type d -name "session_*" -mtime +7 -exec rm -rf {} \; 2>/dev/null || true
    
    success "Очистка завершена"
}

# Главная функция
main() {
    log "🚀 Запуск автоматизированного тестирования многопоточности"
    log "=================================================="
    
    # Проверка зависимостей
    check_dependencies
    
    # Подготовка
    setup_results_directory
    cleanup_old_results
    
    # Переменные для отслеживания результатов
    local total_scenarios=5
    local passed_scenarios=0
    local failed_scenarios=0
    
    # Запуск сценариев
    log "Запуск сценариев тестирования..."
    
    # Сценарий 1
    if scenario_basic; then
        ((passed_scenarios++))
    else
        ((failed_scenarios++))
    fi
    
    sleep 5  # Пауза между сценариями
    
    # Сценарий 2
    if scenario_intensive; then
        ((passed_scenarios++))
    else
        ((failed_scenarios++))
    fi
    
    sleep 5
    
    # Сценарий 3
    if scenario_many_documents; then
        ((passed_scenarios++))
    else
        ((failed_scenarios++))
    fi
    
    sleep 5
    
    # Сценарий 4
    if scenario_fast; then
        ((passed_scenarios++))
    else
        ((failed_scenarios++))
    fi
    
    sleep 5
    
    # Сценарий 5
    if scenario_existing_docs; then
        ((passed_scenarios++))
    else
        ((failed_scenarios++))
    fi
    
    # Анализ результатов
    analyze_all_results
    generate_report
    
    # Итоговый отчет
    log "=================================================="
    log "📊 ИТОГОВЫЕ РЕЗУЛЬТАТЫ"
    log "=================================================="
    log "Всего сценариев: $total_scenarios"
    success "Успешно выполнено: $passed_scenarios"
    if [ $failed_scenarios -gt 0 ]; then
        error "Завершились с ошибкой: $failed_scenarios"
    fi
    log "Результаты сохранены в: $TEST_SESSION_DIR"
    
    if [ $failed_scenarios -eq 0 ]; then
        success "🎉 Все сценарии выполнены успешно!"
        return 0
    else
        warning "⚠️ Некоторые сценарии завершились с ошибками"
        return 1
    fi
}

# Обработка аргументов командной строки
case "${1:-all}" in
    "basic")
        check_dependencies
        setup_results_directory
        scenario_basic
        ;;
    "intensive")
        check_dependencies
        setup_results_directory
        scenario_intensive
        ;;
    "many-docs")
        check_dependencies
        setup_results_directory
        scenario_many_documents
        ;;
    "fast")
        check_dependencies
        setup_results_directory
        scenario_fast
        ;;
    "existing")
        check_dependencies
        setup_results_directory
        scenario_existing_docs
        ;;
    "all"|"")
        main
        ;;
    "help"|"-h"|"--help")
        echo "Использование: $0 [СЦЕНАРИЙ]"
        echo ""
        echo "Сценарии:"
        echo "  basic      - Базовое тестирование"
        echo "  intensive  - Интенсивное тестирование"
        echo "  many-docs  - Большое количество документов"
        echo "  fast       - Быстрое тестирование"
        echo "  existing   - Существующие документы"
        echo "  all        - Все сценарии (по умолчанию)"
        echo "  help       - Показать эту справку"
        echo ""
        echo "Примеры:"
        echo "  $0                  # Запустить все сценарии"
        echo "  $0 basic           # Запустить только базовое тестирование"
        echo "  $0 intensive       # Запустить только интенсивное тестирование"
        ;;
    *)
        error "Неизвестный сценарий: $1"
        echo "Используйте '$0 help' для получения справки"
        exit 1
        ;;
esac 