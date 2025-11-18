<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Board;
use App\Models\BoardColumn;
use App\Models\Task;
use App\Models\Label;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

    // Background Modal
    public $showBackgroundModal = false;
    public $selectedBackground = '';

    // Predefined backgrounds
    public function getBackgroundsProperty()
    {
        return [
            // Gradient backgrounds
            'gradient-1' => [
                'name' => 'Dark Blue Purple',
                'type' => 'gradient',
                'value' => 'linear-gradient(135deg, #1e3c72 0%, #2a5298 100%)',
                'icon' => 'bi-circle-fill'
            ],
            'gradient-2' => [
                'name' => 'Light Blue Cyan',
                'type' => 'gradient',
                'value' => 'linear-gradient(135deg, #74b9ff 0%, #00b894 100%)',
                'icon' => 'bi-snow'
            ],
            'gradient-3' => [
                'name' => 'Dark Blue Cloud',
                'type' => 'gradient',
                'value' => 'linear-gradient(135deg, #2c3e50 0%, #34495e 100%)',
                'icon' => 'bi-cloud-lightning'
            ],
            'gradient-4' => [
                'name' => 'Purple Pink',
                'type' => 'gradient',
                'value' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                'icon' => 'bi-stars'
            ],
            'gradient-5' => [
                'name' => 'Purple Light',
                'type' => 'gradient',
                'value' => 'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)',
                'icon' => 'bi-rainbow'
            ],
            'gradient-6' => [
                'name' => 'Orange Dark',
                'type' => 'gradient',
                'value' => 'linear-gradient(135deg, #ff9a56 0%, #ff6a88 100%)',
                'icon' => 'bi-circle'
            ],
            'gradient-7' => [
                'name' => 'Pink Red',
                'type' => 'gradient',
                'value' => 'linear-gradient(135deg, #ff6b9d 0%, #c44569 100%)',
                'icon' => 'bi-heart'
            ],
            'gradient-8' => [
                'name' => 'Teal Green',
                'type' => 'gradient',
                'value' => 'linear-gradient(135deg, #00d2ff 0%, #3a7bd5 100%)',
                'icon' => 'bi-globe'
            ],
            'gradient-9' => [
                'name' => 'Dark Blue Grey',
                'type' => 'gradient',
                'value' => 'linear-gradient(135deg, #1a1a2e 0%, #16213e 100%)',
                'icon' => 'bi-moon-stars'
            ],
            'gradient-10' => [
                'name' => 'Red Brown',
                'type' => 'gradient',
                'value' => 'linear-gradient(135deg, #c94b4b 0%, #4b134f 100%)',
                'icon' => 'bi-fire'
            ],
            'gradient-11' => [
                'name' => 'Ocean Blue',
                'type' => 'gradient',
                'value' => 'linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%)',
                'icon' => 'bi-droplet'
            ],
            'gradient-12' => [
                'name' => 'Sunset',
                'type' => 'gradient',
                'value' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                'icon' => 'bi-sun'
            ],
            // Solid colors
            'solid-blue' => [
                'name' => 'Blue',
                'type' => 'solid',
                'value' => '#0d6efd',
                'icon' => null
            ],
            'solid-orange' => [
                'name' => 'Orange',
                'type' => 'solid',
                'value' => '#fd7e14',
                'icon' => null
            ],
            'solid-green' => [
                'name' => 'Green',
                'type' => 'solid',
                'value' => '#198754',
                'icon' => null
            ],
            'solid-red' => [
                'name' => 'Red',
                'type' => 'solid',
                'value' => '#dc3545',
                'icon' => null
            ],
            'solid-purple' => [
                'name' => 'Purple',
                'type' => 'solid',
                'value' => '#6f42c1',
                'icon' => null
            ],
            'solid-pink' => [
                'name' => 'Pink',
                'type' => 'solid',
                'value' => '#d63384',
                'icon' => null
            ],
            'solid-teal' => [
                'name' => 'Teal',
                'type' => 'solid',
                'value' => '#20c997',
                'icon' => null
            ],
            'solid-cyan' => [
                'name' => 'Cyan',
                'type' => 'solid',
                'value' => '#0dcaf0',
                'icon' => null
            ],
            'solid-grey' => [
                'name' => 'Grey',
                'type' => 'solid',
                'value' => '#6c757d',
                'icon' => null
            ],
            'solid-dark' => [
                'name' => 'Dark Grey',
                'type' => 'solid',
                'value' => '#212529',
                'icon' => null
            ],
        ];
    }

    public function mount($id)
    {
        $user = Auth::user();
        if (!$user->hasRole('admin') && !$user->hasRole('manager')) {
            abort(403, 'Nu ai permisiunea de a accesa această pagină.');
        }

        $this->boardId = $id;
        $this->loadBoard();
        $this->selectedBackground = $this->board->background ?? '';
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
            $oldAssignedTo = $task->assigned_to;
            $data['updated_by'] = Auth::id();
            $task->update($data);
            $task->labels()->sync($this->taskLabels);
            
            // Reload task with relationships for notifications
            $task->load(['column.board', 'project', 'assignedUser', 'creator']);
            
            // Send notifications for updated task
            if ($task->assignedUser) {
                // Reîmprospătează utilizatorul pentru a avea preferințele actualizate
                $assignedUser = \App\Models\User::find($task->assignedUser->id);
                $assignedUser->refresh(); // Reîmprospătează pentru a avea preferințele actualizate
                
                if ($assignedUser && $assignedUser->notification_task_updated) {
                    try {
                        // Actualizăm token-ul din setări înainte de trimitere
                        $settings = \App\Models\Setting::first();
                        if ($settings && $settings->telegram_bot_token) {
                            // Folosim ambele metode pentru a ne asigura că token-ul este setat
                            config(['services.telegram.bot_token' => $settings->telegram_bot_token]);
                            \Illuminate\Support\Facades\Config::set('services.telegram.bot_token', $settings->telegram_bot_token);
                            // Forțăm refresh-ul configurației
                            \Illuminate\Support\Facades\Config::get('services.telegram.bot_token', $settings->telegram_bot_token);
                        }
                        
                        Log::info("Trimitere notificare 'updated' către utilizator {$assignedUser->id} ({$assignedUser->email})");
                        Log::info("Token verificat: " . (config('services.telegram.bot_token') ? 'EXISTS (' . substr(config('services.telegram.bot_token'), 0, 10) . '...)' : 'MISSING'));
                        $assignedUser->notify(new \App\Notifications\TaskUpdatedNotification($task));
                    } catch (\Exception $e) {
                        Log::error('Eroare la trimiterea notificării Telegram (updated): ' . $e->getMessage());
                        Log::error("Stack trace: " . $e->getTraceAsString());
                    }
                }
            }
            
            // If task was assigned to a new user, send assignment notification
            if ($this->taskAssignedTo && $this->taskAssignedTo != $oldAssignedTo) {
                $assignedUser = \App\Models\User::find($this->taskAssignedTo);
                $assignedUser->refresh(); // Reîmprospătează pentru a avea preferințele actualizate
                
                if ($assignedUser && $assignedUser->notification_task_assigned) {
                    try {
                        // Actualizăm token-ul din setări înainte de trimitere
                        $settings = \App\Models\Setting::first();
                        if ($settings && $settings->telegram_bot_token) {
                            // Folosim ambele metode pentru a ne asigura că token-ul este setat
                            config(['services.telegram.bot_token' => $settings->telegram_bot_token]);
                            \Illuminate\Support\Facades\Config::set('services.telegram.bot_token', $settings->telegram_bot_token);
                        }
                        
                        Log::info("Trimitere notificare 'assigned' către utilizator {$assignedUser->id} ({$assignedUser->email})");
                        $assignedUser->notify(new \App\Notifications\TaskAssignedNotification($task));
                    } catch (\Exception $e) {
                        Log::error('Eroare la trimiterea notificării Telegram (assigned): ' . $e->getMessage());
                        Log::error("Stack trace: " . $e->getTraceAsString());
                    }
                }
            }
            
            notify()->success('Task-ul "' . $this->taskTitle . '" a fost actualizat cu succes!');
        } else {
            $data['created_by'] = Auth::id();
            $task = Task::create($data);
            $task->labels()->sync($this->taskLabels);
            
            // Reload task with relationships for notifications
            $task->load(['column.board', 'project', 'assignedUser', 'creator']);
            
            // Send notifications for created task
            $usersToNotify = [];
            
            // Adaugă utilizatorul atribuit dacă există
            if ($task->assignedUser) {
                $assignedUser = \App\Models\User::find($task->assignedUser->id);
                if ($assignedUser) {
                    $usersToNotify[] = [
                        'user' => $assignedUser,
                        'notifications' => ['created', 'assigned']
                    ];
                }
            }
            
            // Adaugă creatorul dacă este diferit de utilizatorul atribuit
            if ($task->creator) {
                $creator = \App\Models\User::find($task->creator->id);
                if ($creator && (!$task->assignedUser || $creator->id != $task->assignedUser->id)) {
                    $usersToNotify[] = [
                        'user' => $creator,
                        'notifications' => ['created']
                    ];
                }
            }
            
            // Trimite notificările
            foreach ($usersToNotify as $item) {
                $user = $item['user'];
                $user->refresh(); // Reîmprospătează pentru a avea preferințele actualizate
                
                foreach ($item['notifications'] as $notificationType) {
                    try {
                        // Actualizăm token-ul din setări înainte de trimitere
                        $settings = \App\Models\Setting::first();
                        if ($settings && $settings->telegram_bot_token) {
                            // Folosim ambele metode pentru a ne asigura că token-ul este setat
                            config(['services.telegram.bot_token' => $settings->telegram_bot_token]);
                            \Illuminate\Support\Facades\Config::set('services.telegram.bot_token', $settings->telegram_bot_token);
                        }
                        
                        if ($notificationType === 'created' && $user->notification_task_created) {
                            Log::info("Trimitere notificare 'created' către utilizator {$user->id} ({$user->email})");
                            $user->notify(new \App\Notifications\TaskCreatedNotification($task));
                        } elseif ($notificationType === 'assigned' && $user->notification_task_assigned) {
                            Log::info("Trimitere notificare 'assigned' către utilizator {$user->id} ({$user->email})");
                            $user->notify(new \App\Notifications\TaskAssignedNotification($task));
                        }
                    } catch (\Exception $e) {
                        Log::error("Eroare la trimiterea notificării Telegram ({$notificationType}) către utilizator {$user->id}: " . $e->getMessage());
                        Log::error("Stack trace: " . $e->getTraceAsString());
                    }
                }
            }
            
            $column = BoardColumn::find($this->taskColumnId);
            $columnName = $column ? $column->name : 'coloana selectată';
            notify()->success('Task-ul "' . $this->taskTitle . '" a fost creat cu succes în coloana "' . $columnName . '"!');
        }

        $this->closeTaskModal();
        $this->loadBoard();
    }

    public function deleteTask($id)
    {
        $task = Task::findOrFail($id);
        $taskTitle = $task->title;
        $task->delete();
        notify()->success('Task-ul "' . $taskTitle . '" a fost șters cu succes!');
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
        notify()->success('Ordinea coloanelor a fost actualizată cu succes!');
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
            notify()->success('Coloana "' . $this->columnName . '" a fost actualizată cu succes!');
        } else {
            $maxPosition = BoardColumn::where('board_id', $this->boardId)->max('position') ?? 0;
            BoardColumn::create([
                'board_id' => $this->boardId,
                'name' => $this->columnName,
                'color' => $this->columnColor,
                'position' => $maxPosition + 1,
            ]);
            notify()->success('Coloana "' . $this->columnName . '" a fost creată cu succes în board!');
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
            notify()->error('Nu poți șterge coloana "' . $columnName . '" deoarece conține ' . $taskCount . ' task-uri. Mută sau șterge task-urile înainte!');
            return;
        }
        $column->delete();
        notify()->success('Coloana "' . $columnName . '" a fost ștearsă cu succes din board!');
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
        notify()->success('Numele coloanei a fost schimbat de la "' . $oldName . '" la "' . $this->editingColumnName . '"!');
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
            notify()->error('Utilizatorul "' . $user->first_name . ' ' . $user->last_name . '" (' . $user->email . ') este deja membru al acestui board!');
            return;
        }

        // Add member
        $roleLabel = $this->inviteRole === 'admin' ? 'Administrator' : ($this->inviteRole === 'viewer' ? 'Viewer' : 'Membru');
        $this->board->members()->attach($user->id, ['role' => $this->inviteRole]);
        notify()->success('Utilizatorul "' . $user->first_name . ' ' . $user->last_name . '" a fost adăugat ca ' . strtolower($roleLabel) . ' în board!');
        
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
                notify()->error('Nu poți schimba propriul rol deoarece ești ultimul administrator al board-ului!');
                $this->loadBoard();
                return;
            }
        }

        $oldRole = $this->board->members()->wherePivot('user_id', $userId)->first()->pivot->role ?? 'member';
        $this->board->members()->updateExistingPivot($userId, ['role' => $role]);
        notify()->success('Rolul utilizatorului "' . $user->first_name . ' ' . $user->last_name . '" a fost schimbat de la "' . ($roleLabels[$oldRole] ?? $oldRole) . '" la "' . ($roleLabels[$role] ?? $role) . '"!');
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
                notify()->error('Nu poți șterge propriul cont deoarece ești ultimul administrator al board-ului!');
                return;
            }
        }

        $this->board->members()->detach($userId);
        notify()->success('Utilizatorul "' . $userName . '" a fost eliminat din board!');
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
            notify()->success('Board-ul "' . $this->board->name . '" este acum public! Poți partaja link-ul pentru acces.');
        } else {
            notify()->info('Board-ul "' . $this->board->name . '" este acum privat. Doar membrii pot accesa board-ul.');
        }
    }

    public function copyPublicLink()
    {
        if (!$this->board->is_public || !$this->board->public_hash) {
            notify()->error('Board-ul nu este public sau nu are link generat. Activează opțiunea "Board public" mai întâi!');
            return;
        }
        
        $url = route('public.board', $this->board->public_hash);
        $this->dispatch('copy-to-clipboard', url: $url);
        notify()->success('Link-ul public al board-ului "' . $this->board->name . '" a fost copiat în clipboard! Poți să-l partajezi acum.');
    }

    public function deletePublicLink()
    {
        $this->board->is_public = false;
        $this->board->public_hash = null;
        $this->board->save();
        $this->loadBoard();
        notify()->info('Link-ul public al board-ului "' . $this->board->name . '" a fost șters. Board-ul este acum privat.');
    }

    public function updateLinkPermissions($permissions)
    {
        $this->linkPermissions = $permissions;
        // Note: Link permissions can be stored in a separate column if needed
        // For now, we'll use the default role when joining via link
    }

    // Background Modal Methods
    public function openBackgroundModal()
    {
        $this->selectedBackground = $this->board->background ?? '';
        $this->showBackgroundModal = true;
    }

    public function closeBackgroundModal()
    {
        $this->showBackgroundModal = false;
        $this->selectedBackground = '';
    }

    public function selectBackground($backgroundKey)
    {
        $this->selectedBackground = $backgroundKey;
    }

    public function saveBackground()
    {
        $this->board->background = $this->selectedBackground;
        $this->board->updated_by = Auth::id();
        $this->board->save();
        
        $this->loadBoard();
        $this->closeBackgroundModal();
        
        $backgroundName = $this->backgrounds[$this->selectedBackground]['name'] ?? 'implicit';
        notify()->success('Background-ul "' . $backgroundName . '" a fost aplicat cu succes!');
    }

    public function getBackgroundStyle()
    {
        if (!$this->board->background) {
            return '';
        }
        
        $backgrounds = $this->getBackgroundsProperty();
        if (isset($backgrounds[$this->board->background])) {
            $bg = $backgrounds[$this->board->background];
            return 'background: ' . $bg['value'] . ';';
        }
        
        return '';
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
        ])->layout('layouts.app', [
            'boardBackground' => $this->getBackgroundStyle()
        ]);
    }
}

