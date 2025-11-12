<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Proposal;
use App\Models\ProposalItem;
use App\Models\ProposalTemplate;
use App\Models\ProposalHistory;
use App\Models\Admin\Client;
use App\Models\Admin\Service;
use App\Models\Currency;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\ProposalEmail;

class ProposalViewComponent extends Component
{
    public $proposalId;
    public $proposal;
    public $isCreating = false;

    // Form fields
    public $title = '';
    public $client_id = '';
    public $template_id = '';
    public $proposal_number = '';
    public $proposal_date = '';
    public $valid_until = '';
    public $status = 'draft';
    public $tags = '';
    public $notes = '';
    public $currency_id = '';

    // Items management
    public $items = [];
    public $showItemModal = false;
    public $editingItemIndex = null;
    public $itemServiceId = '';
    public $itemCategory = '';
    public $itemSubcategory = '';
    public $itemDescription = '';
    public $itemQuantity = 1;
    public $itemUnitPrice = 0;
    public $itemTaxRate = 0;

    // Computed totals
    public $subtotal = 0;
    public $taxTotal = 0;
    public $total = 0;

    protected $rules = [
        'title' => 'required|string|max:255',
        'client_id' => 'required|exists:clients,id',
        'template_id' => 'nullable|exists:proposal_templates,id',
        'proposal_date' => 'required|date',
        'valid_until' => 'required|date|after:proposal_date',
        'status' => 'required|in:draft,sent,accepted,rejected,expired',
        'currency_id' => 'nullable|exists:currencies,id',
    ];

    public function mount($id = null)
    {
        if ($id) {
            $this->proposalId = $id;
            $this->loadProposal();
        } else {
            $this->isCreating = true;
            $this->proposal_date = date('Y-m-d');
            $this->valid_until = date('Y-m-d', strtotime('+30 days'));
            $defaultCurrency = Currency::getDefault();
            if ($defaultCurrency) {
                $this->currency_id = $defaultCurrency->id;
            }
        }
    }

    public function loadProposal()
    {
        $this->proposal = Proposal::with(['client', 'template', 'currency', 'items.service', 'creator', 'updater', 'history.user'])->findOrFail($this->proposalId);
        
        // Auto-expire if needed
        if ($this->proposal->isExpired()) {
            $this->proposal->update(['status' => 'expired']);
            $this->addHistoryEvent($this->proposal->id, 'expired', 'Ofertă expirată', 'Oferta a expirat automat', null, null);
            $this->proposal->refresh();
        }
        
        $this->title = $this->proposal->title;
        $this->client_id = $this->proposal->client_id;
        $this->template_id = $this->proposal->template_id;
        $this->proposal_number = $this->proposal->proposal_number;
        $this->proposal_date = $this->proposal->proposal_date->format('Y-m-d');
        $this->valid_until = $this->proposal->valid_until->format('Y-m-d');
        $this->status = $this->proposal->status;
        $this->tags = $this->proposal->tags;
        $this->notes = $this->proposal->notes;
        $this->currency_id = $this->proposal->currency_id;

        // Load items
        $this->items = [];
        foreach ($this->proposal->items as $item) {
            $this->items[] = [
                'id' => $item->id,
                'service_id' => $item->service_id,
                'category' => $item->category,
                'subcategory' => $item->subcategory,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'tax_rate' => $item->tax_rate,
                'tax_amount' => $item->tax_amount,
                'total' => $item->total,
                'position' => $item->position,
            ];
        }

        $this->calculateTotals();
    }

    public function updated($propertyName)
    {
        if (str_starts_with($propertyName, 'item')) {
            // Item fields changed, recalculate totals
            $this->calculateTotals();
        } elseif ($propertyName === 'valid_until' || $propertyName === 'proposal_date') {
            // Validate date relationship
            if ($this->proposal_date && $this->valid_until) {
                if ($this->valid_until <= $this->proposal_date) {
                    $this->addError('valid_until', 'Data expirării trebuie să fie după data ofertei.');
                }
            }
        } else {
            $this->validateOnly($propertyName);
        }
    }

