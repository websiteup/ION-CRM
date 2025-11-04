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

    // Share Modal
    public $showShareModal = false;
    public $shareEmail = '';
    public $shareRole = 'member';
    public $activeShareTab = 'members';
    public $inviteRole = 'member';
    public $linkPermissions = 'member';

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
        $this->board = Board::with([
            'columns.tasks' => function($query) {
                $query->orderBy('position');
            }, 
            'columns.tasks.assignedUser', 
            'columns.tasks.labels',
            'members'
        ])->findOrFail($this->boardId);
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
            $this->dispatch('show-toast', [
                'type' => 'success',
                'title' => 'Task actualizat',
                'message' => 'Task-ul "' . $this->taskTitle . '" a fost actualizat cu succes!'
            ]);
        } else {
            $data['created_by'] = Auth::id();
            $task = Task::create($data);
            $task->labels()->sync($this->taskLabels);
            $column = BoardColumn::find($this->taskColumnId);
            $columnName = $column ? $column->name : 'coloana selectată';
            $this->dispatch('show-toast', [
                'type' => 'success',
                'title' => 'Task creat',
                'message' => 'Task-ul "' . $this->taskTitle . '" a fost creat cu succes în coloana "' . $columnName . '"!'
            ]);
        }

        $this->closeTaskModal();
        $this->loadBoard();
    }

    public function deleteTask($id)
    {
        $task = Task::findOrFail($id);
        $taskTitle = $task->title;
        $task->delete();
        $this->dispatch('show-toast', [
            'type' => 'success',
            'title' => 'Task șters',
            'message' => 'Task-ul "' . $taskTitle . '" a fost șters cu succes!'
        ]);
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

    public function updateColumnPosition($columnIds)
    {
        foreach ($columnIds as $index => $columnId) {
            BoardColumn::where('id', $columnId)
                ->where('board_id', $this->boardId)
                ->update(['position' => $index]);
        }
        
        $this->loadBoard();
        $this->dispatch('column-position-updated');
        $this->dispatch('show-toast', [
            'type' => 'success',
            'title' => 'Ordine coloane actualizată',
            'message' => 'Ordinea coloanelor a fost actualizată cu succes!'
        ]);
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
            $this->dispatch('show-toast', [
                'type' => 'success',
                'title' => 'Coloană actualizată',
                'message' => 'Coloana "' . $this->columnName . '" a fost actualizată cu succes!'
            ]);
        } else {
            $maxPosition = BoardColumn::where('board_id', $this->boardId)->max('position') ?? 0;
            BoardColumn::create([
                'board_id' => $this->boardId,
                'name' => $this->columnName,
                'color' => $this->columnColor,
                'position' => $maxPosition + 1,
            ]);
            $this->dispatch('show-toast', [
                'type' => 'success',
                'title' => 'Coloană creată',
                'message' => 'Coloana "' . $this->columnName . '" a fost creată cu succes în board!'
            ]);
        }

        $this->closeColumnModal();
        $this->loadBoard();
        $this->dispatch('column-saved');
    }

    public function deleteColumn($id)
    {
        $column = BoardColumn::findOrFail($id);
        $columnName = $column->name;
        $taskCount = $column->tasks()->count();
        
        if ($taskCount > 0) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'title' => 'Ștergere imposibilă',
                'message' => 'Nu poți șterge coloana "' . $columnName . '" deoarece conține ' . $taskCount . ' task-uri. Mută sau șterge task-urile înainte!'
            ]);
            return;
        }
        $column->delete();
        $this->dispatch('show-toast', [
            'type' => 'success',
            'title' => 'Coloană ștearsă',
            'message' => 'Coloana "' . $columnName . '" a fost ștearsă cu succes din board!'
        ]);
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
        $oldName = $column->name;
        $column->update(['name' => $this->editingColumnName]);
        
        $this->editingColumnId = null;
        $this->editingColumnName = '';
        $this->loadBoard();
        $this->dispatch('column-name-updated');
        $this->dispatch('show-toast', [
            'type' => 'success',
            'title' => 'Nume coloană actualizat',
            'message' => 'Numele coloanei a fost schimbat de la "' . $oldName . '" la "' . $this->editingColumnName . '"!'
        ]);
    }

    // Share Modal Methods
    public function openShareModal()
    {
        $this->showShareModal = true;
        $this->loadBoard();
    }

    public function closeShareModal()
    {
        $this->showShareModal = false;
        $this->shareEmail = '';
        $this->shareRole = 'member';
        $this->inviteRole = 'member';
        $this->resetValidation();
    }

    public function switchShareTab($tab)
    {
        $this->activeShareTab = $tab;
    }

    public function inviteMember()
    {
        $this->validate([
            'shareEmail' => 'required|email|exists:users,email',
        ], [
            'shareEmail.required' => 'Email-ul este obligatoriu.',
            'shareEmail.email' => 'Email-ul trebuie să fie valid.',
            'shareEmail.exists' => 'Utilizatorul cu acest email nu există în sistem.',
        ]);

        $user = User::where('email', $this->shareEmail)->first();

        // Check if user is already a member
        if ($this->board->members->contains($user->id)) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'title' => 'Membru deja existent',
                'message' => 'Utilizatorul "' . $user->first_name . ' ' . $user->last_name . '" (' . $user->email . ') este deja membru al acestui board!'
            ]);
            return;
        }

        // Add member
        $roleLabel = $this->inviteRole === 'admin' ? 'Administrator' : ($this->inviteRole === 'viewer' ? 'Viewer' : 'Membru');
        $this->board->members()->attach($user->id, ['role' => $this->inviteRole]);
        $this->dispatch('show-toast', [
            'type' => 'success',
            'title' => 'Membru adăugat',
            'message' => 'Utilizatorul "' . $user->first_name . ' ' . $user->last_name . '" a fost adăugat ca ' . strtolower($roleLabel) . ' în board!'
        ]);
        
        $this->shareEmail = '';
        $this->loadBoard();
    }

    public function updateMemberRole($userId, $role)
    {
        $user = User::findOrFail($userId);
        $roleLabels = [
            'admin' => 'Administrator',
            'member' => 'Membru',
            'viewer' => 'Viewer'
        ];
        
        // Check if user is removing themselves as admin and they're the only admin
        if ($userId == Auth::id() && $role !== 'admin') {
            $adminCount = $this->board->members()->wherePivot('role', 'admin')->count();
            if ($adminCount <= 1) {
                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'title' => 'Schimbare rol imposibilă',
                    'message' => 'Nu poți schimba propriul rol deoarece ești ultimul administrator al board-ului!'
                ]);
                $this->loadBoard();
                return;
            }
        }

        $oldRole = $this->board->members()->wherePivot('user_id', $userId)->first()->pivot->role ?? 'member';
        $this->board->members()->updateExistingPivot($userId, ['role' => $role]);
        $this->dispatch('show-toast', [
            'type' => 'success',
            'title' => 'Rol actualizat',
            'message' => 'Rolul utilizatorului "' . $user->first_name . ' ' . $user->last_name . '" a fost schimbat de la "' . ($roleLabels[$oldRole] ?? $oldRole) . '" la "' . ($roleLabels[$role] ?? $role) . '"!'
        ]);
        $this->loadBoard();
    }

    public function removeMember($userId)
    {
        $user = User::findOrFail($userId);
        $userName = $user->first_name . ' ' . $user->last_name;
        
        // Prevent removing yourself if you're the only admin
        if ($userId == Auth::id()) {
            $adminMembers = $this->board->members()->wherePivot('role', 'admin')->count();
            if ($adminMembers <= 1) {
                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'title' => 'Ștergere imposibilă',
                    'message' => 'Nu poți șterge propriul cont deoarece ești ultimul administrator al board-ului!'
                ]);
                return;
            }
        }

        $this->board->members()->detach($userId);
        $this->dispatch('show-toast', [
            'type' => 'success',
            'title' => 'Membru eliminat',
            'message' => 'Utilizatorul "' . $userName . '" a fost eliminat din board!'
        ]);
        $this->loadBoard();
    }

    public function togglePublic()
    {
        $isPublic = !$this->board->is_public;
        
        $this->board->is_public = $isPublic;
        
        if ($isPublic) {
            if (!$this->board->public_hash) {
                $this->board->generatePublicHash();
            }
        } else {
            $this->board->public_hash = null;
        }
        
        $this->board->updated_by = Auth::id();
        $this->board->save();
        $this->loadBoard();
        
        if ($isPublic) {
            $this->dispatch('show-toast', [
                'type' => 'success',
                'title' => 'Board public activat',
                'message' => 'Board-ul "' . $this->board->name . '" este acum public! Poți partaja link-ul pentru acces.'
            ]);
        } else {
            $this->dispatch('show-toast', [
                'type' => 'info',
                'title' => 'Board privat',
                'message' => 'Board-ul "' . $this->board->name . '" este acum privat. Doar membrii pot accesa board-ul.'
            ]);
        }
    }

    public function copyPublicLink()
    {
        if (!$this->board->is_public || !$this->board->public_hash) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'title' => 'Link indisponibil',
                'message' => 'Board-ul nu este public sau nu are link generat. Activează opțiunea "Board public" mai întâi!'
            ]);
            return;
        }
        
        $url = route('public.board', $this->board->public_hash);
        $this->dispatch('copy-to-clipboard', url: $url);
        $this->dispatch('show-toast', [
            'type' => 'success',
            'title' => 'Link copiat',
            'message' => 'Link-ul public al board-ului "' . $this->board->name . '" a fost copiat în clipboard! Poți să-l partajezi acum.'
        ]);
    }

    public function deletePublicLink()
    {
        $this->board->is_public = false;
        $this->board->public_hash = null;
        $this->board->save();
        $this->loadBoard();
        $this->dispatch('show-toast', [
            'type' => 'info',
            'title' => 'Link public șters',
            'message' => 'Link-ul public al board-ului "' . $this->board->name . '" a fost șters. Board-ul este acum privat.'
        ]);
    }

    public function updateLinkPermissions($permissions)
    {
        $this->linkPermissions = $permissions;
        // Note: Link permissions can be stored in a separate column if needed
        // For now, we'll use the default role when joining via link
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

        // Load board members with roles
        $boardMembers = $this->board->members()->with('roles')->get();

        return view('livewire.admin.board-view-component', [
            'board' => $this->board,
            'columns' => $columns,
            'users' => $users,
            'labels' => $labels,
            'boardMembers' => $boardMembers,
        ])->layout('layouts.app');
    }
}

