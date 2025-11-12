<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_name',
        'default_language',
        'timezone',
        'date_format',
        'app_logo',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'smtp_from_name',
        'smtp_from_email',
        'smtp_test_email',
        'telegram_bot_token',
    ];

    protected $casts = [
        'smtp_port' => 'integer',
    ];

    /**
     * Get the first settings record or create one
     */
    public static function getSettings()
    {
        return static::firstOrCreate([]);
    }
}

