<div>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestionare Clienți</h2>
            <button wire:click="openModal" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Adaugă Client
            </button>
        </div>

        @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Caută după nume, email sau telefon...">
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tip</th>
                                <th>Nume</th>
                                <th>Email</th>
                                <th>Telefon</th>
                                <th>Țară</th>
                                <th>Acțiuni</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($clients as $client)
                                <tr>
                                    <td>{{ $client->id }}</td>
                                    <td>
                                        <span class="badge bg-{{ $client->type === 'customer' ? 'success' : 'warning' }}">
                                            {{ $client->type === 'customer' ? 'Client' : 'Lead' }}
                                        </span>
                                    </td>
                                    <td>{{ $client->first_name }} {{ $client->last_name }}</td>
                                    <td>{{ $client->email ?? '-' }}</td>
                                    <td>{{ $client->phone ?? '-' }}</td>
                                    <td>{{ $client->country ?? '-' }}</td>
                                    <td>
                                        <button wire:click="openModal({{ $client->id }})" class="btn btn-sm btn-info">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button wire:click="delete({{ $client->id }})" 
                                                wire:confirm="Ești sigur că vrei să ștergi acest client?" 
                                                class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Nu există clienți.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $clients->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $clientId ? 'Editează Client' : 'Adaugă Client' }}</h5>
                        <button type="button" wire:click="closeModal" class="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="type" class="form-label">Tip</label>
                                    <select wire:model="type" class="form-select @error('type') is-invalid @enderror" id="type">
                                        <option value="lead">Lead</option>
                                        <option value="customer">Client</option>
                                    </select>
                                    @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">Prenume</label>
                                    <input type="text" wire:model="first_name" class="form-control @error('first_name') is-invalid @enderror" id="first_name">
                                    @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Nume</label>
                                    <input type="text" wire:model="last_name" class="form-control @error('last_name') is-invalid @enderror" id="last_name">
                                    @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                                    <label for="country" class="form-label">Țară</label>
                                    <input type="text" wire:model="country" class="form-control @error('country') is-invalid @enderror" id="country">
                                    @error('country') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Adresă</label>
                                <textarea wire:model="address" class="form-control @error('address') is-invalid @enderror" id="address" rows="3"></textarea>
                                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
