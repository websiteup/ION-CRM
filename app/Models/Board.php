<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Board extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'project_id',
        'is_public',
        'public_hash',
        'background',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($board) {
            if ($board->is_public && !$board->public_hash) {
                $board->public_hash = Str::random(32);
            }
        });
    }

    /**
     * Get the project for this board.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the columns for this board.
     */
    public function columns()
    {
        return $this->hasMany(BoardColumn::class)->orderBy('position');
    }

    /**
     * Get all tasks for this board (through columns).
     */
    public function tasks()
    {
        return $this->hasManyThrough(Task::class, BoardColumn::class);
    }

    /**
     * Get the user who created the board.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the board.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the members of this board.
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'board_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Generate public hash for board
     */
    public function generatePublicHash()
    {
        do {
            $this->public_hash = Str::random(32);
        } while (Board::where('public_hash', $this->public_hash)->exists());
        $this->save();
    }
}

