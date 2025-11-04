<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Project;
use App\Models\Admin\Client;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class ProjectsComponent extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $clientFilter = '';
    public $projectId = null;
    public $projectName = '';
    public $clientId = '';
    public $clientPortalAccess = false;
    public $status = 'not_started';
    public $billingType = 'hourly_rate';
    public $currencyId = '';
    public $fixedRate = 0;
    public $hourlyRate = 0;
    public $startDate = '';
    public $endDate = '';
    public $descriptionHtml = '';
    public $showModal = false;
    public $showClientModal = false;

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $user = Auth::user();
        if (!$user->hasRole('admin') && !$user->hasRole('manager')) {
            abort(403, 'Nu ai permisiunea de a accesa această pagină.');
        }
    }

    public function openModal($id = null)
    {
        if ($id) {
            $project = Project::findOrFail($id);
            $this->projectId = $id;
            $this->projectName = $project->name;
            $this->clientId = $project->client_id ?? '';
            $this->clientPortalAccess = $project->client_portal_access;
            $this->status = $project->status;
            $this->billingType = $project->billing_type;
            $this->currencyId = $project->currency_id ?? '';
            $this->fixedRate = $project->fixed_rate ?? 0;
            $this->hourlyRate = $project->hourly_rate ?? 0;
            $this->startDate = $project->start_date ? $project->start_date->format('d.m.Y') : '';
            $this->endDate = $project->end_date ? $project->end_date->format('d.m.Y') : '';
            $this->descriptionHtml = $project->description_html ?? '';
        } else {
            $this->resetForm();
        }
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->projectId = null;
        $this->projectName = '';
        $this->clientId = '';
        $this->clientPortalAccess = false;
        $this->status = 'not_started';
        $this->billingType = 'hourly_rate';
        $this->currencyId = '';
        $this->fixedRate = 0;
        $this->hourlyRate = 0;
        $this->startDate = '';
        $this->endDate = '';
        $this->descriptionHtml = '';
        $this->resetValidation();
    }

    public function saveProject()
    {
        $rules = [
            'projectName' => 'required|string|max:255',
            'status' => 'required|in:not_started,on_hold,in_progress,completed,cancelled',
            'billingType' => 'required|in:fixed_rate,hourly_rate',
            'currencyId' => 'required|exists:currencies,id',
        ];

        if ($this->billingType === 'fixed_rate') {
            $rules['fixedRate'] = 'required|numeric|min:0';
        } else {
            $rules['hourlyRate'] = 'required|numeric|min:0';
        }

        $this->validate($rules);

        // Parse dates from d.m.Y format
        $startDate = $this->startDate ? \Carbon\Carbon::createFromFormat('d.m.Y', $this->startDate) : null;
        $endDate = $this->endDate ? \Carbon\Carbon::createFromFormat('d.m.Y', $this->endDate) : null;

        $data = [
            'name' => $this->projectName,
            'client_id' => $this->clientId ?: null,
            'client_portal_access' => $this->clientPortalAccess,
            'status' => $this->status,
            'billing_type' => $this->billingType,
            'currency_id' => $this->currencyId,
            'fixed_rate' => $this->billingType === 'fixed_rate' ? $this->fixedRate : 0,
            'hourly_rate' => $this->billingType === 'hourly_rate' ? $this->hourlyRate : 0,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'description_html' => $this->descriptionHtml,
        ];

        if ($this->projectId) {
            $project = Project::findOrFail($this->projectId);
            $data['updated_by'] = Auth::id();
            $project->update($data);
            session()->flash('message', 'Proiect actualizat cu succes!');
        } else {
            $data['created_by'] = Auth::id();
            $project = Project::create($data);
            
            // Add creator as project member by default
            $project->members()->attach(Auth::id());
            
            session()->flash('message', 'Proiect creat cu succes!');
        }

        $this->closeModal();
    }

    public function deleteProject($id)
    {
        Project::findOrFail($id)->delete();
        session()->flash('message', 'Proiect șters cu succes!');
    }

    public $newClientFirstName = '';
    public $newClientLastName = '';
    public $newClientEmail = '';
    public $newClientPhone = '';

    public function openClientModal()
    {
        $this->showClientModal = true;
        $this->newClientFirstName = '';
        $this->newClientLastName = '';
        $this->newClientEmail = '';
        $this->newClientPhone = '';
    }

    public function closeClientModal()
    {
        $this->showClientModal = false;
        $this->resetValidation();
    }

    public function saveNewClient()
    {
        $this->validate([
            'newClientFirstName' => 'required|string|max:255',
            'newClientLastName' => 'required|string|max:255',
            'newClientEmail' => 'nullable|email|unique:clients,email',
            'newClientPhone' => 'nullable|string|max:50',
        ]);

        $client = Client::create([
            'first_name' => $this->newClientFirstName,
            'last_name' => $this->newClientLastName,
            'email' => $this->newClientEmail,
            'phone' => $this->newClientPhone,
            'type' => 'customer',
        ]);

        $this->clientId = $client->id;
        $this->closeClientModal();
        session()->flash('message', 'Client adăugat cu succes și selectat!');
    }

    public function render()
    {
        $query = Project::with(['client', 'currency']);

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->clientFilter) {
            $query->where('client_id', $this->clientFilter);
        }

        $projects = $query->orderBy('created_at', 'desc')->paginate(10);
        $clients = Client::orderBy('first_name')->orderBy('last_name')->get();
        $currencies = \App\Models\Currency::orderBy('code')->get();

        return view('livewire.admin.projects-component', [
            'projects' => $projects,
            'clients' => $clients,
            'currencies' => $currencies,
        ])->layout('layouts.app');
    }
}

