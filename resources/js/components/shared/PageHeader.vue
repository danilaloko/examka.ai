<template>
    <div class="page-header">
        <div class="header-container">
            <div class="header-content">
                <!-- Левая часть - Логотип -->
                <div class="logo-section">
                    <div class="logo-container" @click="onLogoClick">
                        <img src="/logo_mini.svg" >
                    </div>
                </div>
                
                <!-- Центральная часть - Заголовок -->
                <div class="title-section">
                    <page-title v-if="title" @page-title-click="emit('click:title')" class="header-title">
                        {{ title }}
                    </page-title>
                </div>
                
                <!-- Правая часть - Кнопки -->
                <div class="actions-section">
                    <!-- Кнопка поддержки с подписью -->
                    <button class="support-btn" @click="openSupport" title="Поддержка">
                        <i class="fas fa-headset"></i>
                        <span class="support-text">Поддержка</span>
                    </button>
                    
                    <!-- Кнопка админки (только для администраторов) -->
                    <button 
                        v-if="isAdmin" 
                        class="admin-btn" 
                        @click="openAdmin" 
                        title="Панель администратора"
                    >
                        <i class="fas fa-cog"></i>
                        <span class="admin-text">Админка</span>
                    </button>
                    
                    <!-- Кнопка выхода (только для авторизованных пользователей) -->
                    <button 
                        v-if="showLogoutButton" 
                        class="logout-btn" 
                        @click="handleLogout" 
                        title="Выйти из аккаунта"
                    >
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="logout-text">Выйти</span>
                    </button>
                    
                    <!-- Основная правая кнопка -->
                    <btn 
                        v-if="rightBtnIcon" 
                        :icon="rightBtnIcon" 
                        @click="onRightBtnClick"
                        class="main-action-btn"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { isAuthenticated, user, fullLogout, shouldShowLogoutButton, shouldShowLogoutButtonWithData } from '@/composables/auth';
import { router } from '@inertiajs/vue3';
import Btn from '@/components/shared/Btn.vue';
import PageTitle from '@/components/shared/PageTitle.vue';
import { computed } from 'vue';

const props = defineProps({
    title: {
        type: String
    },
    color: {
        type: String,
        default: 'primary'
    },
    leftBtnIcon: {
        type: String,
    },
    leftBtnRoute: {
        type: String
    },
    leftBtnGoBack: {
        type: Boolean,
        default: true
    },
    rightBtnIcon: {
        type: String
    },
    rightBtnRoute: {
        type: String
    },
    logoGoHome: {
        type: Boolean,
        default: false
    },
    documentsCount: {
        type: Number,
        default: 0
    },
    balance: {
        type: Number,
        default: 0
    }
});

const emit = defineEmits(['click:left', 'click:right', 'click:title']);

// Определяем должна ли показываться кнопка выхода
const showLogoutButton = computed(() => {
    return shouldShowLogoutButtonWithData(props.documentsCount, props.balance);
});

// Проверяем является ли пользователь администратором
const isAdmin = computed(() => {
    return isAuthenticated.value && user.value?.role_id === 1;
});

const onLogoClick = () => {
    window.location.href = '/';
};

const onLeftBtnClick = () => {
    if(props.leftBtnRoute) {
        return redirect(props.leftBtnRoute);
    }
    if(props.leftBtnGoBack) {
        return goBack();
    }
    return emit("click:left")
}

const onRightBtnClick = () => {
    if(props.rightBtnRoute) {
        return redirect(props.rightBtnRoute);
    }
    return emit("click:right")
}

const openSupport = () => {
    // Открытие поддержки
    window.open('https://t.me/examka_help', '_blank');
};

const openAdmin = () => {
    // Открытие админ-панели
    window.location.href = '/admin';
};

// Обработчик выхода из системы
const handleLogout = async () => {
    try {
        await fullLogout();
        // Перенаправляем на главную страницу
        window.location.href = '/';
    } catch (error) {
        console.error('Ошибка при выходе:', error);
        // В случае ошибки всё равно перенаправляем на главную
        window.location.href = '/';
    }
};
</script>

<style scoped>
/* Подключение шрифта Bowler */
@font-face {
    font-family: 'Bowler';
    src: url('/fonts/Bowler.ttf') format('truetype');
    font-weight: normal;
    font-style: normal;
    font-display: swap;
}

img {
    height: 32px;
    width: auto;
}

