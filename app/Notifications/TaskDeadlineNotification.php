<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;
use App\Models\Task;

class TaskDeadlineNotification extends Notification
{
    use Queueable;

    protected $task;
    protected $daysUntilDeadline;

    /**
     * Create a new notification instance.
     */
    public function __construct(Task $task, int $daysUntilDeadline = 0)
    {
        $this->task = $task;
        $this->daysUntilDeadline = $daysUntilDeadline;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        $channels = [];
        
        // Email notification
        if ($notifiable->notification_email_enabled && $notifiable->notification_task_deadline) {
            $channels[] = 'mail';
        }
        
        // Telegram notification
        if ($notifiable->notification_telegram_enabled && $notifiable->notification_task_deadline && $notifiable->telegram_chat_id) {
            $channels[] = 'telegram';
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $board = $this->task->column->board;
        $project = $this->task->project;
        
        $deadlineMessage = $this->daysUntilDeadline == 0 
            ? 'scade astăzi' 
            : ($this->daysUntilDeadline == 1 
                ? 'scade mâine' 
                : "scade în {$this->daysUntilDeadline} zile");
        
        return (new MailMessage)
                    ->subject('⚠️ Deadline aproape: ' . $this->task->title)
                    ->line('Un task atribuit ție are deadline-ul aproape.')
                    ->line('**Task:** ' . $this->task->title)
                    ->line('**Deadline:** ' . $this->task->due_date->format('d.m.Y') . ' (' . $deadlineMessage . ')')
                    ->line('**Proiect:** ' . ($project->name ?? 'N/A'))
                    ->line('**Board:** ' . ($board->name ?? 'N/A'))
                    ->action('Vezi Task', route('admin.boards.view', $board->id))
                    ->line('Mulțumim pentru utilizarea aplicației!');
    }

    /**
     * Get the Telegram representation of the notification.
     */
    public function toTelegram($notifiable)
    {
        if (!$notifiable->telegram_chat_id) {
            return null;
        }
        
        $board = $this->task->column->board;
        $project = $this->task->project;
        
        $deadlineMessage = $this->daysUntilDeadline == 0 
            ? 'scade astăzi' 
            : ($this->daysUntilDeadline == 1 
                ? 'scade mâine' 
                : "scade în {$this->daysUntilDeadline} zile");
        
        $message = "⚠️ *Deadline aproape*\n\n";
        $message .= "• *Task:* " . $this->task->title . "\n";
        $message .= "• *Deadline:* " . $this->task->due_date->format('d.m.Y') . " ({$deadlineMessage})\n";
        $message .= "• *Proiect:* " . ($project->name ?? 'N/A') . "\n";
        $message .= "• *Board:* " . ($board->name ?? 'N/A') . "\n";
        
        $url = route('admin.boards.view', $board->id);
        
        // Obținem token-ul din setări
        $settings = \App\Models\Setting::first();
        $token = $settings && $settings->telegram_bot_token ? $settings->telegram_bot_token : config('services.telegram.bot_token');
        
        $telegramMessage = TelegramMessage::create()
            ->to($notifiable->telegram_chat_id)
            ->content($message)
            ->button('Vezi Task', $url);
        
        // Setăm token-ul dacă este disponibil
        if ($token) {
            $telegramMessage->token($token);
        }
        
        return $telegramMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'board_id' => $this->task->column->board->id,
            'due_date' => $this->task->due_date->format('Y-m-d'),
            'days_until_deadline' => $this->daysUntilDeadline,
        ];
    }
}

