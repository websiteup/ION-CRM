<div>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>{{ $board->name }}</h2>
                <small class="text-muted">Proiect: {{ $board->project->name ?? '-' }}</small>
            </div>
            <div>
                <button wire:click="openColumnModal" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Adaugă Coloană
                </button>
                <button wire:click="openShareModal" class="btn btn-outline-primary">
                    <i class="bi bi-share"></i> Share
                </button>
                <a href="{{ route('admin.boards.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Înapoi
                </a>
            </div>
        </div>


        <!-- Filters -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Filtrează după prioritate</label>
                        <select wire:model.live="filterPriority" class="form-select">
                            <option value="">Toate</option>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Filtrează după utilizator</label>
                        <select wire:model.live="filterAssignee" class="form-select">
                            <option value="">Toți</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Filtrează după label</label>
                        <select wire:model.live="filterLabel" class="form-select">
                            <option value="">Toate</option>
                            @foreach($labels as $label)
                                <option value="{{ $label->id }}">{{ $label->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kanban Board -->
        <div class="kanban-board-container" id="kanban-board" wire:key="kanban-board-{{ $board->id }}">
            @foreach($columns as $column)
                <div class="kanban-column-wrapper" 
                     wire:key="column-{{ $column->id }}"
                     data-column-id="{{ $column->id }}">
                    <div class="card" style="border-top: 4px solid {{ $column->color }};">
                        <div class="card-header d-flex justify-content-between align-items-center" style="background-color: {{ $column->color }}20;">
                            @if($editingColumnId === $column->id)
                                <div class="flex-grow-1 me-2">
                                    <input type="text" 
                                           wire:model="editingColumnName" 
                                           wire:keydown.enter="saveColumnName({{ $column->id }})"
                                           wire:keydown.escape="cancelEditingColumn"
                                           class="form-control form-control-sm" 
                                           style="display: inline-block; width: auto;">
                                </div>
                                <div>
                                    <button wire:click="saveColumnName({{ $column->id }})" class="btn btn-sm btn-link text-success">
                                        <i class="bi bi-check"></i>
                                    </button>
                                    <button wire:click="cancelEditingColumn" class="btn btn-sm btn-link text-danger">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            @else
                                <h6 class="mb-0 flex-grow-1" style="cursor: pointer;" wire:click="startEditingColumn({{ $column->id }})" title="Click pentru a edita">
                                    {{ $column->name }}
                                </h6>
                                <div>
                                    <button wire:click="openColumnModal({{ $column->id }})" class="btn btn-sm btn-link text-dark" title="Editează coloana">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button wire:click="deleteColumn({{ $column->id }})" 
                                            wire:confirm="Ești sigur că vrei să ștergi această coloană?" 
                                            class="btn btn-sm btn-link text-danger"
                                            title="Șterge coloana">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                        <div class="card-body kanban-column-body" 
                             data-column-id="{{ $column->id }}" 
                             id="column-{{ $column->id }}"
                             style="min-height: 200px; max-height: 600px; overflow-y: auto;">
                            @php
                                $columnTasks = $column->filteredTasks ?? $column->tasks;
                            @endphp
                            @foreach($columnTasks as $task)
                                <div class="card mb-2 task-card" 
                                     data-task-id="{{ $task->id }}"
                                     data-column-id="{{ $column->id }}"
                                     wire:key="task-{{ $task->id }}">
                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0 task-title-clickable" wire:click.stop="openTaskModal(null, {{ $task->id }})">{{ $task->title }}</h6>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-link" type="button" data-bs-toggle="dropdown">
                                                    <i class="bi bi-three-dots"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" wire:click="openTaskModal(null, {{ $task->id }})"><i class="bi bi-pencil"></i> Editează</a></li>
                                                    <li><a class="dropdown-item text-danger" wire:click="deleteTask({{ $task->id }})" wire:confirm="Ești sigur că vrei să ștergi acest task?"><i class="bi bi-trash"></i> Șterge</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        
                                        @if($task->description_html)
                                            <div class="mb-2 text-muted small">
                                                {!! \Illuminate\Support\Str::limit(strip_tags($task->description_html), 50) !!}
                                            </div>
                                        @endif

                                        @if($task->labels->count() > 0)
                                            <div class="mb-2">
                                                @foreach($task->labels as $label)
                                                    <span class="badge" style="background-color: {{ $label->color }};">{{ $label->name }}</span>
                                                @endforeach
                                            </div>
                                        @endif

                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-{{ $task->priority === 'urgent' ? 'danger' : ($task->priority === 'high' ? 'warning' : ($task->priority === 'medium' ? 'info' : 'secondary')) }}">
                                                {{ ucfirst($task->priority) }}
                                            </span>
                                            @if($task->assignedUser)
                                                <small class="text-muted">
                                                    <i class="bi bi-person"></i> {{ $task->assignedUser->name }}
                                                </small>
                                            @endif
                                        </div>

                                        @if($task->due_date)
                                            <div class="mt-2">
                                                <small class="text-{{ $task->due_date->isPast() ? 'danger' : 'muted' }}">
                                                    <i class="bi bi-calendar"></i> {{ $task->due_date->format('d/m/Y') }}
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                            @if($columnTasks->count() === 0)
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-inbox"></i><br>
                                    <small>Nu există task-uri</small>
                                </div>
                            @endif
                        </div>
                        <div class="card-footer">
                            <button wire:click="openTaskModal({{ $column->id }})" class="btn btn-sm btn-outline-primary w-100 no-drag">
                                <i class="bi bi-plus"></i> Adaugă Task
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Task Modal -->
    @if($showTaskModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $taskId ? 'Editează Task' : 'Adaugă Task' }}</h5>
                        <button type="button" wire:click="closeTaskModal" class="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveTask">
                            <div class="mb-3">
                                <label for="taskTitle" class="form-label">Titlu <span class="text-danger">*</span></label>
                                <input type="text" wire:model="taskTitle" class="form-control @error('taskTitle') is-invalid @enderror" id="taskTitle">
                                @error('taskTitle') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="taskDescription" class="form-label">Descriere</label>
                                <textarea wire:model="taskDescription" id="taskDescription" class="form-control @error('taskDescription') is-invalid @enderror" rows="5"></textarea>
                                @error('taskDescription') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="taskColumnId" class="form-label">Coloană <span class="text-danger">*</span></label>
                                    <select wire:model="taskColumnId" class="form-select @error('taskColumnId') is-invalid @enderror" id="taskColumnId">
                                        <option value="">Selectează coloană</option>
                                        @foreach($board->columns as $col)
                                            <option value="{{ $col->id }}">{{ $col->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('taskColumnId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="taskPriority" class="form-label">Prioritate</label>
                                    <select wire:model="taskPriority" class="form-select @error('taskPriority') is-invalid @enderror" id="taskPriority">
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                    @error('taskPriority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="taskAssignedTo" class="form-label">Atribuit la</label>
                                    <select wire:model="taskAssignedTo" class="form-select @error('taskAssignedTo') is-invalid @enderror" id="taskAssignedTo">
                                        <option value="">Nimeni</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('taskAssignedTo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="taskDueDate" class="form-label">Data scadență</label>
                                    <input type="date" wire:model="taskDueDate" class="form-control @error('taskDueDate') is-invalid @enderror" id="taskDueDate">
                                    @error('taskDueDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Label-uri</label>
                                <div class="row">
                                    @foreach($labels as $label)
                                        <div class="col-md-3 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="{{ $label->id }}" 
                                                       wire:model="taskLabels" id="label-{{ $label->id }}">
                                                <label class="form-check-label" for="label-{{ $label->id }}">
                                                    <span class="badge" style="background-color: {{ $label->color }};">{{ $label->name }}</span>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="closeTaskModal" class="btn btn-secondary">Anulează</button>
                        <button type="button" wire:click="saveTask" class="btn btn-primary">Salvează</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Column Modal -->
    @if($showColumnModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $columnId ? 'Editează Coloană' : 'Adaugă Coloană' }}</h5>
                        <button type="button" wire:click="closeColumnModal" class="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveColumn">
                            <div class="mb-3">
                                <label for="columnName" class="form-label">Nume</label>
                                <input type="text" wire:model="columnName" class="form-control @error('columnName') is-invalid @enderror" id="columnName">
                                @error('columnName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="columnColor" class="form-label">Culoare</label>
                                <input type="color" wire:model="columnColor" class="form-control form-control-color @error('columnColor') is-invalid @enderror" id="columnColor">
                                @error('columnColor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="closeColumnModal" class="btn btn-secondary">Anulează</button>
                        <button type="button" wire:click="saveColumn" class="btn btn-primary">Salvează</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Share Modal -->
    @if($showShareModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1050;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Share board</h5>
                        <button type="button" wire:click="closeShareModal" class="btn-close"></button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <!-- Invite Members Section -->
                        <div class="mb-4">
                            <div class="d-flex gap-2 mb-3">
                                <div class="flex-grow-1">
                                    <input type="text" 
                                           wire:model="shareEmail" 
                                           class="form-control @error('shareEmail') is-invalid @enderror" 
                                           placeholder="Email address or name">
                                    @error('shareEmail') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>
                                <div>
                                    <select wire:model="inviteRole" class="form-select">
                                        <option value="member">Member</option>
                                        <option value="admin">Admin</option>
                                        <option value="viewer">Viewer</option>
                                    </select>
                                </div>
                                <button wire:click="inviteMember" class="btn btn-primary">
                                    Share
                                </button>
                            </div>
                        </div>

                        <!-- Link Sharing Section -->
                        @if($board->is_public)
                            <div class="mb-4">
                                <div class="d-flex align-items-start gap-3 mb-2">
                                    <i class="bi bi-link-45deg fs-4"></i>
                                    <div class="flex-grow-1">
                                        <p class="mb-2">Anyone with the link can join as a member</p>
                                        <div class="d-flex gap-2 align-items-center">
                                            @if($board->public_hash)
                                                <a href="#" wire:click.prevent="copyPublicLink" class="text-primary text-decoration-none">
                                                    Copy link
                                                </a>
                                                <span>|</span>
                                                <a href="#" wire:click.prevent="deletePublicLink" class="text-danger text-decoration-none">
                                                    Delete link
                                                </a>
                                            @else
                                                <span class="text-muted">Link-ul va fi generat după activarea board-ului public</span>
                                            @endif
                                            <span class="ms-auto">
                                                <select wire:model="linkPermissions" wire:change="updateLinkPermissions($event.target.value)" class="form-select form-select-sm" style="width: auto; display: inline-block;">
                                                    <option value="member">Change permissions</option>
                                                    <option value="member">Member</option>
                                                    <option value="admin">Admin</option>
                                                    <option value="viewer">Viewer</option>
                                                </select>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Public/Private Toggle -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       @if($board->is_public) checked @endif
                                       wire:change="togglePublic"
                                       id="boardPublicToggle">
                                <label class="form-check-label" for="boardPublicToggle">
                                    Board public
                                </label>
                            </div>
                            @if($board->is_public && $board->public_hash)
                                <div class="mt-2">
                                    <small class="text-muted">Link public: 
                                        <a href="{{ route('public.board', $board->public_hash) }}" target="_blank">
                                            {{ route('public.board', $board->public_hash) }}
                                        </a>
                                    </small>
                                </div>
                            @endif
                        </div>

                        <!-- Tabs -->
                        <ul class="nav nav-tabs mb-3" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeShareTab === 'members' ? 'active' : '' }}" 
                                        wire:click="switchShareTab('members')" 
                                        type="button">
                                    Board members
                                    @if($boardMembers->count() > 0)
                                        <span class="badge bg-secondary">{{ $boardMembers->count() }}</span>
                                    @endif
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeShareTab === 'requests' ? 'active' : '' }}" 
                                        wire:click="switchShareTab('requests')" 
                                        type="button">
                                    Join requests
                                </button>
                            </li>
                        </ul>

                        <!-- Tab Content: Board Members -->
                        @if($activeShareTab === 'members')
                            <div class="list-group">
                                @forelse($boardMembers as $member)
                                    <div class="list-group-item d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-3">
                                            @if($member->profile_photo)
                                                <img src="{{ asset('storage/' . $member->profile_photo) }}" 
                                                     alt="{{ $member->name }}" 
                                                     class="rounded-circle" 
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px;">
                                                    <span class="text-white">{{ strtoupper(substr($member->name, 0, 1)) }}</span>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-bold">
                                                    {{ $member->first_name }} {{ $member->last_name }}
                                                    @if($member->id === Auth::id())
                                                        <span class="text-muted">(you)</span>
                                                    @endif
                                                </div>
                                                <div class="text-muted small">
                                                    {{ $member->email }}
                                                    @if($member->position)
                                                        • {{ $member->position }}
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            @php
                                                $memberPivot = $board->members()->wherePivot('user_id', $member->id)->first();
                                                $memberRole = $memberPivot ? $memberPivot->pivot->role : 'member';
                                                $adminCount = $boardMembers->filter(function($m) use ($board) {
                                                    $pivot = $board->members()->wherePivot('user_id', $m->id)->first();
                                                    return $pivot && $pivot->pivot->role === 'admin';
                                                })->count();
                                            @endphp
                                            <select wire:change="updateMemberRole({{ $member->id }}, $event.target.value)"
                                                    class="form-select form-select-sm" 
                                                    style="width: auto;">
                                                <option value="member" {{ $memberRole === 'member' ? 'selected' : '' }}>Member</option>
                                                <option value="admin" {{ $memberRole === 'admin' ? 'selected' : '' }}>Admin</option>
                                                <option value="viewer" {{ $memberRole === 'viewer' ? 'selected' : '' }}>Viewer</option>
                                            </select>
                                            @if($member->id !== Auth::id() || $adminCount > 1)
                                                <button wire:click="removeMember({{ $member->id }})" 
                                                        wire:confirm="Ești sigur că vrei să ștergi acest membru?"
                                                        class="btn btn-sm btn-link text-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-4 text-muted">
                                        <i class="bi bi-people fs-1"></i>
                                        <p class="mt-2">Nu există membri în board</p>
                                    </div>
                                @endforelse
                            </div>
                        @endif

                        <!-- Tab Content: Join Requests -->
                        @if($activeShareTab === 'requests')
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1"></i>
                                <p class="mt-2">Nu există cereri de alăturare</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    let sortableInstances = {};
    let columnSortable = null;

    document.addEventListener('livewire:init', () => {
        Livewire.hook('morph', ({ el, component }) => {
            setTimeout(() => {
                initializeSortable();
                initializeColumnSortable();
            }, 100);
        });
        
        // Listen for Livewire events
        Livewire.on('task-saved', () => {
            setTimeout(() => {
                initializeSortable();
            }, 100);
        });
        
        Livewire.on('task-deleted', () => {
            setTimeout(() => {
                initializeSortable();
            }, 100);
        });
        
        Livewire.on('column-saved', () => {
            setTimeout(() => {
                initializeSortable();
                initializeColumnSortable();
            }, 100);
        });
        
        Livewire.on('column-deleted', () => {
            setTimeout(() => {
                initializeSortable();
                initializeColumnSortable();
            }, 100);
        });
        
        Livewire.on('column-name-updated', () => {
            setTimeout(() => {
                initializeSortable();
            }, 100);
        });
        
        Livewire.on('task-position-updated', () => {
            setTimeout(() => {
                initializeSortable();
            }, 100);
        });

        Livewire.on('column-position-updated', () => {
            setTimeout(() => {
                initializeColumnSortable();
            }, 100);
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        initializeSortable();
        initializeColumnSortable();
    });

    function initializeColumnSortable() {
        const kanbanBoard = document.getElementById('kanban-board');
        if (!kanbanBoard) return;

        // Destroy existing instance
        if (columnSortable) {
            columnSortable.destroy();
            columnSortable = null;
        }

        // Initialize column sortable
        columnSortable = new Sortable(kanbanBoard, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            handle: '.card-header', // Allow dragging from header only
            filter: '.card-footer, .card-body, .no-drag, .btn', // Exclude footer, body, and buttons from drag
            onEnd: function(evt) {
                const columnWrappers = Array.from(kanbanBoard.querySelectorAll('.kanban-column-wrapper'));
                const columnIds = columnWrappers.map(wrapper => {
                    return wrapper.getAttribute('data-column-id');
                }).filter(id => id !== null);

                if (columnIds.length > 0) {
                    @this.call('updateColumnPosition', columnIds);
                }
            }
        });
    }

    function initializeSortable() {
        // Destroy existing instances
        Object.keys(sortableInstances).forEach(key => {
            if (sortableInstances[key]) {
                try {
                    sortableInstances[key].destroy();
                } catch (e) {
                    // Instance might already be destroyed
                }
            }
        });
        sortableInstances = {};

        // Initialize Sortable for each column
        const columnElements = document.querySelectorAll('[id^="column-"]');
        columnElements.forEach(function(columnElement) {
            const columnId = columnElement.getAttribute('id').replace('column-', '');
            const instanceKey = 'column-' + columnId;
            
            if (columnElement && !sortableInstances[instanceKey]) {
                sortableInstances[instanceKey] = new Sortable(columnElement, {
                    group: {
                        name: 'kanban',
                        pull: true,
                        put: true
                    },
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    filter: '.btn, .no-drag, .task-title-clickable',
                    draggable: '.task-card',
                    onEnd: function(evt) {
                        const taskId = evt.item.dataset.taskId;
                        const newColumnId = evt.to.dataset.columnId;
                        const newPosition = evt.newIndex;
                        
                        if (taskId && newColumnId && newPosition !== null) {
                            @this.call('updateTaskPosition', taskId, newColumnId, newPosition);
                        }
                    }
                });
            }
        });
    }

    // Initialize Summernote for task description
    let summernoteInitialized = false;

    function initializeSummernote() {
        if (!summernoteInitialized && $('#taskDescription').length) {
            $('#taskDescription').summernote({
                height: 200,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                callbacks: {
                    onChange: function(contents, $editable) {
                        @this.set('taskDescription', contents);
                    }
                }
            });
            summernoteInitialized = true;
        }
    }

    function destroySummernote() {
        if ($('#taskDescription').length && $('#taskDescription').summernote('code') !== undefined) {
            $('#taskDescription').summernote('destroy');
            summernoteInitialized = false;
        }
    }

    document.addEventListener('livewire:init', () => {
        Livewire.hook('morph', ({ el, component }) => {
            if (document.querySelector('.modal.show')) {
                setTimeout(() => {
                    initializeSummernote();
                }, 300);
            } else {
                destroySummernote();
            }
        });
    });

    // Watch for modal open/close
    document.addEventListener('DOMContentLoaded', function() {
        const observer = new MutationObserver(function(mutations) {
            if (document.querySelector('.modal.show') && document.getElementById('taskDescription')) {
                setTimeout(() => {
                    initializeSummernote();
                }, 200);
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        // Copy to clipboard functionality
        Livewire.on('copy-to-clipboard', (data) => {
            if (data.url) {
                navigator.clipboard.writeText(data.url).then(() => {
                    // Toast notification is already shown via Livewire event
                }).catch(err => {
                    console.error('Failed to copy:', err);
                    toastr.error('Nu s-a putut copia link-ul în clipboard!', 'Eroare!');
                });
            }
        });
    });
</script>
<style>
    .sortable-ghost {
        opacity: 0.4;
        background-color: #f0f0f0;
    }
    .sortable-drag {
        opacity: 0.8;
        transform: rotate(5deg);
    }
    .task-card:hover {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    .task-card {
        user-select: none;
        cursor: move;
        flex-shrink: 0;
    }
    .kanban-column-body {
        min-height: 200px !important;
        display: flex;
        flex-direction: column;
    }
    .task-card .card-body {
        padding: 0.5rem !important;
    }
    .no-drag {
        cursor: pointer;
        flex-shrink: 0;
    }
</style>
@endpush

