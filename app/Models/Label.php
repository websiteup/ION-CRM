<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Label extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
        'type',
        'created_by',
    ];

    protected $casts = [
        'type' => 'string',
    ];

    /**
     * Get the tasks for this label.
     */
    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_labels');
    }

    /**
     * Get the user who created the label.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get predefined labels
     */
    public static function getPredefined()
    {
        return static::where('type', 'predefined')->get();
    }
}

