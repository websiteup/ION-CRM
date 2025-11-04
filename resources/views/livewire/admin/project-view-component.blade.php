<div>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>{{ $project->name }}</h2>
            <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Înapoi
            </a>
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

        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs mb-4" id="projectTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'details' ? 'active' : '' }}" wire:click="switchTab('details')" type="button">
                    Detalii Proiect
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'tasks' ? 'active' : '' }}" wire:click="switchTab('tasks')" type="button">
                    Repere & Task-uri
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'timesheet' ? 'active' : '' }}" wire:click="switchTab('timesheet')" type="button">
                    Foaie de Timp
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'invoice' ? 'active' : '' }}" wire:click="switchTab('invoice')" type="button">
                    Factură
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'cost' ? 'active' : '' }}" wire:click="switchTab('cost')" type="button">
                    Cost
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'files' ? 'active' : '' }}" wire:click="switchTab('files')" type="button">
                    Fișiere
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'notes' ? 'active' : '' }}" wire:click="switchTab('notes')" type="button">
                    Note
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'activity' ? 'active' : '' }}" wire:click="switchTab('activity')" type="button">
                    Jurnal de Activitate
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'edit' ? 'active' : '' }}" wire:click="switchTab('edit')" type="button">
                    Editează
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
            @if($activeTab === 'details')
                <div class="row">
                    <!-- Left Column - Project Details -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tbody>
                                        <tr>
                                            <td style="width: 200px;"><strong>Nume:</strong></td>
                                            <td>{{ $project->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Client:</strong></td>
                                            <td>{{ $project->client ? $project->client->first_name . ' ' . $project->client->last_name : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Progres:</strong></td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tipul de Facturare:</strong></td>
                                            <td>{{ $project->billing_type === 'fixed_rate' ? 'Rata Fixă' : 'Rata Orară' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                @if($project->status === 'not_started')
                                                    <span class="badge bg-secondary">Neinceput</span>
                                                @elseif($project->status === 'on_hold')
                                                    <span class="badge bg-warning">În așteptare</span>
                                                @elseif($project->status === 'in_progress')
                                                    <span class="badge bg-primary">În dezvoltare</span>
                                                @elseif($project->status === 'completed')
                                                    <span class="badge bg-success">Complet</span>
                                                @else
                                                    <span class="badge bg-danger">Anulat</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Rata Fixă:</strong></td>
                                            <td>{{ number_format($project->fixed_rate ?? 0, 2) }} {{ $currencySymbol }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Rata Orară:</strong></td>
                                            <td>{{ number_format($project->hourly_rate ?? 0, 2) }} {{ $currencySymbol }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Data de început:</strong></td>
                                            <td>{{ $project->start_date ? $project->start_date->format('d.m.Y') : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Data de sfârșit:</strong></td>
                                            <td>{{ $project->end_date ? $project->end_date->format('d.m.Y') : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Descrierea Proiectului:</strong></td>
                                            <td>
                                                @if($project->description_html)
                                                    <div>{!! $project->description_html !!}</div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Summary Metrics and Members -->
                    <div class="col-md-4">
                        <!-- Summary Metrics -->
                        <div class="row mb-3">
                            <div class="col-6 mb-3">
                                <div class="card text-center" style="border-left: 4px solid #0d6efd;">
                                    <div class="card-body p-3">
                                        <i class="bi bi-currency-dollar fs-3 text-primary"></i>
                                        <h6 class="mt-2 mb-0">Sume facturate</h6>
                                        <p class="mb-0"><strong>{{ number_format($invoicedAmount, 2) }} {{ $currencySymbol }}</strong></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="card text-center" style="border-left: 4px solid #198754;">
                                    <div class="card-body p-3">
                                        <i class="bi bi-credit-card fs-3 text-success"></i>
                                        <h6 class="mt-2 mb-0">Costuri</h6>
                                        <p class="mb-0"><strong>{{ number_format($costs, 2) }} {{ $currencySymbol }}</strong></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="card text-center" style="border-left: 4px solid #dc3545;">
                                    <div class="card-body p-3">
                                        <i class="bi bi-clock fs-3 text-danger"></i>
                                        <h6 class="mt-2 mb-0">Total Ore Lucrate</h6>
                                        <p class="mb-0"><strong>{{ $totalHours }} Ore</strong></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="card text-center" style="border-left: 4px solid #198754;">
                                    <div class="card-body p-3">
                                        <i class="bi bi-bar-chart fs-3 text-success"></i>
                                        <h6 class="mt-2 mb-0">Cost Orar Total</h6>
                                        <p class="mb-0"><strong>{{ number_format($totalHourlyCost, 2) }} {{ $currencySymbol }}</strong></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <div class="card text-center" style="border-left: 4px solid #0d6efd;">
                                    <div class="card-body p-3">
                                        <i class="bi bi-wallet fs-3 text-primary"></i>
                                        <h6 class="mt-2 mb-0">Sume în sold</h6>
                                        <p class="mb-0"><strong>{{ number_format($balanceAmount, 2) }} {{ $currencySymbol }}</strong></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Project Members -->
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Membrii Proiect</h6>
                                <button wire:click="$set('showAddMemberModal', true)" class="btn btn-sm btn-primary">
                                    <i class="bi bi-plus-circle"></i>
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Nume</th>
                                                <th>Șterge</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($project->members as $index => $member)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>
                                                        <i class="bi bi-person-circle"></i> {{ $member->name }}
                                                    </td>
                                                    <td>
                                                        <button wire:click="removeMember({{ $member->id }})" 
                                                                wire:confirm="Ești sigur că vrei să ștergi acest membru?" 
                                                                class="btn btn-sm btn-link text-success">
                                                            Șterge
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">Nu există membri</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($activeTab === 'tasks')
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Repere & Task-uri (Board-uri)</h5>
                        <a href="{{ route('admin.boards.index') }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle"></i> Adaugă Board
                        </a>
                    </div>
                    <div class="card-body">
                        @forelse($boards as $board)
                            <div class="mb-4">
                                <h6 class="mb-3">
                                    <a href="{{ route('admin.boards.view', $board->id) }}" class="text-decoration-none">
                                        {{ $board->name }}
                                        <i class="bi bi-arrow-right"></i>
                                    </a>
                                </h6>
                                <div class="row">
                                    @foreach($board->columns as $column)
                                        <div class="col-md-3 mb-3">
                                            <div class="card" style="border-top: 4px solid {{ $column->color }};">
                                                <div class="card-header" style="background-color: {{ $column->color }}20;">
                                                    <h6 class="mb-0">{{ $column->name }}</h6>
                                                </div>
                                                <div class="card-body" style="min-height: 200px; max-height: 400px; overflow-y: auto;">
                                                    @foreach($column->tasks as $task)
                                                        <div class="card mb-2">
                                                            <div class="card-body p-2">
                                                                <h6 class="mb-1">{{ $task->title }}</h6>
                                                                @if($task->assignedUser)
                                                                    <small class="text-muted">
                                                                        <i class="bi bi-person"></i> {{ $task->assignedUser->name }}
                                                                    </small>
                                                                @endif
                                                                @if($task->due_date)
                                                                    <div class="mt-1">
                                                                        <small class="text-{{ $task->due_date->isPast() ? 'danger' : 'muted' }}">
                                                                            <i class="bi bi-calendar"></i> {{ $task->due_date->format('d.m.Y') }}
                                                                        </small>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                    @if($column->tasks->count() === 0)
                                                        <div class="text-center text-muted py-3">
                                                            <small>Nu există task-uri</small>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <hr>
                        @empty
                            <div class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-3">Nu există board-uri pentru acest proiect.</p>
                                <a href="{{ route('admin.boards.index') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Creează primul board
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>
            @elseif($activeTab === 'edit')
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted">Secțiunea de editare va fi implementată în curând.</p>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted">Această secțiune va fi implementată în curând.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Add Member Modal -->
    @if($showAddMemberModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Adaugă Membru</h5>
                        <button type="button" wire:click="$set('showAddMemberModal', false)" class="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="addMember">
                            <div class="mb-3">
                                <label for="selectedUserId" class="form-label">Selectează Utilizator</label>
                                <select wire:model="selectedUserId" class="form-select @error('selectedUserId') is-invalid @enderror" id="selectedUserId">
                                    <option value="">Selectează utilizator</option>
                                    @foreach($users as $user)
                                        @if(!$project->members->contains($user->id))
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('selectedUserId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="$set('showAddMemberModal', false)" class="btn btn-secondary">Anulează</button>
                        <button type="button" wire:click="addMember" class="btn btn-primary">Adaugă</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

