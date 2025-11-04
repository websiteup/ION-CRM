<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardColumn extends Model
{
    use HasFactory;

    protected $fillable = [
        'board_id',
        'name',
        'position',
        'color',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    /**
     * Get the board for this column.
     */
    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    /**
     * Get the tasks for this column.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class)->orderBy('position');
    }
}

