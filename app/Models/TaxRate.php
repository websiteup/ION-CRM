<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'rate',
        'description',
        'is_default',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_default' => 'boolean',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($taxRate) {
            if ($taxRate->is_default) {
                static::where('id', '!=', $taxRate->id)->update(['is_default' => false]);
            }
        });
    }

    /**
     * Get the default tax rate
     */
    public static function getDefault()
    {
        return static::where('is_default', true)->first();
    }
}

