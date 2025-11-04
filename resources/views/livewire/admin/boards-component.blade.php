<div>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Board-uri</h2>
            <button wire:click="openModal" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Adaugă Board
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
                <div class="row mb-3">
                    <div class="col-md-6">
                        <input type="text" wire:model.live="search" class="form-control" placeholder="Caută după nume board...">
                    </div>
                    <div class="col-md-6">
                        <select wire:model.live="projectFilter" class="form-select">
                            <option value="">Toate proiectele</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }} @if($project->client) - {{ $project->client->first_name }} {{ $project->client->last_name }} @endif</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Nume</th>
                                <th>Proiect</th>
                                <th>Client</th>
                                <th>Status</th>
                                <th>Public</th>
                                <th>Acțiuni</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($boards as $board)
                                <tr>
                                    <td><strong>{{ $board->name }}</strong></td>
                                    <td>{{ $board->project->name ?? '-' }}</td>
                                    <td>{{ $board->project->client->first_name ?? '' }} {{ $board->project->client->last_name ?? '' }}</td>
                                    <td>{{ $board->project->status ?? '-' }}</td>
                                    <td>
                                        @if($board->is_public)
                                            <span class="badge bg-success">Da</span>
                                        @else
                                            <span class="badge bg-secondary">Nu</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.boards.view', $board->id) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i> Vezi
                                        </a>
                                        <button wire:click="openModal({{ $board->id }})" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        @if(Auth::user()->hasRole('admin'))
                                            <button wire:click="togglePublic({{ $board->id }})" class="btn btn-sm btn-{{ $board->is_public ? 'secondary' : 'primary' }}">
                                                <i class="bi bi-{{ $board->is_public ? 'lock' : 'unlock' }}"></i>
                                            </button>
                                        @endif
                                        <button wire:click="deleteBoard({{ $board->id }})" 
                                                wire:confirm="Ești sigur că vrei să ștergi acest board?" 
                                                class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Nu există board-uri.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $boards->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Board Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $boardId ? 'Editează Board' : 'Adaugă Board' }}</h5>
                        <button type="button" wire:click="closeModal" class="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveBoard">
                            <div class="mb-3">
                                <label for="boardName" class="form-label">Nume Board</label>
                                <input type="text" wire:model="boardName" class="form-control @error('boardName') is-invalid @enderror" id="boardName">
                                @error('boardName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="projectId" class="form-label">Proiect</label>
                                <select wire:model="projectId" class="form-select @error('projectId') is-invalid @enderror" id="projectId">
                                    <option value="">Selectează proiect</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->name }} @if($project->client) - {{ $project->client->first_name }} {{ $project->client->last_name }} @endif</option>
                                    @endforeach
                                </select>
                                @error('projectId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            @if($boardId && Auth::user()->hasRole('admin'))
                                <div class="mb-3 form-check">
                                    <input type="checkbox" wire:model="isPublic" class="form-check-input" id="isPublic">
                                    <label class="form-check-label" for="isPublic">Board Public (pentru clienți)</label>
                                </div>
                            @endif
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="closeModal" class="btn btn-secondary">Anulează</button>
                        <button type="button" wire:click="saveBoard" class="btn btn-primary">Salvează</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

