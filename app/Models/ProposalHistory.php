<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposalHistory extends Model
{
    use HasFactory;

    protected $table = 'proposal_history';

    protected $fillable = [
        'proposal_id',
        'event_type',
        'title',
        'description',
        'changes',
        'user_id',
        'event_date',
    ];

    protected $casts = [
        'changes' => 'array',
        'event_date' => 'datetime',
    ];

    /**
     * Get the proposal for this history entry.
     */
    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    /**
     * Get the user who performed this action.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get icon for event type
     */
    public function getIconAttribute(): string
    {
        return match($this->event_type) {
            'created' => 'bi-plus-circle',
            'updated' => 'bi-pencil',
            'sent' => 'bi-envelope',
            'accepted' => 'bi-check-circle',
            'rejected' => 'bi-x-circle',
            'expired' => 'bi-clock-history',
            'duplicated' => 'bi-files',
            default => 'bi-circle',
        };
    }

    /**
     * Get color for event type
     */
    public function getColorAttribute(): string
    {
        return match($this->event_type) {
            'created' => 'primary',
            'updated' => 'secondary',
            'sent' => 'info',
            'accepted' => 'success',
            'rejected' => 'danger',
            'expired' => 'warning',
            'duplicated' => 'info',
            default => 'secondary',
        };
    }
}

