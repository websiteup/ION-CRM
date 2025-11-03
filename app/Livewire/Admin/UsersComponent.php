<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\Role;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UsersComponent extends Component
{
    use WithPagination, WithFileUploads;

    public function mount()
    {
        // Verificăm dacă utilizatorul este administrator
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            abort(403, 'Nu ai permisiunea de a accesa această pagină.');
        }
    }

    public $first_name = '';
    public $last_name = '';
    public $nickname = '';
    public $position = '';
    public $name = '';
    public $email = '';
    public $phone = '';
    public $password = '';
    public $password_confirmation = '';
    public $profile_photo;
    public $email_signature = '';
    public $selectedRoles = [];
    public $userId = null;
    public $showModal = false;
    public $search = '';
    public $photoPreview = null;

    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'nickname' => 'nullable|string|max:255',
        'position' => 'nullable|string|max:255',
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'phone' => 'nullable|string|max:50',
        'password' => 'required|string|min:8|confirmed',
        'profile_photo' => 'nullable|image|max:2048',
        'email_signature' => 'nullable|string',
        'selectedRoles' => 'nullable|array',
    ];

    public function updated($propertyName)
    {
        if ($propertyName === 'profile_photo') {
            $this->photoPreview = $this->profile_photo->temporaryUrl();
        } elseif ($propertyName === 'email' && $this->userId) {
            $this->validateOnly($propertyName, ['email' => 'required|email|unique:users,email,' . $this->userId]);
        } elseif ($propertyName === 'password' && $this->userId) {
            // Password este opțional la editare
        } else {
            $this->validateOnly($propertyName);
        }
    }

    public function openModal($id = null)
    {
        if ($id) {
            $user = User::with('roles')->findOrFail($id);
            $this->userId = $id;
            $this->first_name = $user->first_name ?? '';
            $this->last_name = $user->last_name ?? '';
            $this->nickname = $user->nickname ?? '';
            $this->position = $user->position ?? '';
            $this->name = $user->name;
            $this->email = $user->email;
            $this->phone = $user->phone ?? '';
            $this->email_signature = $user->email_signature ?? '';
            $this->selectedRoles = $user->roles->pluck('id')->toArray();
            $this->photoPreview = $user->profile_photo ? asset('storage/' . $user->profile_photo) : null;
            $this->password = '';
            $this->password_confirmation = '';
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
        $this->userId = null;
        $this->first_name = '';
        $this->last_name = '';
        $this->nickname = '';
        $this->position = '';
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->profile_photo = null;
        $this->photoPreview = null;
        $this->email_signature = '';
        $this->selectedRoles = [];
        $this->resetValidation();
    }

    public function save()
    {
        $rules = $this->rules;
        
        if ($this->userId) {
            // La editare, email-ul poate rămâne același
            $rules['email'] = 'required|email|unique:users,email,' . $this->userId;
            // Parola este opțională la editare
            $rules['password'] = 'nullable|string|min:8|confirmed';
        } else {
            // La creare, parola este obligatorie
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $this->validate($rules);

        $data = [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'nickname' => $this->nickname,
            'position' => $this->position,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'email_signature' => $this->email_signature,
        ];

        if ($this->profile_photo) {
            $photoPath = $this->profile_photo->store('profiles', 'public');
            $data['profile_photo'] = $photoPath;
        }

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->userId) {
            $user = User::findOrFail($this->userId);
            if ($this->profile_photo && $user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $user->update($data);
            $user->roles()->sync($this->selectedRoles);
            session()->flash('message', 'Utilizator actualizat cu succes!');
        } else {
            $user = User::create($data);
            $user->roles()->sync($this->selectedRoles);
            session()->flash('message', 'Utilizator adăugat cu succes!');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $user = User::with('roles')->findOrFail($id);
        
        // Verificăm dacă utilizatorul este administrator
        if ($user->hasRole('admin')) {
            // Verificăm dacă este singurul administrator
            $adminRole = Role::where('slug', 'admin')->first();
            $adminCount = $adminRole ? $adminRole->users()->count() : 0;
            
            if ($adminCount <= 1) {
                session()->flash('error', 'Nu poți șterge singurul administrator din sistem!');
                return;
            }
            
            // Verificăm dacă utilizatorul încearcă să-și șteargă propriul profil
            if ($user->id === Auth::id()) {
                session()->flash('error', 'Nu poți șterge propriul profil dacă ești singurul administrator!');
                return;
            }
        }
        
        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }
        $user->delete();
        session()->flash('message', 'Utilizator șters cu succes!');
    }

    public function render()
    {
        $query = User::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%')
                  ->orWhere('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('nickname', 'like', '%' . $this->search . '%');
            });
        }

        $users = $query->with('roles')->orderBy('created_at', 'desc')->paginate(10);
        $roles = Role::orderBy('name')->get();

        return view('livewire.admin.users-component', [
            'users' => $users,
            'roles' => $roles,
        ])->layout('layouts.app');
    }
}

