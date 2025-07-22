<template>
    <page-layout :auto-auth="true">
        <Head title="Создание документа" />
        <YandexMetrika />
        
        <div class="container">
            <!-- Мобильный заголовок и прогресс (показывается только на мобильных) -->
            <div class="mobile-header">
                <h1 class="mobile-title">Расскажи о своей работе</h1>
                <div class="mobile-progress">
                    <div class="progress-bar">
                        <div class="progress-fill" :style="{ width: `${(1/3) * 100}%` }"></div>
                    </div>
                    <div class="progress-text">Шаг 1 из 3</div>
                </div>
            </div>

            <!-- Основной контент с двумя колонками -->
            <div class="main-content">
                <!-- Левая колонка с информацией -->
                <div class="info-column">
                    <!-- Заголовок (скрывается на мобильных) -->
                    <div class="header-section">
                        <h1 class="main-title">Расскажи о своей работе</h1>
                    </div>

                    <!-- Блок с шагами (скрывается на мобильных) -->
                    <div class="steps-card">
                        <h3 class="steps-title">
                            3 шага до сдачи
                        </h3>
                        <div class="steps-blocks">
                            <div class="step-block active">
                                <h4 class="step-block-title">Опиши работу</h4>
                            </div>
                            <div class="step-block">
                                <h4 class="step-block-title">Утверди содержание</h4>
                            </div>
                            <div class="step-block">
                                <h4 class="step-block-title">Получи работу</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Правая колонка с формой -->
                <div class="form-column">
                    <div class="form-container">
                        <q-form @submit="onSubmit" class="document-form">
                            
                            <!-- Выбор типа работы -->
                            <div class="form-section">
                                <h3 class="section-title">Тип работы</h3>
                                <div class="work-type-buttons">
                                    <button 
                                        v-for="type in displayedTypes" 
                                        :key="type.id"
                                        type="button"
                                        @click="selectWorkType(type)"
                                        :class="[
                                            'work-type-btn',
                                            { 'active': form.document_type_id === type.id }
                                        ]"
                                    >
                                        <span class="work-type-name">{{ type.name }}</span>
                                    </button>
                                    <button 
                                        v-if="hiddenTypes.length > 0"
                                        type="button"
                                        @click="toggleOtherTypesModal"
                                        class="work-type-btn other-type-btn"
                                    >
                                        <span class="work-type-name">Другое</span>
                                    </button>
                                </div>
                                <div v-if="hasError('document_type_id')" class="error-message">
                                    {{ getError('document_type_id') }}
                                </div>
                            </div>

                            <!-- Тема работы -->
                            <div class="form-section">
                                <h3 class="section-title">Тема работы</h3>
                                <p class="section-description">
                                    Опиши тему твоей работы. Чем подробнее, тем лучше будет результат (минимум 10 символов)
                                </p>
                                <div class="input-container">
                                    <CustomInput
                                        v-model="form.topic"
                                        type="textarea"
                                        placeholder="Введите тему документа..."
                                        :rows="4"
                                        :error="hasError('topic') ? getError('topic') : ''"
                                    />
                                </div>
                            </div>

                            <!-- Объем работы -->
                            <div class="form-section">
                                <h3 class="section-title">Объем работы (от 4 до 25)</h3>
                                
                                <!-- Визуализация структуры документа -->
                                <div class="document-structure">
                                    <div class="structure-line">
                                        <div class="structure-segment title-page" :style="{ flex: 1 }">
                                            <div class="segment-bar">
                                                <span class="segment-pages-on-bar">1</span>
                                            </div>
                                            <div class="segment-label">
                                                <span class="segment-name">Титульник</span>
                                            </div>
                                        </div>
                                        <div class="structure-segment contents-page" :style="{ flex: 1 }">
                                            <div class="segment-bar">
                                                <span class="segment-pages-on-bar">1</span>
                                            </div>
                                            <div class="segment-label">
                                                <span class="segment-name">Содержание</span>
                                            </div>
                                        </div>
                                        <div class="structure-segment main-text" :style="{ flex: form.pages_num - 3 }">
                                            <div class="segment-bar">
                                                <span class="segment-pages-on-bar">{{ form.pages_num - 3 }}</span>
                                            </div>
                                            <div class="segment-label">
                                                <span class="segment-name">Текст работы</span>
                                            </div>
                                        </div>
                                        <div class="structure-segment references-page" :style="{ flex: 1 }">
                                            <div class="segment-bar">
                                                <span class="segment-pages-on-bar">1</span>
                                            </div>
                                            <div class="segment-label">
                                                <span class="segment-name">Список литературы</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="pages-input-container">
                                    <div class="pages-counter">
                                        <button 
                                            type="button" 
                                            @click="decrementPages" 
                                            :disabled="form.pages_num <= 4"
                                            class="counter-btn"
                                        >
                                            <q-icon name="remove" />
                                        </button>
                                        <div class="pages-display">
                                            <span class="pages-number">{{ form.pages_num }}</span>
                                            <span class="pages-label">страниц</span>
                                        </div>
                                        <button 
                                            type="button" 
                                            @click="incrementPages" 
                                            :disabled="form.pages_num >= 25"
                                            class="counter-btn"
                                        >
                                            <q-icon name="add" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Ошибки -->
                            <div v-if="error" class="global-error">
                                <q-icon name="error" class="error-icon" />
                                <span>{{ error }}</span>
                            </div>

                            <!-- Кнопка отправки -->
                            <div class="submit-section">
                                <div class="submit-container">
                                    <!-- Мобильная подсказка -->
                                    <div v-if="showMobileHint" :class="['mobile-hint', { 'mobile-hint-closing': mobileHintClosing }]">
                                        <q-icon name="info" class="mobile-hint-icon" />
                                        <span>{{ getMobileHintText() }}</span>
                                    </div>
                                    
                                    <div class="submit-wrapper">
                                        <q-btn
                                            type="button"
                                            :loading="isLoading"
                                            :class="['submit-btn', { 'submit-btn-disabled': !canSubmit }]"
                                            unelevated
                                            @click="handleSubmitClick"
                                            @touchstart="handleTouchStart"
                                        >
                                            <q-tooltip 
                                                v-if="!canSubmit && !isMobile" 
                                                class="submit-tooltip"
                                                anchor="top middle" 
                                                self="bottom middle"
                                                :offset="[0, 8]"
                                            >
                                                {{ getSubmitHint() }}
                                            </q-tooltip>
                                            <q-icon name="auto_awesome" class="submit-icon" />
                                            <span>Создать работу</span>
                                        </q-btn>
                                    </div>
                                    
                                                        <!-- Время генерации -->
                    <div class="time-estimate">
                        <q-icon name="schedule" class="time-icon" />
                        <span>Общее время генерации: 5-10 минут</span>
                    </div>

                    <!-- reCAPTCHA бейдж -->
                    <div v-if="recaptcha.enabled" class="recaptcha-badge">
                        Эта страница защищена reCAPTCHA. Действуют 
                        <a href="https://policies.google.com/privacy" target="_blank">Политика конфиденциальности</a> 
                        и 
                        <a href="https://policies.google.com/terms" target="_blank">Условия использования</a> 
                        Google.
                    </div>
                </div>
            </div>
        </q-form>
    </div>
