<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Proposal;
use App\Models\ProposalHistory;
use App\Models\Admin\Client;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class ProposalsComponent extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $clientFilter = '';

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingClientFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        // Auto-expire proposals that are past their valid_until date
        $expiredProposals = Proposal::expired()->get();
        foreach ($expiredProposals as $proposal) {
            $proposal->update(['status' => 'expired']);
            ProposalHistory::create([
                'proposal_id' => $proposal->id,
                'event_type' => 'expired',
                'title' => 'Ofertă expirată',
                'description' => 'Oferta a expirat automat',
                'changes' => null,
                'user_id' => null, // System action
                'event_date' => now(),
            ]);
        }

        $query = Proposal::with(['client', 'currency', 'creator']);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('proposal_number', 'like', '%' . $this->search . '%')
                  ->orWhere('tags', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->clientFilter) {
            $query->where('client_id', $this->clientFilter);
        }

        $proposals = $query->orderBy('created_at', 'desc')->paginate(15);
        $clients = Client::orderBy('first_name')->get();

        $statusCounts = [
            'all' => Proposal::count(),
            'draft' => Proposal::where('status', 'draft')->count(),
            'sent' => Proposal::where('status', 'sent')->count(),
            'accepted' => Proposal::where('status', 'accepted')->count(),
            'rejected' => Proposal::where('status', 'rejected')->count(),
            'expired' => Proposal::where('status', 'expired')->count(),
        ];

        return view('livewire.admin.proposals-component', [
            'proposals' => $proposals,
            'clients' => $clients,
            'statusCounts' => $statusCounts,
        ]);
    }
}