    public function openItemModal($index = null)
    {
        if ($index !== null) {
            $this->editingItemIndex = $index;
            $item = $this->items[$index];
            $this->itemServiceId = $item['service_id'] ?? '';
            $this->itemCategory = $item['category'] ?? '';
            $this->itemSubcategory = $item['subcategory'] ?? '';
            $this->itemDescription = $item['description'] ?? '';
            $this->itemQuantity = $item['quantity'] ?? 1;
            $this->itemUnitPrice = $item['unit_price'] ?? 0;
            $this->itemTaxRate = $item['tax_rate'] ?? 0;
        } else {
            $this->resetItemForm();
        }
        $this->showItemModal = true;
    }

    public function closeItemModal()
    {
        $this->showItemModal = false;
        $this->resetItemForm();
    }

    public function resetItemForm()
    {
        $this->editingItemIndex = null;
        $this->itemServiceId = '';
        $this->itemCategory = '';
        $this->itemSubcategory = '';
        $this->itemDescription = '';
        $this->itemQuantity = 1;
        $this->itemUnitPrice = 0;
        $this->itemTaxRate = 0;
    }

    public function updatedItemServiceId()
    {
        if ($this->itemServiceId) {
            $service = Service::findOrFail($this->itemServiceId);
            $this->itemDescription = $service->name . ($service->description ? ' - ' . $service->description : '');
            $this->itemUnitPrice = $service->unit_price;
            $this->itemTaxRate = $service->tax ?? 0;
        }
    }

    public function saveItem()
    {
        $this->validate([
            'itemDescription' => 'required|string',
            'itemQuantity' => 'required|numeric|min:0.01',
            'itemUnitPrice' => 'required|numeric|min:0',
            'itemTaxRate' => 'nullable|numeric|min:0|max:100',
        ]);

        $quantity = floatval($this->itemQuantity);
        $unitPrice = floatval($this->itemUnitPrice);
        $taxRate = floatval($this->itemTaxRate ?? 0);
        
        $subtotalItem = $quantity * $unitPrice;
        $taxAmount = $subtotalItem * ($taxRate / 100);
        $totalItem = $subtotalItem + $taxAmount;

        $itemData = [
            'service_id' => $this->itemServiceId ?: null,
            'category' => $this->itemCategory,
            'subcategory' => $this->itemSubcategory,
            'description' => $this->itemDescription,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total' => $totalItem,
            'position' => count($this->items),
        ];

        if ($this->editingItemIndex !== null) {
            $itemData['id'] = $this->items[$this->editingItemIndex]['id'] ?? null;
            $this->items[$this->editingItemIndex] = $itemData;
        } else {
            $this->items[] = $itemData;
        }

        $this->calculateTotals();
        $this->closeItemModal();
    }