</div>
            </div>
        </div>
    </page-layout>

         <!-- Модальное окно для выбора "Другого" типа работы -->
     <q-dialog v-model="showOtherTypesModal">
         <q-card class="other-types-modal">
             <q-card-section class="other-types-modal-header">
                 <h3>Выберите тип работы</h3>
                 <q-btn icon="close" flat round dense @click="toggleOtherTypesModal" />
             </q-card-section>
             <q-card-section class="other-types-modal-body">
                 <div class="other-types-list">
                     <button 
                         v-for="type in hiddenTypes" 
                         :key="type.id" 
                         class="other-type-item"
                         @click="selectOtherType(type)"
                     >
                         <span class="other-type-name">{{ type.name }}</span>
                     </button>
                 </div>
             </q-card-section>
         </q-card>
     </q-dialog>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';
import PageLayout from '@/components/shared/PageLayout.vue';
import YandexMetrika from '@/components/shared/YandexMetrika.vue';
import { Head } from '@inertiajs/vue3';
import { apiClient, isLoading, useLaravelErrors } from '@/composables/api';
import CustomInput from '@/components/shared/CustomInput.vue';
import { useRecaptcha } from '@/composables/recaptcha';

const $q = useQuasar();

const props = defineProps({
    document_types: {
        type: Array,
        required: true,
        default: () => []
    },
    recaptcha: {
        type: Object,
        default: () => ({
            site_key: null,
            enabled: false
        })
    },
    selected_type: {
        type: String,
        default: null
    }
});

