<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SentEmail extends Model
{
    use HasFactory;

    protected $fillable = [
        'to',
        'from',
        'from_name',
        'subject',
        'body_html',
        'body_text',
        'headers',
        'attachments',
        'status',
        'error_message',
        'user_id',
        'related_type',
        'related_id',
        'sent_at',
    ];

    protected $casts = [
        'headers' => 'array',
        'attachments' => 'array',
        'sent_at' => 'datetime',
    ];

    /**
     * Get the user who sent the email
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related model
     */
    public function related()
    {
        return $this->morphTo();
    }
}

