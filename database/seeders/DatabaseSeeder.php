<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Creăm mai întâi rolurile
        $this->call([
            RoleSeeder::class,
            CurrencySeeder::class,
            LanguageSeeder::class,
            SettingsSeeder::class,
            LabelSeeder::class,
        ]);

        // User::factory(10)->create();

        // Creăm utilizatorul de test
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Atribuim rolul de administrator utilizatorului de test
        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $user->roles()->attach($adminRole->id);
        }

        // Rulează ProposalTemplateSeeder după crearea user-ului
        $this->call([
            ProposalTemplateSeeder::class,
        ]);
    }
}
