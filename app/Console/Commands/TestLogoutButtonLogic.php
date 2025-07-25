<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class TestLogoutButtonLogic extends Command
{
    protected $signature = 'test:logout-button {--user-id=}';
    protected $description = 'Тестирует логику отображения кнопки выхода для пользователей';

    public function handle()
    {
        $this->info('🧪 Тестирование логики кнопки выхода');
        $this->line('');

        if ($this->option('user-id')) {
            $user = User::find($this->option('user-id'));
            if (!$user) {
                $this->error('❌ Пользователь не найден');
                return 1;
            }
            $this->testSingleUser($user);
        } else {
            $this->testAllUsers();
        }

        return 0;
    }

    private function testSingleUser(User $user)
    {
        $this->info("👤 Тестирование пользователя: {$user->name} (ID: {$user->id})");
        $this->line("📧 Email: {$user->email}");
        $this->line("🕐 Создан: {$user->created_at}");
        $this->line("💰 Баланс: {$user->balance_rub} руб.");
        
        $documentsCount = $user->documents()->count();
        $this->line("📄 Документов: {$documentsCount}");
        
        // Показываем критерии
        $shouldShow = $this->shouldShowLogoutButton($user, $documentsCount);
        
        $this->line('');
        if ($shouldShow) {
            $this->info('✅ Кнопка выхода ПОКАЗЫВАЕТСЯ');
        } else {
            $this->warn('❌ Кнопка выхода СКРЫТА');
        }
        $this->line('');
    }

    private function testAllUsers()
    {
        $users = User::with('documents')->get();
        
        $showLogoutCount = 0;
        $hideLogoutCount = 0;
        $autoUsers = 0;
        $realUsers = 0;
        
        $this->info("📊 Всего пользователей: {$users->count()}");
        $this->line('');
        
        foreach ($users as $user) {
            $documentsCount = $user->documents->count();
            $shouldShow = $this->shouldShowLogoutButton($user, $documentsCount);
            
            if ($shouldShow) {
                $showLogoutCount++;
            } else {
                $hideLogoutCount++;
            }
            
            if ($user->hasAutoGeneratedEmail()) {
                $autoUsers++;
            } else {
                $realUsers++;
            }
            
            $status = $shouldShow ? '✅ Показать' : '❌ Скрыть';
            $emailType = $user->hasAutoGeneratedEmail() ? '[AUTO]' : '[REAL]';
            
            $this->line(sprintf(
                '%s %s %-30s %s (Документов: %d, Баланс: %.0f)',
                $status,
                $emailType,
                $user->name,
                $user->email,
                $documentsCount,
                $user->balance_rub
            ));
        }
        
        $this->line('');
        $this->info('📈 Статистика:');
        $this->line("   Показывать кнопку: {$showLogoutCount}");
        $this->line("   Скрывать кнопку: {$hideLogoutCount}");
        $this->line("   Автогенерированные: {$autoUsers}");
        $this->line("   Реальные: {$realUsers}");
        
        $this->line('');
        $this->info('🎯 Пустые аккаунты (кнопка скрыта):');
        foreach ($users as $user) {
            $documentsCount = $user->documents->count();
            if (!$this->shouldShowLogoutButton($user, $documentsCount)) {
                $ageMinutes = $user->created_at->diffInMinutes(now());
                $this->line("   - {$user->name} (возраст: {$ageMinutes} мин, email: {$user->email})");
            }
        }
    }

    private function shouldShowLogoutButton(User $user, int $documentsCount): bool
    {
        // 1. Если есть хотя бы 1 документ
        if ($documentsCount > 0) {
            return true;
        }
        
        // 2. Если баланс пополнен (больше 0)
        if ($user->balance_rub > 0) {
            return true;
        }
        
        // 3. Если пользователь связан с Telegram
        if ($user->telegram_id) {
            return true;
        }
        
        // 4. Если email НЕ является автогенерированным
        if (!$user->hasAutoGeneratedEmail()) {
            return true;
        }
        
        // 5. Если есть данные о согласии на обработку персональных данных
        if ($user->privacy_consent) {
            return true;
        }
        
        // 6. Если пользователь связан с Telegram (дата связывания)
        if ($user->telegram_linked_at) {
            return true;
        }
        
        // 7. Если аккаунт существует более 1 часа
        if ($user->created_at->diffInHours(now()) >= 1) {
            return true;
        }
        
        // 8. Если в person есть данные о том, что аккаунт не автосозданный
        if ($user->person && 
            isset($user->person['telegram']) && 
            isset($user->person['telegram']['auto_created']) && 
            $user->person['telegram']['auto_created'] === false) {
            return true;
        }
        
        // 9. Если пользователь имеет настройки или статистику
        if ($user->settings && !empty($user->settings)) {
            return true;
        }
        
        if ($user->statistics && !empty($user->statistics)) {
            return true;
        }
        
        // Если ни один из критериев не сработал - это "пустой" аккаунт
        return false;
    }
} 