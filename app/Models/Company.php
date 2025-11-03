<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'company_logo',
        'invoice_prefix',
        'proforma_prefix',
    ];

    /**
     * Get the first company record or create one
     */
    public static function getCompany()
    {
        return static::firstOrCreate([]);
    }
}

