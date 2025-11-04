<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Board;
use App\Models\BoardColumn;
use App\Models\Task;
use App\Models\Label;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BoardViewComponent extends Component
{
    public $boardId;
    public $board;
    
    // Task Modal
    public $taskId = null;
    public $taskTitle = '';
    public $taskDescription = '';
    public $taskColumnId = '';
    public $taskAssignedTo = '';
    public $taskPriority = 'medium';
    public $taskDueDate = '';
    public $taskLabels = [];
    public $showTaskModal = false;

    // Column Modal
    public $columnId = null;
    public $columnName = '';
    public $columnColor = '#6c757d';
    public $showColumnModal = false;
    
    // Inline editing for column name
    public $editingColumnId = null;
    public $editingColumnName = '';

    // Filters
    public $filterPriority = '';
    public $filterAssignee = '';
    public $filterLabel = '';

    public function mount($id)
    {
        $user = Auth::user();
        if (!$user->hasRole('admin') && !$user->hasRole('manager')) {
            abort(403, 'Nu ai permisiunea de a accesa această pagină.');
        }

        $this->boardId = $id;
        $this->loadBoard();
    }

    public function loadBoard()
    {
        $this->board = Board::with(['columns.tasks' => function($query) {
            $query->orderBy('position');
        }, 'columns.tasks.assignedUser', 'columns.tasks.labels'])->findOrFail($this->boardId);
    }

    public function openTaskModal($columnId = null, $taskId = null)
    {
        if ($taskId) {
            $task = Task::with('labels')->findOrFail($taskId);
            $this->taskId = $taskId;
            $this->taskTitle = $task->title;
            $this->taskDescription = $task->description_html ?? '';
            $this->taskColumnId = $task->board_column_id;
            $this->taskAssignedTo = $task->assigned_to ?? '';
            $this->taskPriority = $task->priority;
            $this->taskDueDate = $task->due_date ? $task->due_date->format('Y-m-d') : '';
            $this->taskLabels = $task->labels->pluck('id')->toArray();
        } else {
            $this->resetTaskForm();
            $this->taskColumnId = $columnId ?? ($this->board->columns->first()->id ?? '');
        }
        $this->showTaskModal = true;
    }

    public function closeTaskModal()
    {
        $this->showTaskModal = false;
        $this->resetTaskForm();
    }

    public function resetTaskForm()
    {
        $this->taskId = null;
        $this->taskTitle = '';
        $this->taskDescription = '';
        $this->taskColumnId = '';
        $this->taskAssignedTo = '';
        $this->taskPriority = 'medium';
        $this->taskDueDate = '';
        $this->taskLabels = [];
        $this->resetValidation();
    }

    public function saveTask()
    {
        $this->validate([
            'taskTitle' => 'required|string|max:255',
            'taskColumnId' => 'required|exists:board_columns,id',
            'taskPriority' => 'required|in:low,medium,high,urgent',
            'taskAssignedTo' => 'nullable|exists:users,id',
            'taskDueDate' => 'nullable|date',
        ]);

        $column = BoardColumn::findOrFail($this->taskColumnId);
        $maxPosition = Task::where('board_column_id', $this->taskColumnId)->max('position') ?? 0;

        $data = [
            'title' => $this->taskTitle,
            'description_html' => $this->taskDescription,
            'board_column_id' => $this->taskColumnId,
            'project_id' => $this->board->project_id,
            'assigned_to' => $this->taskAssignedTo ?: null,
            'priority' => $this->taskPriority,
            'due_date' => $this->taskDueDate ?: null,
            'position' => $maxPosition + 1,
        ];

        if ($this->taskId) {
            $task = Task::findOrFail($this->taskId);
            $data['updated_by'] = Auth::id();
            $task->update($data);
            $task->labels()->sync($this->taskLabels);
            session()->flash('message', 'Task actualizat cu succes!');
        } else {
            $data['created_by'] = Auth::id();
            $task = Task::create($data);
            $task->labels()->sync($this->taskLabels);
            session()->flash('message', 'Task creat cu succes!');
        }

        $this->closeTaskModal();
        $this->loadBoard();
    }

    public function deleteTask($id)
    {
        Task::findOrFail($id)->delete();
        session()->flash('message', 'Task șters cu succes!');
        $this->loadBoard();
    }

    public function updateTaskPosition($taskId, $columnId, $position)
    {
        $task = Task::findOrFail($taskId);
        $oldColumnId = $task->board_column_id;
        
        // Update task column
        $task->board_column_id = $columnId;
        $task->updated_by = Auth::id();
        $task->save();

        // Get all tasks in the new column (excluding the moved task)
        $tasksInNewColumn = Task::where('board_column_id', $columnId)
            ->where('id', '!=', $taskId)
            ->orderBy('position')
            ->get();

        // Reorder all tasks in the new column to consecutive positions
        // Insert the moved task at the specified position
        $pos = 0;
        foreach ($tasksInNewColumn as $t) {
            if ($pos >= $position) {
                // Shift tasks after the insertion point
                $t->position = $pos + 1;
            } else {
                // Keep tasks before the insertion point
                $t->position = $pos;
            }
            $t->save();
            $pos++;
        }
        
        // Set the moved task's position
        $task->position = $position;
        $task->save();

        // Reorder tasks in the old column if moved from a different column
        if ($oldColumnId != $columnId) {
            $tasksInOldColumn = Task::where('board_column_id', $oldColumnId)
                ->orderBy('position')
                ->get();
            
            // Reorder to consecutive positions (0, 1, 2, ...)
            $pos = 0;
            foreach ($tasksInOldColumn as $t) {
                $t->position = $pos;
                $t->save();
                $pos++;
            }
        }

        $this->loadBoard();
        $this->dispatch('task-position-updated');
    }

    public function openColumnModal($columnId = null)
    {
        if ($columnId) {
            $column = BoardColumn::findOrFail($columnId);
            $this->columnId = $columnId;
            $this->columnName = $column->name;
            $this->columnColor = $column->color;
        } else {
            $this->resetColumnForm();
        }
        $this->showColumnModal = true;
    }

    public function closeColumnModal()
    {
        $this->showColumnModal = false;
        $this->resetColumnForm();
    }

    public function resetColumnForm()
    {
        $this->columnId = null;
        $this->columnName = '';
        $this->columnColor = '#6c757d';
        $this->resetValidation();
    }

    public function saveColumn()
    {
        $this->validate([
            'columnName' => 'required|string|max:255',
            'columnColor' => 'required|string|max:7',
        ]);

        if ($this->columnId) {
            $column = BoardColumn::findOrFail($this->columnId);
            $column->update([
                'name' => $this->columnName,
                'color' => $this->columnColor,
            ]);
            session()->flash('message', 'Coloană actualizată cu succes!');
        } else {
            $maxPosition = BoardColumn::where('board_id', $this->boardId)->max('position') ?? 0;
            BoardColumn::create([
                'board_id' => $this->boardId,
                'name' => $this->columnName,
                'color' => $this->columnColor,
                'position' => $maxPosition + 1,
            ]);
            session()->flash('message', 'Coloană creată cu succes!');
        }

        $this->closeColumnModal();
        $this->loadBoard();
        $this->dispatch('column-saved');
    }

    public function deleteColumn($id)
    {
        $column = BoardColumn::findOrFail($id);
        if ($column->tasks()->count() > 0) {
            session()->flash('error', 'Nu poți șterge o coloană care conține task-uri!');
            return;
        }
        $column->delete();
        session()->flash('message', 'Coloană ștearsă cu succes!');
        $this->loadBoard();
        $this->dispatch('column-deleted');
    }

    // Inline editing for column name
    public function startEditingColumn($columnId)
    {
        $column = BoardColumn::findOrFail($columnId);
        $this->editingColumnId = $columnId;
        $this->editingColumnName = $column->name;
    }

    public function cancelEditingColumn()
    {
        $this->editingColumnId = null;
        $this->editingColumnName = '';
    }

    public function saveColumnName($columnId)
    {
        $this->validate([
            'editingColumnName' => 'required|string|max:255',
        ]);

        $column = BoardColumn::findOrFail($columnId);
        $column->update(['name' => $this->editingColumnName]);
        
        $this->editingColumnId = null;
        $this->editingColumnName = '';
        $this->loadBoard();
        $this->dispatch('column-name-updated');
    }

    public function render()
    {
        $this->loadBoard();
        
        $users = User::orderBy('name')->get();
        $labels = Label::orderBy('type')->orderBy('name')->get();

        // Filter tasks if needed
        $columns = $this->board->columns->map(function($column) {
            $tasks = $column->tasks;
            
            if ($this->filterPriority) {
                $tasks = $tasks->filter(function($task) {
                    return $task->priority === $this->filterPriority;
                });
            }
            
            if ($this->filterAssignee) {
                $tasks = $tasks->filter(function($task) {
                    return $task->assigned_to == $this->filterAssignee;
                });
            }
            
            if ($this->filterLabel) {
                $tasks = $tasks->filter(function($task) {
                    return $task->labels->contains('id', $this->filterLabel);
                });
            }
            
            $column->filteredTasks = $tasks;
            return $column;
        });

        return view('livewire.admin.board-view-component', [
            'board' => $this->board,
            'columns' => $columns,
            'users' => $users,
            'labels' => $labels,
        ])->layout('layouts.app');
    }
}