const error = ref('');
const form = ref({
    document_type_id: null,
    topic: '',
    pages_num: 6
});

const showMobileHint = ref(false);
const mobileHintClosing = ref(false);
const isMobile = ref(false);

// Состояние для модального окна "Другое"
const showOtherTypesModal = ref(false);

// Состояние для отображения типов работ
const displayedTypes = ref([]);
const hiddenTypes = ref([]);

const { hasError, getError } = useLaravelErrors();

// reCAPTCHA
const { initRecaptcha, executeAction, isReady: isRecaptchaReady, getError: getRecaptchaError } = useRecaptcha();

// Computed свойства
const canSubmit = computed(() => {
    return form.value.document_type_id && form.value.topic.trim().length >= 10;
});

const currentStep = computed(() => {
    // На странице создания документа всегда активен первый шаг
    return 1;
});

// Инициализация типов работ
const initializeWorkTypes = () => {
    const maxDisplayed = isMobile.value ? 3 : 5;
    
    if (props.document_types.length <= maxDisplayed) {
        displayedTypes.value = [...props.document_types];
        hiddenTypes.value = [];
    } else {
        displayedTypes.value = props.document_types.slice(0, maxDisplayed);
        hiddenTypes.value = props.document_types.slice(maxDisplayed);
    }
};

// Маппинг английских параметров к типам документов
const getTypeMapping = () => {
    return {
        // Основные типы работ
        'practice-report': 'Отчет о практике',
        'coursework': 'Курсовая работа', 
        'report': 'Доклад',
        'essay': 'Эссе',
        'abstract': 'Реферат',
        'article': 'Научная статья',
        'diploma': 'Диплом',
        'control-work': 'Контрольные работы',
        'graduation-work': 'ВКР',
        'dissertation': 'Диссертация',
        'composition': 'Сочинение',
        'vak': 'ВАК',
        'synopsis': 'Автореферат',
        'mba': 'Диплом MBA',
        'phd': 'Кандидатская диссертация',
        'masters': 'Магистреская диссертация',
        'review': 'Рецензия',
        'research': 'Исследовательские работы',
        'conclusion': 'Выводы',
        'introduction': 'Введение',
        'homework': 'Домашняя работа',
        'research-work': 'Научно исследовательская работа',
        
        // Альтернативные названия
        'course': 'Курсовая работа',
        'course-work': 'Курсовая работа',
        'thesis': 'Диплом',
        'paper': 'Научная статья',
        'scientific-article': 'Научная статья',
        'graduate': 'ВКР',
        'bachelor': 'ВКР',
        'master': 'Магистреская диссертация',
        'masters-thesis': 'Магистреская диссертация',
        'candidate': 'Кандидатская диссертация',
        'phd-thesis': 'Кандидатская диссертация',
        'scientific-work': 'Научно исследовательская работа',
        'home-assignment': 'Домашняя работа'
    };
};

// Функция для получения всех доступных параметров (для разработчиков/API)
const getAllAvailableTypes = () => {
    return Object.keys(getTypeMapping()).sort();
};

