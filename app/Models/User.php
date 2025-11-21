<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'nickname',
        'position',
        'email',
        'phone',
        'password',
        'profile_photo',
        'email_signature',
        'telegram_chat_id',
        'notification_email_enabled',
        'notification_telegram_enabled',
        'notification_task_created',
        'notification_task_assigned',
        'notification_task_updated',
        'notification_task_deadline',
        'dark_mode',
        'google_calendar_token',
        'google_calendar_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notification_email_enabled' => 'boolean',
            'notification_telegram_enabled' => 'boolean',
            'notification_task_created' => 'boolean',
            'notification_task_assigned' => 'boolean',
            'notification_task_updated' => 'boolean',
            'notification_task_deadline' => 'boolean',
            'dark_mode' => 'boolean',
        ];
    }

    /**
     * Get the roles that belong to the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole($role)
    {
        // Verificăm dacă rolurile sunt deja încărcate
        if (!$this->relationLoaded('roles')) {
            $this->load('roles');
        }
        
        if (is_string($role)) {
            return $this->roles->contains('slug', $role);
        }
        
        return $this->roles->contains('id', $role);
    }

    /**
     * Route notifications for the Telegram channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return string
     */
    public function routeNotificationForTelegram($notification)
    {
        return $this->telegram_chat_id;
    }
}
