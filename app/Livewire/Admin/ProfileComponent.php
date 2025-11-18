<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\Setting;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

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
    
    // Telegram & Notification Settings
    public $telegram_chat_id = '';
    public $notification_email_enabled = true;
    public $notification_telegram_enabled = false;
    public $notification_task_created = true;
    public $notification_task_assigned = true;
    public $notification_task_updated = true;
    public $notification_task_deadline = true;
    
    // Dark Mode
    public $dark_mode = false;

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
        'telegram_chat_id' => 'nullable|string|max:255',
        'notification_email_enabled' => 'boolean',
        'notification_telegram_enabled' => 'boolean',
        'notification_task_created' => 'boolean',
        'notification_task_assigned' => 'boolean',
        'notification_task_updated' => 'boolean',
        'notification_task_deadline' => 'boolean',
        'dark_mode' => 'boolean',
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
        
        // Load notification preferences
        $this->telegram_chat_id = $user->telegram_chat_id ?? '';
        $this->notification_email_enabled = $user->notification_email_enabled ?? true;
        $this->notification_telegram_enabled = $user->notification_telegram_enabled ?? false;
        $this->notification_task_created = $user->notification_task_created ?? true;
        $this->notification_task_assigned = $user->notification_task_assigned ?? true;
        $this->notification_task_updated = $user->notification_task_updated ?? true;
        $this->notification_task_deadline = $user->notification_task_deadline ?? true;
        $this->dark_mode = $user->dark_mode ?? false;
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
            'telegram_chat_id' => $this->telegram_chat_id,
            'notification_email_enabled' => $this->notification_email_enabled,
            'notification_telegram_enabled' => $this->notification_telegram_enabled,
            'notification_task_created' => $this->notification_task_created,
            'notification_task_assigned' => $this->notification_task_assigned,
            'notification_task_updated' => $this->notification_task_updated,
            'notification_task_deadline' => $this->notification_task_deadline,
            'dark_mode' => $this->dark_mode,
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
        notify()->success('Profil actualizat cu succes!');
    }

    public function getTelegramChatId()
    {
        $settings = Setting::first();
        if (!$settings || !$settings->telegram_bot_token) {
            notify()->error('Token-ul Telegram Bot nu este configurat. Configurează-l în Setări → General → Telegram Bot Token.');
            return;
        }

        try {
            $response = Http::get("https://api.telegram.org/bot{$settings->telegram_bot_token}/getUpdates");
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['ok']) && $data['ok'] && isset($data['result'])) {
                    $updates = $data['result'];
                    
                    // Căutăm ultimul mesaj de la utilizatorul curent
                    $user = Auth::user();
                    $chatId = null;
                    
                    // Căutăm după număr de telefon sau email
                    foreach ($updates as $update) {
                        if (isset($update['message']['from'])) {
                            $from = $update['message']['from'];
                            
                            // Verificăm dacă numărul de telefon se potrivește (dacă există)
                            if ($user->phone && isset($from['phone_number']) && 
                                $from['phone_number'] === $user->phone) {
                                $chatId = $from['id'];
                                break;
                            }
                            
                            // Dacă nu găsim prin telefon, luăm ultimul chat_id găsit
                            if (isset($from['id'])) {
                                $chatId = $from['id'];
                            }
                        }
                    }
                    
                    if ($chatId) {
                        $this->telegram_chat_id = (string)$chatId;
                        $user->update(['telegram_chat_id' => $this->telegram_chat_id]);
                        notify()->success('Chat ID obținut cu succes! Asigură-te că ai început o conversație cu bot-ul Telegram.');
                    } else {
                        notify()->error('Nu s-a găsit un Chat ID. Asigură-te că ai început o conversație cu bot-ul Telegram trimițând /start.');
                    }
                } else {
                    notify()->error('Nu s-au putut obține actualizările de la Telegram. Verifică token-ul bot-ului.');
                }
            } else {
                notify()->error('Eroare la comunicarea cu Telegram API. Verifică token-ul bot-ului.');
            }
        } catch (\Exception $e) {
            notify()->error('Eroare: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.profile-component')->layout('layouts.app');
    }
}