// Установка выбранного типа работы на основе URL параметра или автовыбор первого
// 
// ПОЛНЫЙ СПИСОК ДОСТУПНЫХ ПАРАМЕТРОВ:
// 
// Основные типы работ:
// /new?type=practice-report      - Отчет о практике
// /new?type=coursework          - Курсовая работа
// /new?type=report              - Доклад
// /new?type=essay               - Эссе
// /new?type=abstract            - Реферат
// /new?type=article             - Научная статья
// /new?type=diploma             - Диплом
// /new?type=control-work        - Контрольные работы
// /new?type=graduation-work     - ВКР
// /new?type=dissertation        - Диссертация
// /new?type=composition         - Сочинение
// /new?type=vak                 - ВАК
// /new?type=synopsis            - Автореферат
// /new?type=mba                 - Диплом MBA
// /new?type=phd                 - Кандидатская диссертация
// /new?type=masters             - Магистреская диссертация
// /new?type=review              - Рецензия
// /new?type=research            - Исследовательские работы
// /new?type=conclusion          - Выводы
// /new?type=introduction        - Введение
// /new?type=homework            - Домашняя работа
// /new?type=research-work       - Научно исследовательская работа
//
// Альтернативные названия:
// /new?type=course              - Курсовая работа
// /new?type=course-work         - Курсовая работа
// /new?type=thesis              - Диплом
// /new?type=paper               - Научная статья
// /new?type=scientific-article  - Научная статья
// /new?type=graduate            - ВКР
// /new?type=bachelor            - ВКР
// /new?type=master              - Магистреская диссертация
// /new?type=masters-thesis      - Магистреская диссертация
// /new?type=candidate           - Кандидатская диссертация
// /new?type=phd-thesis          - Кандидатская диссертация
// /new?type=scientific-work     - Научно исследовательская работа
// /new?type=home-assignment     - Домашняя работа
//
// Если параметр не указан, автоматически выбирается первый тип (Отчет о практике)
const setSelectedType = () => {
    let targetType = null;
    
    // Если передан параметр selected_type, ищем его среди всех типов
    if (props.selected_type) {
        const searchTerm = props.selected_type.toLowerCase().trim();
        const typeMapping = getTypeMapping();
        
        // Сначала ищем по английскому маппингу
        if (typeMapping[searchTerm]) {
            const mappedName = typeMapping[searchTerm];
            targetType = props.document_types.find(type => 
                type.name === mappedName
            );
        }
        
        // Если не найден по маппингу, ищем по обычным правилам (для обратной совместимости)
        if (!targetType) {
            targetType = props.document_types.find(type => 
                type.slug === props.selected_type || 
                type.slug === searchTerm ||
                type.name.toLowerCase() === searchTerm ||
                type.name.toLowerCase().includes(searchTerm)
            );
        }
    }
    
    // Если тип не найден или не передан, выбираем первый
    if (!targetType && props.document_types.length > 0) {
        targetType = props.document_types[0];
    }
    
    if (targetType) {
        // Проверяем, есть ли тип в отображаемых
        const isDisplayed = displayedTypes.value.find(type => type.id === targetType.id);
        
        if (!isDisplayed) {
            // Если типа нет в отображаемых, заменяем последний отображаемый тип
            const lastDisplayedType = displayedTypes.value.pop();
            if (lastDisplayedType) {
                hiddenTypes.value.push(lastDisplayedType);
            }
            
            // Удаляем целевой тип из скрытых и добавляем в отображаемые
            hiddenTypes.value = hiddenTypes.value.filter(type => type.id !== targetType.id);
            displayedTypes.value.push(targetType);
        }
        
        // Устанавливаем как выбранный
        form.value.document_type_id = targetType.id;
    }
};

// Показать/скрыть модальное окно "Другое"
const toggleOtherTypesModal = () => {
    showOtherTypesModal.value = !showOtherTypesModal.value;
};

// Выбор типа из модального окна
const selectOtherType = (selectedType) => {
    // Находим текущий выбранный тип в основном списке (если есть)
    const currentSelectedIndex = displayedTypes.value.findIndex(type => type.id === form.value.document_type_id);
    
    // Если есть выбранный тип в основном списке, перемещаем его в скрытые
    if (currentSelectedIndex !== -1) {
        const currentSelectedType = displayedTypes.value[currentSelectedIndex];
        hiddenTypes.value.push(currentSelectedType);
    }
    
    // Удаляем выбранный тип из скрытых
    hiddenTypes.value = hiddenTypes.value.filter(type => type.id !== selectedType.id);
    
    // Если в основном списке был выбранный тип, заменяем его
    if (currentSelectedIndex !== -1) {
        displayedTypes.value[currentSelectedIndex] = selectedType;
    } else {
        // Если не было выбранного типа, заменяем последний элемент
        const lastType = displayedTypes.value.pop();
        if (lastType) {
            hiddenTypes.value.push(lastType);
        }
        displayedTypes.value.push(selectedType);
    }
    
    // Устанавливаем выбранный тип
    form.value.document_type_id = selectedType.id;
    
    // Закрываем модальное окно
    showOtherTypesModal.value = false;
};

// Методы
const checkMobile = () => {
    const wasMobile = isMobile.value;
    isMobile.value = window.innerWidth <= 768 || /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    
    // Если изменился режим (мобильный/десктопный), переинициализируем типы работ
    if (wasMobile !== isMobile.value) {
        const selectedTypeId = form.value.document_type_id;
        initializeWorkTypes();
        // Восстанавливаем выбранный тип после переинициализации
        if (selectedTypeId) {
            form.value.document_type_id = selectedTypeId;
        }
    }
};

const selectWorkType = (type) => {
    form.value.document_type_id = type.id;
};

