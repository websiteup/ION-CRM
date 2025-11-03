<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Admin\Client;
use Livewire\WithPagination;

class ClientsComponent extends Component
{
    use WithPagination;

    public $first_name = '';
    public $last_name = '';
    public $email = '';
    public $phone = '';
    public $country = '';
    public $address = '';
    public $type = 'lead';
    public $clientId = null;
    public $showModal = false;
    public $search = '';

    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'nullable|email|unique:clients,email',
        'phone' => 'nullable|string|max:50',
        'country' => 'nullable|string|max:100',
        'address' => 'nullable|string',
        'type' => 'required|in:lead,customer',
    ];

    public function updated($propertyName)
    {
        if ($propertyName === 'email') {
            $this->validateOnly($propertyName, ['email' => 'nullable|email|unique:clients,email,' . $this->clientId]);
        } else {
            $this->validateOnly($propertyName);
        }
    }

    public function openModal($id = null)
    {
        if ($id) {
            $client = Client::findOrFail($id);
            $this->clientId = $id;
            $this->first_name = $client->first_name;
            $this->last_name = $client->last_name;
            $this->email = $client->email;
            $this->phone = $client->phone;
            $this->country = $client->country;
            $this->address = $client->address;
            $this->type = $client->type;
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
        $this->clientId = null;
        $this->first_name = '';
        $this->last_name = '';
        $this->email = '';
        $this->phone = '';
        $this->country = '';
        $this->address = '';
        $this->type = 'lead';
        $this->resetValidation();
    }

    public function save()
    {
        $rules = $this->rules;
        $rules['email'] = 'nullable|email|unique:clients,email,' . $this->clientId;
        $this->validate($rules);

        if ($this->clientId) {
            $client = Client::findOrFail($this->clientId);
            $client->update([
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'phone' => $this->phone,
                'country' => $this->country,
                'address' => $this->address,
                'type' => $this->type,
            ]);
            session()->flash('message', 'Client actualizat cu succes!');
        } else {
            Client::create([
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'phone' => $this->phone,
                'country' => $this->country,
                'address' => $this->address,
                'type' => $this->type,
            ]);
            session()->flash('message', 'Client adăugat cu succes!');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        Client::findOrFail($id)->delete();
        session()->flash('message', 'Client șters cu succes!');
    }

    public function render()
    {
        $query = Client::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%');
            });
        }

        $clients = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.admin.clients-component', [
            'clients' => $clients
        ]);
    }
}
