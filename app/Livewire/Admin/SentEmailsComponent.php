<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\SentEmail;
use Livewire\WithPagination;

class SentEmailsComponent extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $toFilter = '';
    public $emailModalId = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingToFilter()
    {
        $this->resetPage();
    }

    public function showEmail($id)
    {
        $this->emailModalId = $id;
    }

    public function render()
    {
        $query = SentEmail::with('user')->orderBy('sent_at', 'desc');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('subject', 'like', '%' . $this->search . '%')
                  ->orWhere('to', 'like', '%' . $this->search . '%')
                  ->orWhere('from', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->toFilter) {
            $query->where('to', 'like', '%' . $this->toFilter . '%');
        }

        $emails = $query->paginate(20);

        return view('livewire.admin.sent-emails-component', [
            'emails' => $emails,
        ])->layout('layouts.app');
    }
}

