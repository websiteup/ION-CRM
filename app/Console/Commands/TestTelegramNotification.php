<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Setting;
use App\Models\Task;
use App\Models\BoardColumn;
use App\Models\Board;
use App\Models\Project;
use App\Notifications\TaskCreatedNotification;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskUpdatedNotification;
use App\Notifications\TaskDeadlineNotification;
use NotificationChannels\Telegram\TelegramMessage;
use Illuminate\Support\Facades\Http;

class TestTelegramNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:test 
                            {--user= : ID-ul utilizatorului pentru testare}
                            {--type= : Tipul notificării (created, assigned, updated, deadline)}
                            {--simple : Trimite un mesaj simplu de testare}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testează notificările Telegram pentru utilizatori';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $settings = Setting::first();
        
        if (!$settings || !$settings->telegram_bot_token) {
            $this->error('❌ Token-ul Telegram Bot nu este configurat!');
            $this->info('Configurează token-ul în: Admin → Setări → General → Telegram Bot Token');
            return 1;
        }

        $this->info('✅ Token Telegram Bot găsit!');

        // Test simplu - trimite un mesaj direct
        if ($this->option('simple')) {
            return $this->testSimpleMessage($settings->telegram_bot_token);
        }

        // Test cu notificări
        $userId = $this->option('user');
        if (!$userId) {
            $this->error('❌ Trebuie să specifici ID-ul utilizatorului cu --user=ID');
            $this->info('Exemplu: php artisan telegram:test --user=1');
            return 1;
        }

        $user = User::find($userId);
        if (!$user) {
            $this->error("❌ Utilizatorul cu ID {$userId} nu există!");
            return 1;
        }

        if (!$user->telegram_chat_id) {
            $this->error("❌ Utilizatorul nu are Chat ID configurat!");
            $this->info('Instrucțiuni:');
            $this->info('1. Mergi la Profil → Conectare Telegram');
            $this->info('2. Începe o conversație cu bot-ul Telegram');
            $this->info('3. Apasă butonul "Obține Chat ID"');
            return 1;
        }

        $this->info("✅ Utilizator găsit: {$user->name} ({$user->email})");
        $this->info("✅ Chat ID: {$user->telegram_chat_id}");

        $type = $this->option('type') ?? 'created';

        switch ($type) {
            case 'created':
                return $this->testTaskCreated($user);
            case 'assigned':
                return $this->testTaskAssigned($user);
            case 'updated':
                return $this->testTaskUpdated($user);
            case 'deadline':
                return $this->testTaskDeadline($user);
            default:
                $this->error("❌ Tip invalid: {$type}");
                $this->info('Tipuri valide: created, assigned, updated, deadline');
                return 1;
        }
    }

    /**
     * Test simplu - trimite un mesaj direct
     */
    protected function testSimpleMessage($botToken)
    {
        $this->info('📤 Testare mesaj simplu...');
        
        // Încercăm să obținem chat IDs din updates
        $response = Http::get("https://api.telegram.org/bot{$botToken}/getUpdates");
        
        if (!$response->successful()) {
            $this->error('❌ Eroare la comunicarea cu Telegram API');
            $this->error('Răspuns: ' . $response->body());
            return 1;
        }

        $data = $response->json();
        
        if (!isset($data['ok']) || !$data['ok'] || empty($data['result'])) {
            $this->warn('⚠️ Nu există mesaje în bot. Începe o conversație cu bot-ul Telegram trimițând /start');
            return 1;
        }

        // Găsim ultimul chat_id
        $chatId = null;
        foreach ($data['result'] as $update) {
            if (isset($update['message']['from']['id'])) {
                $chatId = $update['message']['from']['id'];
            }
        }

        if (!$chatId) {
            $this->error('❌ Nu s-a găsit Chat ID');
            return 1;
        }

        $this->info("📱 Găsit Chat ID: {$chatId}");

        // Trimitem mesaj de test
        $message = "🧪 *Test Notificare Telegram*\n\n";
        $message .= "Acesta este un mesaj de testare!\n";
        $message .= "Dacă vezi acest mesaj, configurația Telegram funcționează corect.";

        $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown',
        ]);

        if ($response->successful()) {
            $this->info('✅ Mesaj trimis cu succes! Verifică Telegram-ul.');
            return 0;
        } else {
            $this->error('❌ Eroare la trimiterea mesajului');
            $this->error('Răspuns: ' . $response->body());
            return 1;
        }
    }

    /**
     * Test Task Created Notification
     */
    protected function testTaskCreated($user)
    {
        $this->info('📋 Testare notificare: Task Creat...');

        // Creăm un task de test
        $task = $this->createTestTask();
        
        if (!$task) {
            $this->error('❌ Nu s-a putut crea task-ul de test');
            return 1;
        }

        try {
            $user->notify(new TaskCreatedNotification($task));
            $this->info('✅ Notificare trimisă! Verifică Telegram-ul.');
            $this->info("📝 Task de test: {$task->title}");
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Eroare la trimiterea notificării: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Test Task Assigned Notification
     */
    protected function testTaskAssigned($user)
    {
        $this->info('👤 Testare notificare: Task Atribuit...');

        $task = $this->createTestTask();
        
        if (!$task) {
            $this->error('❌ Nu s-a putut crea task-ul de test');
            return 1;
        }

        try {
            $user->notify(new TaskAssignedNotification($task));
            $this->info('✅ Notificare trimisă! Verifică Telegram-ul.');
            $this->info("📝 Task de test: {$task->title}");
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Eroare la trimiterea notificării: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Test Task Updated Notification
     */
    protected function testTaskUpdated($user)
    {
        $this->info('✏️ Testare notificare: Task Actualizat...');

        $task = $this->createTestTask();
        
        if (!$task) {
            $this->error('❌ Nu s-a putut crea task-ul de test');
            return 1;
        }

        try {
            $user->notify(new TaskUpdatedNotification($task));
            $this->info('✅ Notificare trimisă! Verifică Telegram-ul.');
            $this->info("📝 Task de test: {$task->title}");
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Eroare la trimiterea notificării: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Test Task Deadline Notification
     */
    protected function testTaskDeadline($user)
    {
        $this->info('⚠️ Testare notificare: Deadline Aproape...');

        $task = $this->createTestTask();
        
        if (!$task) {
            $this->error('❌ Nu s-a putut crea task-ul de test');
            return 1;
        }

        try {
            $user->notify(new TaskDeadlineNotification($task, 1)); // 1 zi până la deadline
            $this->info('✅ Notificare trimisă! Verifică Telegram-ul.');
            $this->info("📝 Task de test: {$task->title}");
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Eroare la trimiterea notificării: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Creează un task de test
     */
    protected function createTestTask()
    {
        // Căutăm un proiect existent
        $project = Project::first();
        if (!$project) {
            $this->warn('⚠️ Nu există proiecte. Creează un proiect pentru a testa.');
            return null;
        }

        // Căutăm un board existent
        $board = Board::where('project_id', $project->id)->first();
        if (!$board) {
            $this->warn('⚠️ Nu există board-uri. Creează un board pentru a testa.');
            return null;
        }

        // Căutăm o coloană existentă
        $column = BoardColumn::where('board_id', $board->id)->first();
        if (!$column) {
            $this->warn('⚠️ Nu există coloane în board. Adaugă coloane pentru a testa.');
            return null;
        }

        // Creează task de test
        $task = Task::create([
            'title' => '🧪 Test Notificare Telegram - ' . now()->format('H:i:s'),
            'description_html' => '<p>Acesta este un task de test pentru notificări Telegram.</p>',
            'board_column_id' => $column->id,
            'project_id' => $project->id,
            'assigned_to' => null,
            'priority' => 'medium',
            'due_date' => now()->addDay(),
            'position' => 0,
            'created_by' => 1,
        ]);

        // Încărcăm relațiile necesare
        $task->load(['column.board', 'project', 'assignedUser', 'creator']);

        return $task;
    }
}

