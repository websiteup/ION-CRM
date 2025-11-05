<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;
use App\Models\Task;

class TaskUpdatedNotification extends Notification
{
    use Queueable;

    protected $task;

    /**
     * Create a new notification instance.
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
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
        if ($notifiable->notification_email_enabled && $notifiable->notification_task_updated) {
            $channels[] = 'mail';
        }
        
        // Telegram notification
        if ($notifiable->notification_telegram_enabled && $notifiable->notification_task_updated && $notifiable->telegram_chat_id) {
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
        
        return (new MailMessage)
                    ->subject('Task actualizat: ' . $this->task->title)
                    ->line('Un task care te afectează a fost actualizat.')
                    ->line('**Task:** ' . $this->task->title)
                    ->line('**Proiect:** ' . ($project->name ?? 'N/A'))
                    ->line('**Board:** ' . ($board->name ?? 'N/A'))
                    ->line('**Coloană:** ' . ($this->task->column->name ?? 'N/A'))
                    ->when($this->task->assignedUser, function ($mail) {
                        return $mail->line('**Atribuit la:** ' . $this->task->assignedUser->name);
                    })
                    ->when($this->task->due_date, function ($mail) {
                        return $mail->line('**Data scadență:** ' . $this->task->due_date->format('d.m.Y'));
                    })
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
        
        $message = "✏️ *Task actualizat*\n\n";
        $message .= "• *Task:* " . $this->task->title . "\n";
        $message .= "• *Proiect:* " . ($project->name ?? 'N/A') . "\n";
        $message .= "• *Board:* " . ($board->name ?? 'N/A') . "\n";
        $message .= "• *Coloană:* " . ($this->task->column->name ?? 'N/A') . "\n";
        
        if ($this->task->assignedUser) {
            $message .= "• *Atribuit la:* " . $this->task->assignedUser->name . "\n";
        }
        
        if ($this->task->due_date) {
            $message .= "• *Data scadență:* " . $this->task->due_date->format('d.m.Y') . "\n";
        }
        
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
        ];
    }
}

