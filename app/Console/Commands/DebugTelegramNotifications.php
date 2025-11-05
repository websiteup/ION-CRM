<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Setting;
use App\Models\Task;

class DebugTelegramNotifications extends Command
{
    protected $signature = 'telegram:debug {user_id}';
    protected $description = 'Debug notificări Telegram pentru un utilizator';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = User::find($userId);

        if (!$user) {
            $this->error("Utilizatorul cu ID {$userId} nu există!");
            return 1;
        }

        $this->info("=== Debug Notificări Telegram pentru {$user->name} ({$user->email}) ===\n");

        // Verifică token-ul bot
        $settings = Setting::first();
        $this->info("1. Token Telegram Bot:");
        if ($settings && $settings->telegram_bot_token) {
            $this->info("   ✅ Token configurat: " . substr($settings->telegram_bot_token, 0, 10) . "...");
        } else {
            $this->error("   ❌ Token NU este configurat!");
            return 1;
        }

        // Verifică chat ID
        $this->info("\n2. Telegram Chat ID:");
        if ($user->telegram_chat_id) {
            $this->info("   ✅ Chat ID: {$user->telegram_chat_id}");
        } else {
            $this->error("   ❌ Chat ID NU este configurat!");
            $this->info("   Instrucțiuni: Mergi la Profil → Conectare Telegram → Obține Chat ID");
            return 1;
        }

        // Verifică preferințele notificări
        $this->info("\n3. Preferințe Notificări:");
        $this->info("   notification_email_enabled: " . ($user->notification_email_enabled ? "✅ DA" : "❌ NU"));
        $this->info("   notification_telegram_enabled: " . ($user->notification_telegram_enabled ? "✅ DA" : "❌ NU"));
        $this->info("   notification_task_created: " . ($user->notification_task_created ? "✅ DA" : "❌ NU"));
        $this->info("   notification_task_assigned: " . ($user->notification_task_assigned ? "✅ DA" : "❌ NU"));
        $this->info("   notification_task_updated: " . ($user->notification_task_updated ? "✅ DA" : "❌ NU"));
        $this->info("   notification_task_deadline: " . ($user->notification_task_deadline ? "✅ DA" : "❌ NU"));

        // Verifică ce canale ar fi folosite pentru o notificare
        $this->info("\n4. Test Canale Notificări:");
        $task = Task::with(['column.board', 'project', 'assignedUser', 'creator'])->first();
        
        if ($task) {
            $notification = new \App\Notifications\TaskCreatedNotification($task);
            $channels = $notification->via($user);
            $this->info("   Canale disponibile: " . implode(', ', $channels ?: ['NICIUNUL']));
            
            if (in_array('telegram', $channels)) {
                $this->info("   ✅ Telegram este activat pentru acest tip de notificare");
            } else {
                $this->warn("   ⚠️ Telegram NU este activat pentru acest tip de notificare");
                $this->info("   Motive posibile:");
                if (!$user->notification_telegram_enabled) {
                    $this->info("     - notification_telegram_enabled = false");
                }
                if (!$user->notification_task_created) {
                    $this->info("     - notification_task_created = false");
                }
                if (!$user->telegram_chat_id) {
                    $this->info("     - telegram_chat_id este gol");
                }
            }
        } else {
            $this->warn("   ⚠️ Nu există task-uri în sistem pentru testare");
        }

        // Testează trimiterea unei notificări
        $this->info("\n5. Test Trimitere Notificare:");
        if ($task && in_array('telegram', $channels ?? [])) {
            try {
                $this->info("   Trimitere notificare de test...");
                $user->notify(new \App\Notifications\TaskCreatedNotification($task));
                $this->info("   ✅ Notificare trimisă cu succes! Verifică Telegram-ul.");
            } catch (\Exception $e) {
                $this->error("   ❌ Eroare la trimiterea notificării: " . $e->getMessage());
                $this->error("   Stack trace: " . $e->getTraceAsString());
            }
        } else {
            $this->warn("   ⚠️ Nu se poate trimite notificare - verifică configurația de mai sus");
        }

        $this->info("\n=== Debug Finalizat ===");
        return 0;
    }
}

