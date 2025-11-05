<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProposalTemplate;
use App\Models\User;

class ProposalTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultTemplate = ProposalTemplate::where('is_default', true)->first();

        if (!$defaultTemplate) {
            // Get first user or null
            $firstUser = User::first();
            
            ProposalTemplate::create([
                'name' => 'Template Default',
                'subject' => 'Ofertă {{proposal_number}}',
                'content_html' => '<h2>Ofertă {{proposal_number}}</h2>
                
<p>Bună ziua {{client_name}},</p>

<p>Vă prezentăm oferta noastră pentru serviciile solicitate:</p>

<h3>{{proposal_title}}</h3>

<p><strong>Data ofertei:</strong> {{proposal_date}}</p>
<p><strong>Valabil până la:</strong> {{valid_until}}</p>

{{items_table}}

<div style="margin-top: 30px;">
    <table style="width: 300px; margin-left: auto; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd;"><strong>Subtotal:</strong></td>
            <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">{{subtotal}} {{currency_symbol}}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd;"><strong>Taxe:</strong></td>
            <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">{{tax_total}} {{currency_symbol}}</td>
        </tr>
        <tr style="background-color: #e0e0e0;">
            <td style="padding: 8px; border: 1px solid #ddd;"><strong>TOTAL:</strong></td>
            <td style="padding: 8px; border: 1px solid #ddd; text-align: right;"><strong>{{total}} {{currency_symbol}}</strong></td>
        </tr>
    </table>
</div>

<p>Toate prețurile sunt nete, plus taxe aplicabile.</p>

<p>Vă rugăm să ne contactați pentru orice întrebări.</p>

<p>Cu respect,<br>{{company_name}}</p>',
                'is_default' => true,
                'created_by' => $firstUser ? $firstUser->id : null,
                'updated_by' => $firstUser ? $firstUser->id : null,
            ]);
        }
    }
}

