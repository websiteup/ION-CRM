<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Acces complet la toate funcțiile aplicației',
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'Poate gestiona clienți, servicii și propuneri',
            ],
            [
                'name' => 'Vânzări',
                'slug' => 'sales',
                'description' => 'Poate gestiona clienți și propuneri',
            ],
            [
                'name' => 'Utilizator',
                'slug' => 'user',
                'description' => 'Acces de bază la aplicație',
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}