const getSubmitHint = () => {
    if (!form.value.document_type_id && form.value.topic.trim().length < 10) {
        return 'Для продолжения выбери тип работы и введи тему (минимум 10 символов)';
    }
    if (!form.value.document_type_id) {
        return 'Для продолжения выбери тип работы выше';
    }
    if (form.value.topic.trim().length < 10) {
        return 'Для продолжения введи тему работы (минимум 10 символов)';
    }
    return '';
};

const handleSubmitClick = (event) => {
    if (!canSubmit.value) {
        if (isMobile.value) {
            event.preventDefault();
            event.stopPropagation();
            
            showMobileHint.value = true;
            mobileHintClosing.value = false;
            setTimeout(() => {
                mobileHintClosing.value = true;
                setTimeout(() => {
                    showMobileHint.value = false;
                    mobileHintClosing.value = false;
                }, 300); // Время анимации исчезновения
            }, 4000); // Увеличиваем время показа до 4 секунд
        }
        return;
    }
    
    // Если можем отправить форму, вызываем onSubmit
    if (canSubmit.value && !isLoading.value) {
        onSubmit();
    }
};

const handleTouchStart = (event) => {
    if (!canSubmit.value && isMobile.value) {
        event.preventDefault();
        
        showMobileHint.value = true;
        mobileHintClosing.value = false;
        setTimeout(() => {
            mobileHintClosing.value = true;
            setTimeout(() => {
                showMobileHint.value = false;
                mobileHintClosing.value = false;
            }, 300); // Время анимации исчезновения
        }, 4000);
    }
};

const incrementPages = () => {
    if (form.value.pages_num < 25) {
        form.value.pages_num++;
    }
};

const decrementPages = () => {
    if (form.value.pages_num > 4) {
        form.value.pages_num--;
    }
};

const onSubmit = async () => {
    try {
        error.value = '';

        // Проверка минимальной длины темы
        if (form.value.topic.trim().length < 10) {
            error.value = 'Тема работы должна содержать минимум 10 символов';
            return;
        }

        const data = {
            ...form.value,
            document_type_id: Number(form.value.document_type_id)
        };

        // Добавляем reCAPTCHA токен, если включена
        if (props.recaptcha.enabled && props.recaptcha.site_key) {
            try {
                const recaptchaToken = await executeAction('document_create');
                data.recaptcha_token = recaptchaToken;
            } catch (recaptchaError) {
                console.error('reCAPTCHA error:', recaptchaError);
                error.value = 'Ошибка проверки безопасности. Попробуйте обновить страницу.';
                isLoading.value = false;
                return;
            }
        }

        const response = await apiClient.post(route('documents.quick-create'), data);
        
        if (response && response.document && response.document.id) {
            const redirectUrl = response.redirect_url || route('documents.show', {
                document: response.document.id,
                autoload: 1
            });
            router.visit(redirectUrl);
        } else {
            throw new Error('Неверный формат ответа от сервера');
        }
    } catch (err) {
        isLoading.value = false
        // console.error('Ошибка при создании документа:', err);  // Закомментировано для продакшена
        
        // Проверяем специфические ошибки reCAPTCHA
        if (err.response && err.response.data && err.response.data.recaptcha_error) {
            error.value = 'Проверка безопасности не пройдена. Попробуйте ещё раз.';
        } else {
            $q.notify({
                type: 'negative',
                message: 'Произошла ошибка при создании документа',
                position: 'top'
            });
        }
    }
};

const getMobileHintText = () => {
    return getSubmitHint();
};

onMounted(async () => {
    checkMobile();
    initializeWorkTypes();
    setSelectedType();
    window.addEventListener('resize', checkMobile);
    
    // Инициализируем reCAPTCHA, если включена
    if (props.recaptcha.enabled && props.recaptcha.site_key) {
        try {
            await initRecaptcha(props.recaptcha.site_key);
        } catch (error) {
            console.error('Failed to initialize reCAPTCHA:', error);
        }
    }
    
    // Добавляем функцию в глобальную область для тестирования (только в dev режиме)
    if (import.meta.env.DEV) {
        window.getAllDocumentTypes = () => {
            console.log('Доступные параметры для /new?type=PARAMETER:');
            console.table(getTypeMapping());
            console.log('Список всех параметров:', getAllAvailableTypes());
            return getAllAvailableTypes();
        };
        console.log('💡 Для просмотра всех доступных типов документов выполните: getAllDocumentTypes()');
    }
});

onUnmounted(() => {
    window.removeEventListener('resize', checkMobile);
});
</script>

