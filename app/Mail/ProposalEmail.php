<?php

namespace App\Mail;

use App\Models\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProposalEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $proposal;

    /**
     * Create a new message instance.
     */
    public function __construct(Proposal $proposal)
    {
        $this->proposal = $proposal;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $proposal = $this->proposal;
        $proposal->load(['client', 'template', 'currency', 'items']);
        
        $subject = $proposal->template && $proposal->template->subject 
            ? $this->replaceShortcodes($proposal->template->subject, $proposal)
            : 'Ofertă #' . $proposal->proposal_number;

        $view = $this->view('emails.proposal')
            ->subject($subject)
            ->with([
                'proposal' => $proposal,
                'content' => $this->getProcessedContent($proposal),
            ]);

        // TODO: Add PDF attachment when PDF generation is implemented
        // $view->attachData($this->generatePdf($proposal), 'proposal-' . $proposal->proposal_number . '.pdf', [
        //     'mime' => 'application/pdf',
        // ]);

        return $view;
    }

    /**
     * Get processed content with shortcodes replaced
     */
    private function getProcessedContent(Proposal $proposal)
    {
        if (!$proposal->template) {
            return view('emails.proposal-default', ['proposal' => $proposal])->render();
        }

        $content = $this->replaceShortcodes($proposal->template->content_html, $proposal);
        return $content;
    }

    /**
     * Replace shortcodes in content
     */
    private function replaceShortcodes($content, Proposal $proposal)
    {
        $company = \App\Models\Company::getCompany();
        
        $replacements = [
            '{{client_name}}' => $proposal->client ? ($proposal->client->first_name . ' ' . $proposal->client->last_name) : '',
            '{{client_first_name}}' => $proposal->client ? $proposal->client->first_name : '',
            '{{client_last_name}}' => $proposal->client ? $proposal->client->last_name : '',
            '{{client_email}}' => $proposal->client ? $proposal->client->email : '',
            '{{client_phone}}' => $proposal->client ? $proposal->client->phone : '',
            '{{client_address}}' => $proposal->client ? $proposal->client->address : '',
            '{{proposal_number}}' => $proposal->proposal_number,
            '{{proposal_date}}' => $proposal->proposal_date->format('d.m.Y'),
            '{{valid_until}}' => $proposal->valid_until->format('d.m.Y'),
            '{{proposal_title}}' => $proposal->title,
            '{{company_name}}' => $company->name ?? '',
            '{{company_email}}' => $company->email ?? '',
            '{{company_phone}}' => $company->phone ?? '',
            '{{company_address}}' => $company->address ?? '',
            '{{subtotal}}' => number_format($proposal->subtotal, 2),
            '{{tax_total}}' => number_format($proposal->tax_total, 2),
            '{{total}}' => number_format($proposal->total, 2),
            '{{currency_symbol}}' => $proposal->currency ? $proposal->currency->symbol : 'RON',
            '{{items_table}}' => $this->generateItemsTable($proposal),
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $content);
        
        return $content;
    }

    /**
     * Get grouped items for proposal
     */
    private function getGroupedItems(Proposal $proposal)
    {
        $groupedItems = [];
        foreach ($proposal->items as $item) {
            $category = $item->category ?: 'Fără categorie';
            $subcategory = $item->subcategory ?: 'General';
            
            if (!isset($groupedItems[$category])) {
                $groupedItems[$category] = [];
            }
            if (!isset($groupedItems[$category][$subcategory])) {
                $groupedItems[$category][$subcategory] = [];
            }
            
            $groupedItems[$category][$subcategory][] = $item;
        }
        return $groupedItems;
    }

    /**
     * Generate items table HTML
     */
    private function generateItemsTable(Proposal $proposal)
    {
        $items = $proposal->items()->orderBy('position')->get();
        
        if ($items->isEmpty()) {
            return '<p>Nu există items în ofertă.</p>';
        }

        $groupedItems = $this->getGroupedItems($proposal);

        $currencySymbol = $proposal->currency ? $proposal->currency->symbol : 'RON';
        
        $html = '<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse;">';
        $html .= '<thead><tr>';
        $html .= '<th>Descriere</th><th>Cantitate</th><th>Preț Unit.</th><th>Tax %</th><th style="text-align: right;">Total</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($groupedItems as $category => $subcategories) {
            // Calculate category total
            $categoryTotal = 0;
            foreach ($subcategories as $subcatItems) {
                foreach ($subcatItems as $item) {
                    $categoryTotal += $item->total;
                }
            }
            
            // Category header
            $html .= '<tr style="background-color: #f0f0f0;"><td colspan="4"><strong>' . htmlspecialchars($category) . '</strong></td>';
            $html .= '<td style="text-align: right;"><strong>' . number_format($categoryTotal, 2) . ' ' . $currencySymbol . '</strong></td></tr>';
            
            foreach ($subcategories as $subcategory => $subcatItems) {
                // Subcategory header (if not General)
                if ($subcategory !== 'General') {
                    $html .= '<tr style="background-color: #f8f8f8;"><td colspan="5"><em>' . htmlspecialchars($subcategory) . '</em></td></tr>';
                }
                
                // Items
                foreach ($subcatItems as $item) {
                    $html .= '<tr>';
                    $html .= '<td>' . htmlspecialchars($item->description) . '</td>';
                    $html .= '<td>' . number_format($item->quantity, 2) . '</td>';
                    $html .= '<td>' . number_format($item->unit_price, 2) . '</td>';
                    $html .= '<td>' . number_format($item->tax_rate, 2) . '%</td>';
                    $html .= '<td style="text-align: right;">' . number_format($item->total, 2) . '</td>';
                    $html .= '</tr>';
                }
            }
        }

        $html .= '</tbody>';
        $html .= '<tfoot>';
        $html .= '<tr><td colspan="4" style="text-align: right;"><strong>Subtotal:</strong></td><td style="text-align: right;">' . number_format($proposal->subtotal, 2) . ' ' . $currencySymbol . '</td></tr>';
        $html .= '<tr><td colspan="4" style="text-align: right;"><strong>Taxe:</strong></td><td style="text-align: right;">' . number_format($proposal->tax_total, 2) . ' ' . $currencySymbol . '</td></tr>';
        $html .= '<tr style="background-color: #e0e0e0;"><td colspan="4" style="text-align: right;"><strong>TOTAL:</strong></td><td style="text-align: right;"><strong>' . number_format($proposal->total, 2) . ' ' . $currencySymbol . '</strong></td></tr>';
        $html .= '</tfoot></table>';

        return $html;
    }
}