    public function deleteItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items); // Reindex array
        $this->calculateTotals();
    }

    public function moveItemUp($index)
    {
        if ($index > 0) {
            $temp = $this->items[$index];
            $this->items[$index] = $this->items[$index - 1];
            $this->items[$index - 1] = $temp;
        }
    }

    public function moveItemDown($index)
    {
        if ($index < count($this->items) - 1) {
            $temp = $this->items[$index];
            $this->items[$index] = $this->items[$index + 1];
            $this->items[$index + 1] = $temp;
        }
    }

    public function calculateTotals()
    {
        $this->subtotal = 0;
        $this->taxTotal = 0;
        $this->total = 0;

        foreach ($this->items as $item) {
            $subtotalItem = floatval($item['quantity']) * floatval($item['unit_price']);
            $taxAmount = $subtotalItem * (floatval($item['tax_rate'] ?? 0) / 100);
            $totalItem = $subtotalItem + $taxAmount;

            $this->subtotal += $subtotalItem;
            $this->taxTotal += $taxAmount;
            $this->total += $totalItem;
        }

        // Round to 2 decimals
        $this->subtotal = round($this->subtotal, 2);
        $this->taxTotal = round($this->taxTotal, 2);
        $this->total = round($this->total, 2);
    }

    public function save()
    {
        $this->validate($this->rules);

        if (count($this->items) === 0) {
            notify()->error('Trebuie să adaugi cel puțin un item în ofertă!');
            return;
        }

        $data = [
            'title' => $this->title,
            'client_id' => $this->client_id,
            'template_id' => $this->template_id,
            'proposal_date' => $this->proposal_date,
            'valid_until' => $this->valid_until,
            'status' => $this->status,
            'tags' => $this->tags,
            'notes' => $this->notes,
            'currency_id' => $this->currency_id,
            'subtotal' => $this->subtotal,
            'tax_total' => $this->taxTotal,
            'total' => $this->total,
            'updated_by' => Auth::id(),
        ];

        if ($this->isCreating) {
            $data['proposal_number'] = Proposal::generateProposalNumber();
            $data['created_by'] = Auth::id();
            $proposal = Proposal::create($data);
            $this->proposalId = $proposal->id;
            $this->isCreating = false;
        } else {
            $this->proposal->update($data);
        }

        // Save items
        $proposal = Proposal::findOrFail($this->proposalId);
        
        // Delete existing items
        $proposal->items()->delete();

        // Create new items
        foreach ($this->items as $index => $item) {
            ProposalItem::create([
                'proposal_id' => $proposal->id,
                'service_id' => $item['service_id'] ?? null,
                'category' => $item['category'] ?? null,
                'subcategory' => $item['subcategory'] ?? null,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'tax_rate' => $item['tax_rate'] ?? 0,
                'tax_amount' => $item['tax_amount'] ?? 0,
                'total' => $item['total'],
                'position' => $index,
            ]);
        }

        // Save history event
        if ($this->isCreating) {
            $this->addHistoryEvent($proposal->id, 'created', 'Ofertă creată', 'Oferta a fost creată', null, Auth::id());
        } else {
            $this->addHistoryEvent($proposal->id, 'updated', 'Ofertă actualizată', 'Oferta a fost modificată', null, Auth::id());
        }

        notify()->success('Ofertă salvată cu succes!');
        $this->loadProposal();
    }

    /**
     * Add history event for proposal
     */
    private function addHistoryEvent($proposalId, $eventType, $title, $description, $changes = null, $userId = null)
    {
        ProposalHistory::create([
            'proposal_id' => $proposalId,
            'event_type' => $eventType,
            'title' => $title,
            'description' => $description,
            'changes' => $changes,
            'user_id' => $userId ?? Auth::id(),
            'event_date' => now(),
        ]);
    }

    public function sendEmail()
    {
        if (!$this->proposalId) {
            notify()->error('Trebuie să salvezi oferta mai întâi!');
            return;
        }

        $proposal = Proposal::with(['client', 'template', 'currency', 'items'])->findOrFail($this->proposalId);
        
        if (!$proposal->client || !$proposal->client->email) {
            notify()->error('Clientul nu are email setat!');
            return;
        }

        try {
            // Actualizăm status-ul doar dacă este draft
            $isFirstSend = $proposal->status === 'draft';
            if ($isFirstSend) {
                $proposal->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
                $this->addHistoryEvent($proposal->id, 'sent', 'Ofertă trimisă', 'Oferta a fost trimisă pe email pentru prima dată', null, Auth::id());
            } else {
                // Pentru retrimitere, actualizăm doar data trimiterii
                $proposal->update([
                    'sent_at' => now(),
                ]);
                $this->addHistoryEvent($proposal->id, 'sent', 'Ofertă retrimisă', 'Oferta a fost retrimisă pe email', null, Auth::id());
            }

            // Send email
            $mailable = new ProposalEmail($proposal);
            Mail::to($proposal->client->email)->send($mailable);
            
            notify()->success('Email trimis cu succes!');
            $this->loadProposal();
        } catch (\Exception $e) {
            notify()->error('Eroare la trimiterea email-ului: ' . $e->getMessage());
        }
    }

    public function acceptProposal()
    {
        if (!$this->proposalId) {
            notify()->error('Proposal nu a fost găsit!');
            return;
        }

        $proposal = Proposal::findOrFail($this->proposalId);
        
        if (!$proposal->canBeAccepted()) {
            notify()->error('Oferta nu poate fi acceptată! Status actual: ' . $proposal->status);
            return;
        }

        $proposal->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        $this->addHistoryEvent($proposal->id, 'accepted', 'Ofertă acceptată', 'Oferta a fost acceptată', null, Auth::id());

        notify()->success('Ofertă acceptată cu succes!');
        $this->loadProposal();
    }

    public function rejectProposal()
    {
        if (!$this->proposalId) {
            notify()->error('Proposal nu a fost găsit!');
            return;
        }

        $proposal = Proposal::findOrFail($this->proposalId);
        
        if (!$proposal->canBeRejected()) {
            notify()->error('Oferta nu poate fi respinsă! Status actual: ' . $proposal->status);
            return;
        }

        $proposal->update([
            'status' => 'rejected',
            'rejected_at' => now(),
        ]);

        $this->addHistoryEvent($proposal->id, 'rejected', 'Ofertă respinsă', 'Oferta a fost respinsă', null, Auth::id());

        notify()->success('Ofertă respinsă!');
        $this->loadProposal();
    }

    public function duplicateProposal()
    {
        if (!$this->proposalId) {
            notify()->error('Proposal nu a fost găsit!');
            return;
        }

        $original = Proposal::with('items')->findOrFail($this->proposalId);
        
        $newProposal = $original->replicate();
        $newProposal->proposal_number = Proposal::generateProposalNumber();
        $newProposal->status = 'draft';
        $newProposal->sent_at = null;
        $newProposal->accepted_at = null;
        $newProposal->rejected_at = null;
        $newProposal->created_by = Auth::id();
        $newProposal->updated_by = Auth::id();
        $newProposal->save();

        // Duplicate items
        foreach ($original->items as $item) {
            $newItem = $item->replicate();
            $newItem->proposal_id = $newProposal->id;
            $newItem->save();
        }

        // Add history events for both proposals
        $this->addHistoryEvent($original->id, 'duplicated', 'Ofertă duplicată', 'Oferta a fost duplicată', ['duplicated_to' => $newProposal->proposal_number], Auth::id());
        $this->addHistoryEvent($newProposal->id, 'created', 'Ofertă creată', 'Oferta a fost creată prin duplicare', ['duplicated_from' => $original->proposal_number], Auth::id());

        notify()->success('Ofertă duplicată cu succes!');
        return redirect()->route('admin.proposals.view', $newProposal->id);
    }

    public function render()
    {
        $clients = Client::orderBy('first_name')->get();
        $templates = ProposalTemplate::orderBy('is_default', 'desc')->orderBy('name')->get();
        $currencies = Currency::orderBy('is_default', 'desc')->orderBy('name')->get();
        $services = Service::orderBy('name')->get();

        // Group items by category for display
        $groupedItems = [];
        foreach ($this->items as $index => $item) {
            $category = $item['category'] ?: 'Fără categorie';
            $subcategory = $item['subcategory'] ?: 'General';
            
            if (!isset($groupedItems[$category])) {
                $groupedItems[$category] = [];
            }
            if (!isset($groupedItems[$category][$subcategory])) {
                $groupedItems[$category][$subcategory] = [];
            }
            
            // Add original index for reference
            $item['_index'] = $index;
            $groupedItems[$category][$subcategory][] = $item;
        }

        // Load proposal with relationships for history
        $proposalForHistory = null;
        if ($this->proposalId && !$this->isCreating) {
            $proposalForHistory = Proposal::with(['creator', 'updater', 'history.user'])->find($this->proposalId);
        }

        return view('livewire.admin.proposal-view-component', [
            'clients' => $clients,
            'templates' => $templates,
            'currencies' => $currencies,
            'services' => $services,
            'groupedItems' => $groupedItems,
            'proposal' => $proposalForHistory ?? $this->proposal,
        ])->layout('layouts.app');
    }
}

