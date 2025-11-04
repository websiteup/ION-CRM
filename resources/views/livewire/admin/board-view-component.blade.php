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
                <a href="{{ route('admin.boards.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Înapoi
                </a>
            </div>
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
        <div class="row" id="kanban-board" wire:key="kanban-board-{{ $board->id }}">
            @foreach($columns as $column)
                <div class="col-md-3 mb-4" wire:key="column-{{ $column->id }}">
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
                                            <h6 class="mb-0">{{ $task->title }}</h6>
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

                            <button wire:click="openTaskModal({{ $column->id }})" class="btn btn-sm btn-outline-primary w-100 mt-2 no-drag">
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
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    let sortableInstances = {};

    document.addEventListener('livewire:init', () => {
        Livewire.hook('morph', ({ el, component }) => {
            setTimeout(() => {
                initializeSortable();
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
            }, 100);
        });
        
        Livewire.on('column-deleted', () => {
            setTimeout(() => {
                initializeSortable();
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
    });

    document.addEventListener('DOMContentLoaded', function() {
        initializeSortable();
    });

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
                    filter: '.btn, .no-drag',
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

