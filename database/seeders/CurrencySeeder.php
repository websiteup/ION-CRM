<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Currency;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            [
                'code' => 'EUR',
                'name' => 'Euro',
                'symbol' => '€',
                'rate' => 1.0000,
                'is_default' => true,
            ],
            [
                'code' => 'RON',
                'name' => 'Leu Românesc',
                'symbol' => 'RON',
                'rate' => 4.9500,
                'is_default' => false,
            ],
            [
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => '$',
                'rate' => 1.1000,
                'is_default' => false,
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::create($currency);
        }
    }
}

