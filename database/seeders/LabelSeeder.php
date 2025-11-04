<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Label;

class LabelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $labels = [
            [
                'name' => 'Urgent',
                'color' => '#dc3545',
                'type' => 'predefined',
            ],
            [
                'name' => 'Important',
                'color' => '#fd7e14',
                'type' => 'predefined',
            ],
            [
                'name' => 'Bug',
                'color' => '#8b0000',
                'type' => 'predefined',
            ],
            [
                'name' => 'Feature',
                'color' => '#0d6efd',
                'type' => 'predefined',
            ],
            [
                'name' => 'In Progress',
                'color' => '#ffc107',
                'type' => 'predefined',
            ],
        ];

        foreach ($labels as $label) {
            Label::firstOrCreate(
                ['name' => $label['name'], 'type' => 'predefined'],
                $label
            );
        }
    }
}

