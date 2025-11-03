<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Language;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            [
                'code' => 'ro',
                'name' => 'Română',
                'is_default' => true,
                'is_active' => true,
            ],
            [
                'code' => 'en',
                'name' => 'English',
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'code' => 'de',
                'name' => 'Deutsch',
                'is_default' => false,
                'is_active' => true,
            ],
        ];

        foreach ($languages as $language) {
            Language::create($language);
        }
    }
}

