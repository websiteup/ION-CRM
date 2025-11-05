<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use Illuminate\Http\Request;

class ProposalController extends Controller
{
    /**
     * Generate PDF view for proposal
     */
    public function pdf($id, Request $request)
    {
        // Check if user is admin
        if (!auth()->check() || !auth()->user()->hasRole('admin')) {
            abort(403, 'Nu ai permisiunea de a accesa această pagină.');
        }

        $proposal = Proposal::with(['client', 'template', 'currency', 'items' => function($query) {
            $query->orderBy('position');
        }])->findOrFail($id);

        // Group items by category
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

        $company = \App\Models\Company::getCompany();

        // Process template content if exists
        $content = '';
        if ($proposal->template) {
            $content = $this->replaceShortcodes($proposal->template->content_html, $proposal, $company);
        }

        $preview = $request->has('preview');

        return view('proposals.pdf', [
            'proposal' => $proposal,
            'company' => $company,
            'groupedItems' => $groupedItems,
            'content' => $content,
            'preview' => $preview,
        ]);
    }

    /**
     * Replace shortcodes in template content
     */
    private function replaceShortcodes($content, Proposal $proposal, $company)
    {
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
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $content);
        
        // Replace items_table with actual table
        if (strpos($content, '{{items_table}}') !== false) {
            $itemsTable = view('proposals.items-table', [
                'proposal' => $proposal,
                'groupedItems' => $this->getGroupedItems($proposal),
            ])->render();
            $content = str_replace('{{items_table}}', $itemsTable, $content);
        }
        
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
}

