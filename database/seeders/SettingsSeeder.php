<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;
use App\Models\Company;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default settings
        Setting::firstOrCreate([], [
            'app_name' => 'ION CRM',
            'default_language' => 'ro',
            'timezone' => 'Europe/Bucharest',
            'date_format' => 'd/m/Y',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
        ]);

        // Create default company
        Company::firstOrCreate([], [
            'name' => 'Compania Mea',
            'invoice_prefix' => 'INV-',
            'proforma_prefix' => 'PROF-',
        ]);
    }
}

