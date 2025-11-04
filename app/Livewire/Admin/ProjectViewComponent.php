<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Project;
use App\Models\User;
use App\Models\Admin\Client;
use Illuminate\Support\Facades\Auth;

class ProjectViewComponent extends Component
{
    public $projectId;
    public $project;
    public $activeTab = 'details';
    
    // Member management
    public $selectedUserId = '';
    public $showAddMemberModal = false;

    public function mount($id)
    {
        $user = Auth::user();
        if (!$user->hasRole('admin') && !$user->hasRole('manager')) {
            abort(403, 'Nu ai permisiunea de a accesa această pagină.');
        }

        $this->projectId = $id;
        $this->loadProject();
    }

    public function loadProject()
    {
        $this->project = Project::with(['client', 'currency', 'members', 'creator', 'updater', 'boards.columns.tasks'])->findOrFail($this->projectId);
        
        // Ensure creator is a member if not already
        if ($this->project->created_by && !$this->project->members->contains($this->project->created_by)) {
            $this->project->members()->attach($this->project->created_by);
            $this->project->refresh();
        }
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function addMember()
    {
        $this->validate([
            'selectedUserId' => 'required|exists:users,id',
        ]);

        if (!$this->project->members->contains($this->selectedUserId)) {
            $this->project->members()->attach($this->selectedUserId);
            session()->flash('message', 'Membru adăugat cu succes!');
        } else {
            session()->flash('error', 'Membrul este deja în proiect!');
        }

        $this->selectedUserId = '';
        $this->showAddMemberModal = false;
        $this->loadProject();
    }

    public function removeMember($userId)
    {
        $this->project->members()->detach($userId);
        session()->flash('message', 'Membru șters cu succes!');
        $this->loadProject();
    }

    public function render()
    {
        $this->loadProject();
        
        $users = User::orderBy('name')->get();
        $boards = $this->project->boards()->with(['columns.tasks' => function($query) {
            $query->orderBy('position');
        }, 'columns.tasks.assignedUser', 'columns.tasks.labels'])->get();
        
        // Calculate metrics (placeholder - to be implemented later)
        $invoicedAmount = 0;
        $costs = 0;
        $totalHours = 0;
        $totalHourlyCost = 0;
        $balanceAmount = 0;

        // Get currency symbol
        $currencySymbol = $this->project->currency ? $this->project->currency->symbol : 'RON';

        return view('livewire.admin.project-view-component', [
            'project' => $this->project,
            'users' => $users,
            'boards' => $boards,
            'invoicedAmount' => $invoicedAmount,
            'costs' => $costs,
            'totalHours' => $totalHours,
            'totalHourlyCost' => $totalHourlyCost,
            'balanceAmount' => $balanceAmount,
            'currencySymbol' => $currencySymbol,
        ])->layout('layouts.app');
    }
}

