<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ProfileComponent extends Component
{
    use WithFileUploads;

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
    public $photoPreview = null;

    protected $rules = [
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'nickname' => 'nullable|string|max:255',
        'position' => 'nullable|string|max:255',
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'phone' => 'nullable|string|max:50',
        'password' => 'nullable|string|min:8|confirmed',
        'profile_photo' => 'nullable|image|max:2048',
        'email_signature' => 'nullable|string',
    ];

    public function mount()
    {
        $user = Auth::user();
        $this->first_name = $user->first_name ?? '';
        $this->last_name = $user->last_name ?? '';
        $this->nickname = $user->nickname ?? '';
        $this->position = $user->position ?? '';
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->email_signature = $user->email_signature ?? '';
        $this->photoPreview = $user->profile_photo ? asset('storage/' . $user->profile_photo) : null;
    }

    public function updated($propertyName)
    {
        if ($propertyName === 'profile_photo') {
            $this->photoPreview = $this->profile_photo->temporaryUrl();
        } elseif ($propertyName === 'email') {
            $this->validateOnly($propertyName, ['email' => 'required|email|unique:users,email,' . Auth::id()]);
        } else {
            $this->validateOnly($propertyName);
        }
    }

    public function save()
    {
        $rules = $this->rules;
        $rules['email'] = 'required|email|unique:users,email,' . Auth::id();
        $rules['password'] = 'nullable|string|min:8|confirmed';

        $this->validate($rules);

        $user = Auth::user();
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
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $photoPath = $this->profile_photo->store('profiles', 'public');
            $data['profile_photo'] = $photoPath;
        }

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        $user->update($data);
        session()->flash('message', 'Profil actualizat cu succes!');
    }

    public function render()
    {
        return view('livewire.admin.profile-component')->layout('layouts.app');
    }
}

