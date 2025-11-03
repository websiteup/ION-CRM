<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'first_name', 'last_name', 'email', 'phone', 'country', 'address'];
    // Definirea relațiilor cu alte modele (de exemplu, Proposals)

}