<style scoped>
.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 40px 24px;
    min-height: 100vh;
}

/* Мобильный заголовок и прогресс */
.mobile-header {
    display: none;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 32px;
    position: relative;
}

.mobile-title {
    font-size: 28px;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0;
    letter-spacing: -0.02em;
    line-height: 1.1;
    text-align: center;
}

.mobile-progress {
    display: flex;
    flex-direction: column;
    gap: 8px;
    align-items: center;
}

.progress-bar {
    width: 100%;
    max-width: 300px;
    height: 6px;
    background: #e2e8f0;
    border-radius: 3px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    border-radius: 3px;
    transition: width 0.3s ease;
}

.progress-text {
    font-size: 14px;
    color: #6b7280;
    font-weight: 500;
}

/* Двухколоночный layout */
.main-content {
    display: grid;
    grid-template-columns: 1fr 700px;
    gap: 60px;
    align-items: start;
}

/* Левая колонка с информацией */
.info-column {
    display: flex;
    flex-direction: column;
    gap: 40px;
}

.header-section {
    text-align: left;
}

.main-title {
    font-size: 48px;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0;
    letter-spacing: -0.02em;
    line-height: 1.1;
}

/* Информационная карточка */
.info-card {
    background: #ffffff;
    border-radius: 24px;
    padding: 32px;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
    border: 1px solid #f1f5f9;
    position: sticky;
    top: calc(80px + 24px);
}

.info-title {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 24px;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0 0 24px 0;
}

.info-icon {
    font-size: 28px;
    color: #3b82f6;
}

.info-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 16px;
    color: #374151;
    line-height: 1.5;
}

.list-icon {
    font-size: 20px;
    color: #3b82f6;
    flex-shrink: 0;
}

/* Карточка с шагами */
.steps-card {
    background: #ffffff;
    border-radius: 24px;
    padding: 32px;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
    border: 1px solid #f1f5f9;
    position: sticky;
    top: calc(80px + 24px);
}

.steps-title {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 24px;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0 0 32px 0;
}

.steps-icon {
    font-size: 28px;
    color: #3b82f6;
}

.steps-blocks {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.step-block {
    padding: 16px 20px;
    border-radius: 12px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
}

.step-block.active {
    background: #3b82f6;
    border-color: #3b82f6;
}

.step-block-title {
    font-size: 16px;
    font-weight: 500;
    color: #6b7280;
    margin: 0;
    line-height: 1.4;
    transition: all 0.3s ease;
}

.step-block.active .step-block-title {
    color: #ffffff;
    font-weight: 600;
}

/* Правая колонка с формой */
.form-column {
    width: 100%;
}

.form-container {
    background: #ffffff;
    border-radius: 24px;
    padding: 36px;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
    border: 1px solid #f1f5f9;
    width: 100%;
}

.document-form {
    display: flex;
    flex-direction: column;
    gap: 28px;
}

.form-section {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.section-title {
    font-size: 22px;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0;
    letter-spacing: -0.01em;
}

.section-description {
    font-size: 15px;
    color: #6b7280;
    margin: 0;
    line-height: 1.4;
}

/* Кнопки выбора типа работы */
.work-type-buttons {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
}

.work-type-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px 12px;
    border: 2px solid #e2e8f0;
    border-radius: 16px;
    background: #ffffff;
    cursor: pointer;
    transition: all 0.2s ease;
    min-height: 60px;
}

.work-type-btn:hover {
    border-color: #3b82f6;
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(59, 130, 246, 0.15);
}

.work-type-btn.active {
    border-color: #3b82f6;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    box-shadow: 0 8px 24px rgba(59, 130, 246, 0.25);
}

.work-type-name {
    font-size: 14px;
    font-weight: 600;
    text-align: center;
    line-height: 1.2;
}

.other-type-btn {
    border-color: #e2e8f0;
    background: #ffffff;
    color: #374151;
}

.other-type-btn:hover {
    border-color: #3b82f6;
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(59, 130, 246, 0.15);
}

/* Поле ввода темы */
.input-container {
    position: relative;
}

/* Счетчик страниц */
.pages-input-container {
    display: flex;
    justify-content: center;
}

.pages-counter {
    display: flex;
    align-items: center;
    gap: 0;
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 16px;
    overflow: hidden;
}

.counter-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border: none;
    background: #ffffff;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s ease;
}

.counter-btn:hover:not(:disabled) {
    background: #3b82f6;
    color: white;
}

