<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description_html',
        'board_column_id',
        'project_id',
        'assigned_to',
        'priority',
        'due_date',
        'google_calendar_event_id',
        'position',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'position' => 'integer',
    ];

    /**
     * Get the column for this task.
     */
    public function column()
    {
        return $this->belongsTo(BoardColumn::class, 'board_column_id');
    }

    /**
     * Get the project for this task.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user assigned to this task.
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the labels for this task.
     */
    public function labels()
    {
        return $this->belongsToMany(Label::class, 'task_labels');
    }

    /**
     * Get the user who created the task.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the task.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

