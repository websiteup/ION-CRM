<div>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestionare Utilizatori</h2>
            <button wire:click="openModal" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Adaugă Utilizator
            </button>
        </div>

        @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Caută după nume, email sau nickname...">
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Foto</th>
                                <th>Nume Complet</th>
                                <th>Nickname</th>
                                <th>Poziție</th>
                                <th>Email</th>
                                <th>Telefon</th>
                                <th>Roluri</th>
                                <th>Acțiuni</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>
                                        @if($user->profile_photo)
                                            <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="{{ $user->name }}" style="width: 50px; height: 50px; object-fit: cover;" class="rounded-circle">
                                        @else
                                            <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                <i class="bi bi-person text-white"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $user->first_name }} {{ $user->last_name }}
                                        @if(!$user->first_name && !$user->last_name)
                                            {{ $user->name }}
                                        @endif
                                    </td>
                                    <td>{{ $user->nickname ?? '-' }}</td>
                                    <td>{{ $user->position ?? '-' }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->phone ?? '-' }}</td>
                                    <td>
                                        @foreach($user->roles as $role)
                                            <span class="badge bg-info me-1">{{ $role->name }}</span>
                                        @endforeach
                                        @if($user->roles->isEmpty())
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button wire:click="openModal({{ $user->id }})" class="btn btn-sm btn-info">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        @php
                                            $isAdmin = $user->hasRole('admin');
                                            $adminRole = \App\Models\Role::where('slug', 'admin')->first();
                                            $adminCount = $adminRole ? $adminRole->users()->count() : 0;
                                            $isOnlyAdmin = $isAdmin && $adminCount <= 1;
                                            $isCurrentUser = $user->id === auth()->id();
                                        @endphp
                                        @if($isOnlyAdmin || ($isAdmin && $isCurrentUser && $adminCount <= 1))
                                            <button class="btn btn-sm btn-danger" disabled title="Nu poți șterge singurul administrator">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @else
                                            <button wire:click="delete({{ $user->id }})" 
                                                    wire:confirm="Ești sigur că vrei să ștergi acest utilizator?" 
                                                    class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">Nu există utilizatori.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);" wire:ignore.self>
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $userId ? 'Editează Utilizator' : 'Adaugă Utilizator' }}</h5>
                        <button type="button" wire:click="closeModal" class="btn-close"></button>
                    </div>
                    <div class="modal-body">
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
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Telefon</label>
                                    <input type="text" wire:model="phone" class="form-control @error('phone') is-invalid @enderror" id="phone">
                                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="profile_photo" class="form-label">Poza de Profil</label>
                                    <input type="file" wire:model="profile_photo" class="form-control @error('profile_photo') is-invalid @enderror" id="profile_photo" accept="image/*">
                                    @error('profile_photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    @if($photoPreview)
                                        <div class="mt-2">
                                            <img src="{{ $photoPreview }}" alt="Preview" style="max-width: 150px; max-height: 150px;" class="img-thumbnail rounded-circle">
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Parolă {{ $userId ? '(lasă gol pentru a nu schimba)' : '' }}</label>
                                    <input type="password" wire:model="password" class="form-control @error('password') is-invalid @enderror" id="password">
                                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                @if(!$userId || $password)
                                    <div class="col-md-6 mb-3">
                                        <label for="password_confirmation" class="form-label">Confirmă Parola</label>
                                        <input type="password" wire:model="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" id="password_confirmation">
                                        @error('password_confirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                @endif
                            </div>
                            <div class="mb-3">
                                <label for="roles" class="form-label">Roluri</label>
                                <div class="row">
                                    @foreach($roles as $role)
                                        <div class="col-md-3 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" wire:model="selectedRoles" value="{{ $role->id }}" id="role_{{ $role->id }}">
                                                <label class="form-check-label" for="role_{{ $role->id }}">
                                                    {{ $role->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if($roles->isEmpty())
                                        <div class="col-12">
                                            <p class="text-muted">Nu există roluri create. Creează roluri mai întâi.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="email_signature" class="form-label">Semnătură Email (HTML)</label>
                                <textarea wire:model="email_signature" class="form-control @error('email_signature') is-invalid @enderror" id="email_signature" rows="10"></textarea>
                                @error('email_signature') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="closeModal" class="btn btn-secondary">Anulează</button>
                        <button type="button" wire:click="save" class="btn btn-primary">Salvează</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
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

    function destroySummernote() {
        if ($('#email_signature').length && $('#email_signature').summernote('code') !== undefined) {
            $('#email_signature').summernote('destroy');
            summernoteInitialized = false;
        }
    }

    // Inițializare când modal-ul este deschis
    document.addEventListener('livewire:init', () => {
        Livewire.hook('morph', ({ el, component }) => {
            // Verificăm dacă modal-ul este deschis
            if (document.querySelector('.modal.show')) {
                setTimeout(() => {
                    initializeSummernote();
                }, 300);
            } else {
                destroySummernote();
            }
        });
    });

    // Re-inițializare când Livewire actualizează DOM-ul
    document.addEventListener('DOMContentLoaded', function() {
        // Observăm schimbările în modal
        const observer = new MutationObserver(function(mutations) {
            if (document.querySelector('.modal.show') && document.getElementById('email_signature')) {
                setTimeout(() => {
                    initializeSummernote();
                }, 200);
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    });
</script>
@endpush

