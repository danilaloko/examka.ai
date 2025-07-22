<template>
    <Head title="Современный дизайн" />

    <page-layout 
        title="Демо"
        :auto-auth="true"
        left-btn-icon="fa-solid fa-arrow-left"
        :left-btn-go-back="true"
        :logo-go-home="true"
        right-btn-icon="fa-solid fa-user"
        right-btn-route="/lk"
    >
        <!-- Демонстрационный контент -->
        <div class="demo-content">
            <h2 class="demo-title">�� Современный дизайн Экзамка</h2>
            
            <div class="demo-cards">
                <div class="demo-card">
                    <div class="card-icon">
                        <i class="fas fa-palette"></i>
                    </div>
                    <h3>Новый дизайн</h3>
                    <p>Современный градиентный дизайн с эффектами стекломорфизма</p>
                </div>

                <div class="demo-card">
                    <div class="card-icon">
                        <i class="fas fa-font"></i>
                    </div>
                    <h3>Шрифт Bowler</h3>
                    <p>Логотип использует кастомный шрифт Bowler.ttf</p>
                </div>

                <div class="demo-card">
                    <div class="card-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Адаптивность</h3>
                    <p>Полностью адаптивный дизайн для всех устройств</p>
                </div>
            </div>

            <div class="demo-section">
                <h3>✨ Особенности нового дизайна:</h3>
                <ul class="feature-list">
                    <li>Градиентные фоны с эффектом размытия</li>
                    <li>Современная типографика с использованием Bowler</li>
                    <li>Стекломорфизм эффекты</li>
                    <li>Плавные анимации и переходы</li>
                    <li>Улучшенная навигация</li>
                    <li>Поддержка темной темы</li>
                    <li>Логотип только слева с шрифтом Bowler</li>
                </ul>
            </div>

            <div class="demo-buttons">
                <q-btn 
                    label="Вернуться к старому дизайну" 
                    color="secondary" 
                    @click="goToOldDesign"
                    class="demo-btn"
                />
                <q-btn 
                    label="Личный кабинет" 
                    color="primary" 
                    @click="goToLk"
                    class="demo-btn"
                />
            </div>
        </div>
    </page-layout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';

// Функции навигации
const goToOldDesign = () => {
    router.visit('/lk'); // Вернуться к старому дизайну ЛК
};

const goToLk = () => {
    router.visit('/lk');
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

.demo-content {
    padding: 20px 0;
}

.demo-title {
    font-family: 'Bowler', 'Inter', sans-serif;
    font-size: 32px;
    font-weight: 800;
    text-align: center;
    margin-bottom: 40px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    text-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.demo-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
    margin-bottom: 40px;
}

.demo-card {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 30px;
    text-align: center;
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.demo-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 16px 48px rgba(0, 0, 0, 0.15);
}

.card-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(45deg, #667eea, #764ba2);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
}

.card-icon i {
    font-size: 24px;
    color: white;
}

.demo-card h3 {
    font-family: 'Bowler', 'Inter', sans-serif;
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 12px;
    color: #333;
}

.demo-card p {
    color: #666;
    line-height: 1.6;
    font-size: 14px;
}

.demo-section {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(20px);
    border-radius: 16px;
    padding: 30px;
    margin-bottom: 30px;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.demo-section h3 {
    font-family: 'Bowler', 'Inter', sans-serif;
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 20px;
    color: #333;
}

.feature-list {
    list-style: none;
    padding: 0;
}

.feature-list li {
    padding: 8px 0;
    padding-left: 25px;
    position: relative;
    color: #555;
    line-height: 1.6;
}

.feature-list li::before {
    content: "✓";
    position: absolute;
    left: 0;
    color: #667eea;
    font-weight: bold;
    font-size: 16px;
}

.demo-buttons {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
}

.demo-btn {
    padding: 12px 24px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 12px;
    min-width: 180px;
}

/* Адаптивный дизайн */
@media (max-width: 768px) {
    .demo-title {
        font-size: 24px;
        margin-bottom: 30px;
    }
    
    .demo-cards {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .demo-card {
        padding: 24px;
    }
    
    .demo-section {
        padding: 24px;
    }
    
    .demo-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .demo-btn {
        min-width: 200px;
    }
}

@media (max-width: 480px) {
    .demo-title {
        font-size: 20px;
    }
    
    .demo-card {
        padding: 20px;
    }
    
    .demo-section {
        padding: 20px;
    }
}

/* Темная тема */
@media (prefers-color-scheme: dark) {
    .demo-card {
        background: rgba(26, 26, 46, 0.8);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .demo-card h3 {
        color: #fff;
    }
    
    .demo-card p {
        color: #ccc;
    }
    
    .demo-section {
        background: rgba(26, 26, 46, 0.8);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .demo-section h3 {
        color: #fff;
    }
    
    .feature-list li {
        color: #ccc;
    }
}
</style> 