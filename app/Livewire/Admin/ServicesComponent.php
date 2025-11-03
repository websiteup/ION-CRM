<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Admin\Service;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ServicesComponent extends Component
{
    use WithPagination, WithFileUploads;

    public $name = '';
    public $description = '';
    public $unit_price = '';
    public $tax = 0;
    public $unit_type = 'unit';
    public $photo;
    public $serviceId = null;
    public $showModal = false;
    public $search = '';
    public $photoPreview = null;

    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'unit_price' => 'required|numeric|min:0',
        'tax' => 'nullable|numeric|min:0|max:100',
        'unit_type' => 'required|string|max:50',
        'photo' => 'nullable|image|max:2048',
    ];

    public function updated($propertyName)
    {
        if ($propertyName === 'photo') {
            $this->photoPreview = $this->photo->temporaryUrl();
        } else {
            $this->validateOnly($propertyName);
        }
    }

    public function openModal($id = null)
    {
        if ($id) {
            $service = Service::findOrFail($id);
            $this->serviceId = $id;
            $this->name = $service->name;
            $this->description = $service->description;
            $this->unit_price = $service->unit_price;
            $this->tax = $service->tax;
            $this->unit_type = $service->unit_type;
            $this->photoPreview = $service->photo ? asset('storage/' . $service->photo) : null;
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
        $this->serviceId = null;
        $this->name = '';
        $this->description = '';
        $this->unit_price = '';
        $this->tax = 0;
        $this->unit_type = 'unit';
        $this->photo = null;
        $this->photoPreview = null;
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'unit_price' => $this->unit_price,
            'tax' => $this->tax ?? 0,
            'unit_type' => $this->unit_type,
        ];

        if ($this->photo) {
            $photoPath = $this->photo->store('services', 'public');
            $data['photo'] = $photoPath;
        }

        if ($this->serviceId) {
            $service = Service::findOrFail($this->serviceId);
            if ($this->photo && $service->photo) {
                Storage::disk('public')->delete($service->photo);
            }
            $data['updated_by'] = Auth::id();
            $service->update($data);
            session()->flash('message', 'Serviciu actualizat cu succes!');
        } else {
            $data['created_by'] = Auth::id();
            Service::create($data);
            session()->flash('message', 'Serviciu adăugat cu succes!');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $service = Service::findOrFail($id);
        if ($service->photo) {
            Storage::disk('public')->delete($service->photo);
        }
        $service->delete();
        session()->flash('message', 'Serviciu șters cu succes!');
    }

    public function render()
    {
        $query = Service::query();

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
        }

        $services = $query->with(['creator', 'updater'])->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.admin.services-component', [
            'services' => $services
        ]);
    }
}