.counter-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.pages-display {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 0 32px;
    min-width: 120px;
    background: white;
}

.pages-number {
    font-size: 24px;
    font-weight: 700;
    color: #1a1a1a;
    line-height: 1;
}

.pages-label {
    font-size: 14px;
    color: #6b7280;
    font-weight: 500;
}

/* Кнопка отправки */
.submit-section {
    margin-top: 8px;
}

.submit-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}

.submit-wrapper {
    display: inline-block;
}

.submit-btn {
    padding: 16px 40px;
    border-radius: 16px;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    font-size: 17px;
    font-weight: 600;
    min-width: 200px;
    height: auto;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    position: relative;
}

.submit-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(59, 130, 246, 0.4);
}

.submit-btn:disabled {
    opacity: 0.6;
    transform: none;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
    cursor: not-allowed;
}

.submit-btn-disabled {
    opacity: 0.6;
    transform: none;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
    cursor: not-allowed;
    pointer-events: all !important;
}

.submit-icon {
    margin-right: 8px;
    font-size: 20px;
}

/* Время генерации */
.time-estimate {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #6b7280;
    font-size: 13px;
    font-weight: 500;
}

.time-icon {
    font-size: 16px;
    color: #3b82f6;
}

/* Tooltip для кнопки */
.submit-tooltip {
    background: #1f2937 !important;
    color: white !important;
    border-radius: 8px !important;
    padding: 8px 12px !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
}

/* Мобильная подсказка */
.mobile-hint {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 16px 20px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    color: #475569;
    font-size: 14px;
    font-weight: 500;
    animation: slideInDown 0.3s ease-out;
    max-width: 100%;
    text-align: center;
    margin: 0 auto 16px auto;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    position: relative;
}

.mobile-hint-closing {
    animation: slideOutUp 0.3s ease-in;
}

.mobile-hint-icon {
    font-size: 18px;
    color: #64748b;
    flex-shrink: 0;
}

/* Стрелка вниз для указания на кнопку */
.mobile-hint::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 0;
    border-left: 8px solid transparent;
    border-right: 8px solid transparent;
    border-top: 8px solid #e2e8f0;
}

.mobile-hint::before {
    content: '';
    position: absolute;
    bottom: -7px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 0;
    border-left: 7px solid transparent;
    border-right: 7px solid transparent;
    border-top: 7px solid #f1f5f9;
}

@keyframes slideInDown {
    0% { 
        opacity: 0; 
        transform: translateY(-20px); 
    }
    100% { 
        opacity: 1; 
        transform: translateY(0); 
    }
}

@keyframes slideOutUp {
    0% { 
        opacity: 1; 
        transform: translateY(0); 
    }
    100% { 
        opacity: 0; 
        transform: translateY(-20px); 
    }
}

/* Ошибки */
.error-message {
    color: #ef4444;
    font-size: 14px;
    margin-top: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.global-error {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 12px;
    color: #dc2626;
    font-size: 16px;
}

.error-icon {
    font-size: 20px;
}

/* Адаптивность */
@media (max-width: 1200px) {
    .main-content {
        grid-template-columns: 1fr 650px;
        gap: 40px;
    }
    
    .main-title {
        font-size: 42px;
    }
}

@media (max-width: 1024px) {
    .main-content {
        grid-template-columns: 1fr;
        gap: 40px;
    }
    
    .info-column {
        order: 1;
    }
    
    .form-column {
        order: 0;
    }
    
    .steps-card {
        position: static;
    }
    
    .work-type-buttons {
        grid-template-columns: 1fr;
    }
    
    .work-type-btn {
        min-height: 80px;
        padding: 16px;
    }
    
    .work-type-name {
        font-size: 16px;
    }
    
    /* На мобильных показываем кнопку "Другое" если есть скрытые типы */
}

@media (max-width: 768px) {
    .container {
        padding: 24px 16px;
    }
    
    /* Показываем мобильный заголовок */
    .mobile-header {
        display: flex;
    }
    
    /* Скрываем десктопный заголовок и блок шагов */
    .header-section,
    .steps-card {
        display: none;
    }
    
    .main-title {
        font-size: 36px;
    }
    
    .form-container {
        padding: 32px 24px;
        border-radius: 20px;
    }
    
    .submit-btn {
        padding: 16px 32px;
        font-size: 16px;
        min-width: 180px;
    }
}

@media (max-width: 480px) {
    .main-title {
        font-size: 28px;
    }
    
    .section-title {
        font-size: 20px;
    }
    
    .form-container {
        padding: 24px 20px;
    }
    
    .pages-display {
        padding: 0 24px;
        min-width: 100px;
    }
    
    .submit-btn {
        min-width: 160px;
    }
}

/* Визуализация структуры документа */
.document-structure {
    margin-bottom: 16px;
}

.structure-line {
    display: flex;
    align-items: stretch;
    gap: 0;
    height: 40px;
}

.structure-segment {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    position: relative;
    transition: flex 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94); /* Плавная анимация изменения ширины */
}

