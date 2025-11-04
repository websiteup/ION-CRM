<div>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>{{ $board->name }}</h2>
                <small class="text-muted">Proiect: {{ $board->project->name ?? '-' }}</small>
            </div>
        </div>

        <!-- Kanban Board (Read-Only) -->
        <div class="row" id="kanban-board">
            @foreach($board->columns as $column)
                <div class="col-md-3 mb-4">
                    <div class="card" style="border-top: 4px solid {{ $column->color }};">
                        <div class="card-header" style="background-color: {{ $column->color }}20;">
                            <h6 class="mb-0">{{ $column->name }}</h6>
                        </div>
                        <div class="card-body" style="min-height: 400px; max-height: 600px; overflow-y: auto;">
                            @foreach($column->tasks as $task)
                                <div class="card mb-2 task-card">
                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0">{{ $task->title }}</h6>
                                        </div>
                                        
                                        @if($task->description_html)
                                            <div class="mb-2 text-muted small">
                                                {!! Str::limit(strip_tags($task->description_html), 50) !!}
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

                            @if($column->tasks->count() === 0)
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-inbox"></i><br>
                                    <small>Nu există task-uri</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

