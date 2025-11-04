<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description_html',
        'client_id',
        'client_portal_access',
        'status',
        'billing_type',
        'currency_id',
        'fixed_rate',
        'hourly_rate',
        'start_date',
        'end_date',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'client_portal_access' => 'boolean',
        'status' => 'string',
        'billing_type' => 'string',
        'fixed_rate' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the boards for this project.
     */
    public function boards()
    {
        return $this->hasMany(Board::class);
    }

    /**
     * Get the client for this project.
     */
    public function client()
    {
        return $this->belongsTo(\App\Models\Admin\Client::class);
    }

    /**
     * Get the currency for this project.
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get the tasks for this project.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get the project members.
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'project_members');
    }

    /**
     * Get the user who created the project.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the project.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

