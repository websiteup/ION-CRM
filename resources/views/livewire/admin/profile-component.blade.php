<div>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Profilul Meu</h2>
        </div>

        @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Informații Personale</h5>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="save">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">Prenume</label>
                                    <input type="text" wire:model="first_name" class="form-control @error('first_name') is-invalid @enderror" id="first_name">
                                    @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Nume</label>
                                    <input type="text" wire:model="last_name" class="form-control @error('last_name') is-invalid @enderror" id="last_name">
                                    @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nickname" class="form-label">Nickname</label>
                                    <input type="text" wire:model="nickname" class="form-control @error('nickname') is-invalid @enderror" id="nickname">
                                    @error('nickname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="position" class="form-label">Poziție</label>
                                    <input type="text" wire:model="position" class="form-control @error('position') is-invalid @enderror" id="position">
                                    @error('position') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Nume Complet (Display Name)</label>
                                    <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" id="name">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror" id="email">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Telefon</label>
                                <input type="text" wire:model="phone" class="form-control @error('phone') is-invalid @enderror" id="phone">
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Parolă Nouă (lasă gol pentru a nu schimba)</label>
                                <input type="password" wire:model="password" class="form-control @error('password') is-invalid @enderror" id="password">
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            @if($password)
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirmă Parola</label>
                                    <input type="password" wire:model="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" id="password_confirmation">
                                    @error('password_confirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            @endif
                            <div class="mb-3">
                                <label for="email_signature" class="form-label">Semnătură Email (HTML)</label>
                                <textarea wire:model="email_signature" class="form-control @error('email_signature') is-invalid @enderror" id="email_signature" rows="10"></textarea>
                                @error('email_signature') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <hr>
                            <h6 class="mb-3">Notificări</h6>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="notification_email_enabled" id="notification_email_enabled">
                                    <label class="form-check-label" for="notification_email_enabled">
                                        Activează notificări prin Email
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="notification_telegram_enabled" id="notification_telegram_enabled">
                                    <label class="form-check-label" for="notification_telegram_enabled">
                                        Activează notificări prin Telegram
                                    </label>
                                </div>
                            </div>
                            <hr>
                            <h6 class="mb-3">Tipuri de Notificări</h6>
                            <div class="mb-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="notification_task_created" id="notification_task_created">
                                    <label class="form-check-label" for="notification_task_created">
                                        Task creat
                                    </label>
                                </div>
                            </div>
                            <div class="mb-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="notification_task_assigned" id="notification_task_assigned">
                                    <label class="form-check-label" for="notification_task_assigned">
                                        Task atribuit
                                    </label>
                                </div>
                            </div>
                            <div class="mb-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="notification_task_updated" id="notification_task_updated">
                                    <label class="form-check-label" for="notification_task_updated">
                                        Task actualizat
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="notification_task_deadline" id="notification_task_deadline">
                                    <label class="form-check-label" for="notification_task_deadline">
                                        Deadline aproape
                                    </label>
                                </div>
                            </div>
                            <hr>
                            <h6 class="mb-3">Aspect</h6>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="dark_mode" id="dark_mode">
                                    <label class="form-check-label" for="dark_mode">
                                        <i class="bi bi-moon-stars"></i> Mod întunecat (Dark Mode)
                                    </label>
                                </div>
                                <small class="text-muted">Activează modul întunecat pentru interfață</small>
                            </div>
                            <hr>
                            <h6 class="mb-3">Conectare Telegram</h6>
                            <div class="mb-3">
                                <label for="telegram_chat_id" class="form-label">Telegram Chat ID</label>
                                <div class="input-group">
                                    <input type="text" wire:model="telegram_chat_id" class="form-control @error('telegram_chat_id') is-invalid @enderror" id="telegram_chat_id" placeholder="ID-ul va fi obținut automat" readonly>
                                    <button type="button" class="btn btn-outline-primary" wire:click="getTelegramChatId">
                                        <i class="bi bi-telegram"></i> Obține Chat ID
                                    </button>
                                </div>
                                @error('telegram_chat_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="form-text text-muted">
                                    Pentru a obține Chat ID-ul, începe o conversație cu bot-ul Telegram și apasă butonul "Obține Chat ID".
                                    <br>
                                    <strong>Instrucțiuni:</strong>
                                    <ol class="small">
                                        <li>Asigură-te că ai configurat Token-ul Bot în Setări (Admin → Setări → General → Telegram Bot Token)</li>
                                        <li>Deschide Telegram și căută bot-ul creat cu @BotFather</li>
                                        <li>Începe o conversație cu bot-ul trimițând comanda /start</li>
                                        <li>Apasă butonul "Obține Chat ID" de mai sus</li>
                                    </ol>
                                </small>
                            </div>
                            <button type="submit" class="btn btn-primary">Salvează Modificările</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Poza de Profil</h5>
                    </div>
                    <div class="card-body text-center">
                        @if($photoPreview)
                            <img src="{{ $photoPreview }}" alt="Profile Photo" class="img-thumbnail rounded-circle mb-3" style="width: 200px; height: 200px; object-fit: cover;">
                        @else
                            <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 200px; height: 200px;">
                                <i class="bi bi-person text-white" style="font-size: 80px;"></i>
                            </div>
                        @endif
                        <div class="mb-3">
                            <label for="profile_photo" class="form-label">Schimbă Poza de Profil</label>
                            <input type="file" wire:model="profile_photo" class="form-control @error('profile_photo') is-invalid @enderror" id="profile_photo" accept="image/*">
                            @error('profile_photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let summernoteInitialized = false;

    function initializeSummernote() {
        if ($('#email_signature').length && !summernoteInitialized) {
            // Distrugem instanța anterioară dacă există
            if ($('#email_signature').summernote('code')) {
                $('#email_signature').summernote('destroy');
            }
            
            $('#email_signature').summernote({
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                callbacks: {
                    onChange: function(contents) {
                        @this.set('email_signature', contents);
                    },
                    onBlur: function() {
                        @this.set('email_signature', $('#email_signature').summernote('code'));
                    }
                }
            });
            
            // Setăm conținutul dacă există
            if (@this.get('email_signature')) {
                $('#email_signature').summernote('code', @this.get('email_signature'));
            }
            
            summernoteInitialized = true;
        }
    }

    // Inițializare când componenta se încarcă
    document.addEventListener('livewire:init', () => {
        Livewire.hook('morph', ({ el, component }) => {
            if (document.getElementById('email_signature')) {
                setTimeout(() => {
                    initializeSummernote();
                }, 300);
            }
        });
    });

    // Inițializare la încărcarea paginii
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            initializeSummernote();
        }, 500);
    });
</script>
@endpush