.segment-bar {
    height: 20px;
    width: 100%;
    transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94); /* Плавная анимация всех изменений */
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

/* Скругления только у крайних блоков */
.title-page .segment-bar {
    background: #94a3b8;
    border-radius: 4px 0 0 4px;
}

.contents-page .segment-bar {
    background: #3b82f6;
    border-radius: 0;
}

.main-text .segment-bar {
    background: #10b981;
    border-radius: 0;
}

.references-page .segment-bar {
    background: #f59e0b;
    border-radius: 0 4px 4px 0;
}

.segment-pages-on-bar {
    font-size: 13px;
    font-weight: 700;
    color: white;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease; /* Анимация изменения текста */
}

.segment-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    text-align: center;
}

.segment-name {
    font-size: 11px;
    font-weight: 600;
    color: #374151;
    line-height: 1.2;
    transition: all 0.3s ease; /* Анимация текста подписи */
}

/* Эффект пульсации при изменении значения */
@keyframes pulse {
    0% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 0 0 8px rgba(16, 185, 129, 0);
    }
    100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
    }
}

.main-text .segment-bar:hover {
    animation: pulse 0.6s ease-in-out;
}

/* reCAPTCHA бейдж */
.recaptcha-badge {
    font-size: 11px;
    color: #9ca3af;
    text-align: center;
    line-height: 1.4;
    margin-top: 8px;
}

.recaptcha-badge a {
    color: #3b82f6;
    text-decoration: none;
}

.recaptcha-badge a:hover {
    text-decoration: underline;
}

/* Адаптивность для структуры документа */
@media (max-width: 768px) {
    .document-structure {
        margin-bottom: 12px;
    }
    
    .structure-line {
        height: 35px;
    }
    
    .segment-bar {
        height: 18px;
    }
    
    .segment-pages-on-bar {
        font-size: 12px;
    }
    
    .segment-name {
        font-size: 10px;
    }
}

@media (max-width: 480px) {
    .document-structure {
        margin-bottom: 10px;
    }
    
    .structure-line {
        height: 32px;
        gap: 0;
    }
    
    .segment-bar {
        height: 16px;
    }
    
    .segment-pages-on-bar {
        font-size: 11px;
    }
    
    .segment-name {
        font-size: 9px;
    }
}

/* Модальное окно для выбора "Другого" типа работы */
.other-types-modal {
    width: 100%;
    max-width: 600px;
    max-height: 80vh;
    border-radius: 20px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.other-types-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    flex-shrink: 0;
}

.other-types-modal-header h3 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    color: #1a1a1a;
}

.other-types-modal-body {
    padding: 24px;
    overflow-y: auto;
    flex: 1;
    min-height: 0;
}

/* Кастомный скроллбар для модального окна */
.other-types-modal-body::-webkit-scrollbar {
    width: 6px;
}

.other-types-modal-body::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

.other-types-modal-body::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.other-types-modal-body::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.other-types-list {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
}

.other-type-item {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    padding: 16px 20px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    background: #ffffff;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: left;
    width: 100%;
}

.other-type-item:hover {
    border-color: #3b82f6;
    background: #f8fafc;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

.other-type-name {
    font-size: 16px;
    font-weight: 500;
    color: #374151;
    line-height: 1.4;
}

/* Адаптивность модального окна */
@media (max-width: 768px) {
    .other-types-modal {
        max-width: calc(100vw - 32px);
        max-height: 70vh;
        margin: 16px;
    }
    
    .other-types-modal-header {
        padding: 16px 20px;
    }
    
    .other-types-modal-header h3 {
        font-size: 18px;
    }
    
    .other-types-modal-body {
        padding: 20px;
    }
    
    .other-types-list {
        grid-template-columns: 1fr;
    }
    
    .other-type-item {
        padding: 14px 16px;
    }
    
    .other-type-name {
        font-size: 15px;
    }
}
</style>