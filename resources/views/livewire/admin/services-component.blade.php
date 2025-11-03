<div>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestionare Servicii</h2>
            <button wire:click="openModal" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Adaugă Serviciu
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
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Caută după nume sau descriere...">
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nume</th>
                                <th>Descriere</th>
                                <th>Preț Unitate</th>
                                <th>Tax</th>
                                <th>Tip Unitate</th>
                                <th>Foto</th>
                                <th>Acțiuni</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($services as $service)
                                <tr>
                                    <td>{{ $service->id }}</td>
                                    <td>{{ $service->name }}</td>
                                    <td>{{ Str::limit($service->description, 50) }}</td>
                                    <td>{{ number_format($service->unit_price, 2) }} RON</td>
                                    <td>{{ $service->tax }}%</td>
                                    <td>{{ $service->unit_type }}</td>
                                    <td>
                                        @if($service->photo)
                                            <img src="{{ asset('storage/' . $service->photo) }}" alt="{{ $service->name }}" style="width: 50px; height: 50px; object-fit: cover;" class="rounded">
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button wire:click="openModal({{ $service->id }})" class="btn btn-sm btn-info">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button wire:click="delete({{ $service->id }})" 
                                                wire:confirm="Ești sigur că vrei să ștergi acest serviciu?" 
                                                class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">Nu există servicii.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $services->links() }}
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
                        <h5 class="modal-title">{{ $serviceId ? 'Editează Serviciu' : 'Adaugă Serviciu' }}</h5>
                        <button type="button" wire:click="closeModal" class="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nume</label>
                                <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" id="name">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Descriere</label>
                                <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" id="description" rows="3"></textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="unit_price" class="form-label">Preț Unitate</label>
                                    <input type="number" step="0.01" wire:model="unit_price" class="form-control @error('unit_price') is-invalid @enderror" id="unit_price">
                                    @error('unit_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="tax" class="form-label">Tax (%)</label>
                                    <input type="number" step="0.01" wire:model="tax" class="form-control @error('tax') is-invalid @enderror" id="tax">
                                    @error('tax') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="unit_type" class="form-label">Tip Unitate</label>
                                    <input type="text" wire:model="unit_type" class="form-control @error('unit_type') is-invalid @enderror" id="unit_type" placeholder="ex: unit, oră, kg">
                                    @error('unit_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="photo" class="form-label">Foto</label>
                                <input type="file" wire:model="photo" class="form-control @error('photo') is-invalid @enderror" id="photo" accept="image/*">
                                @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @if($photoPreview)
                                    <div class="mt-2">
                                        <img src="{{ $photoPreview }}" alt="Preview" style="max-width: 200px; max-height: 200px;" class="img-thumbnail">
                                    </div>
                                @endif
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