.page-header {
    position: sticky;
    top: 0;
    z-index: 1000;
    width: 100%;
    background: #ffffff;
    border-bottom: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.header-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 64px;
    gap: 20px;
}

/* Логотип */
.logo-section {
    flex: 0 0 auto;
}

.logo-container {
    display: flex;
    align-items: center;
    cursor: pointer;
    padding: 10px 16px;
    border-radius: 12px;
}

.logo-image {
    height: 60px;
    width: auto;
    object-fit: contain;
    transition: all 0.3s ease;
}

.logo-container:hover .logo-image {
    transform: scale(1.05);
}

/* Заголовок */
.title-section {
    flex: 1;
    text-align: center;
}

.header-title {
    font-size: 20px;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}

/* Действия */
.actions-section {
    flex: 0 0 auto;
    display: flex;
    align-items: center;
    gap: 12px;
}

.support-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border: none;
    border-radius: 12px;
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.support-btn:hover {
    background: rgba(16, 185, 129, 0.2);
    color: #059669;
    transform: scale(1.02);
}

.support-btn i {
    font-size: 16px;
}

.support-text {
    font-size: 14px;
    font-weight: 500;
}

.logout-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border: none;
    border-radius: 12px;
    background: rgba(248, 113, 113, 0.1);
    color: #f87171;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.logout-btn:hover {
    background: rgba(248, 113, 113, 0.2);
    color: #dc2626;
    transform: scale(1.02);
}

.logout-btn i {
    font-size: 16px;
}

.logout-text {
    font-size: 14px;
    font-weight: 500;
}

.admin-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border: none;
    border-radius: 12px;
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.admin-btn:hover {
    background: rgba(245, 158, 11, 0.2);
    color: #d97706;
    transform: scale(1.02);
}

.admin-btn i {
    font-size: 16px;
}

.admin-text {
    font-size: 14px;
    font-weight: 500;
}

.main-action-btn {
    border-radius: 12px;
}

/* Адаптивность */
@media (max-width: 768px) {
    .header-container {
        padding: 0 16px;
    }
    
    .header-content {
        height: 56px;
        gap: 12px;
    }
    
    .logo-container {
        padding: 8px 12px;
    }
    
    .logo-image {
        height: 48px;
        width: auto;
    }
    
    .header-title {
        font-size: 18px;
    }
    
    .support-btn {
        padding: 8px 12px;
        gap: 6px;
    }
    
    .support-btn i {
        font-size: 14px;
    }
    
    .support-text {
        display: none;
    }
    
    .logout-btn {
        padding: 8px 12px;
        gap: 6px;
    }
    
    .logout-btn i {
        font-size: 14px;
    }
    
    .logout-text {
        display: none;
    }
    
    .admin-btn {
        display: flex;
        align-items: center;
        background: #f59e0b;
        border: none;
        border-radius: 8px;
        color: white;
        font-size: 12px;
        font-weight: 500;
        padding: 8px 12px;
        gap: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .admin-btn:hover {
        background: #d97706;
        transform: translateY(-1px);
    }
    
    .admin-btn i {
        font-size: 14px;
    }
    
    .admin-text {
        display: none;
    }
    
    .actions-section {
        gap: 8px;
    }
}

@media (max-width: 480px) {
    .header-container {
        padding: 0 12px;
    }
    
    .header-content {
        height: 52px;
        gap: 8px;
    }
    
    .logo-container {
        padding: 6px 10px;
    }
    
    .logo-image {
        height: 42px;
        width: auto;
    }
    
    .header-title {
        font-size: 16px;
    }
    
    .support-btn {
        padding: 6px 10px;
        gap: 4px;
    }
    
    .support-btn i {
        font-size: 16px;
    }
    
    .support-text {
        display: none;
    }
    
    .logout-btn {
        padding: 6px 10px;
        gap: 4px;
    }
    
    .logout-btn i {
        font-size: 16px;
    }
    
    .logout-text {
        display: none;
    }
    
    .admin-btn {
        padding: 6px 10px;
        gap: 4px;
    }
    
    .admin-btn i {
        font-size: 16px;
    }
    
    .admin-text {
        display: none;
    }
}

@media (min-width: 768px) {
    .support-text {
        display: block;
    }
    
    .logout-text {
        display: block;
    }
    
    .admin-text {
        display: block;
    }
}
</style>

