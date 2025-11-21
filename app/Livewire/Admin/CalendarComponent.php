<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Task;
use App\Services\GoogleCalendarService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CalendarComponent extends Component
{
    public $isConnected = false;
    public $view = 'month'; // month, week, day
    protected $googleCalendarService;

    public function mount(GoogleCalendarService $googleCalendarService)
    {
        $this->googleCalendarService = $googleCalendarService;
        $this->isConnected = $googleCalendarService->isConnected(Auth::user());
    }

    public function getTasksProperty()
    {
        $user = Auth::user();
        $events = [];
        
        // Get tasks assigned to user or created by user
        $tasks = Task::with(['project', 'assignedUser', 'column'])
            ->where(function($query) use ($user) {
                $query->where('assigned_to', $user->id)
                      ->orWhere('created_by', $user->id);
            })
            ->whereNotNull('due_date')
            ->get()
            ->map(function($task) {
                return [
                    'id' => 'task_' . $task->id,
                    'title' => $task->title,
                    'start' => $task->due_date->format('Y-m-d'),
                    'end' => $task->due_date->format('Y-m-d'),
                    'color' => $this->getPriorityColor($task->priority),
                    'url' => route('admin.boards.view', $task->column->board_id ?? '#'),
                    'extendedProps' => [
                        'source' => 'task',
                        'priority' => $task->priority,
                        'project' => $task->project->name ?? 'N/A',
                        'assigned_to' => $task->assignedUser->name ?? 'N/A',
                        'description' => strip_tags($task->description_html ?? ''),
                    ],
                ];
            });

        $events = array_merge($events, $tasks->toArray());
        
        // Get Google Calendar events if connected
        if ($this->isConnected) {
            try {
                $googleEvents = $this->googleCalendarService->getCalendarEvents($user);
                $events = array_merge($events, $googleEvents);
            } catch (\Exception $e) {
                \Log::error('Failed to load Google Calendar events: ' . $e->getMessage());
            }
        }

        return $events;
    }

    protected function getPriorityColor($priority)
    {
        return match($priority) {
            'urgent' => '#dc3545', // Red
            'high' => '#fd7e14',    // Orange
            'medium' => '#ffc107',  // Yellow
            'low' => '#198754',     // Green
            default => '#6c757d',   // Gray
        };
    }

    public function getGoogleCalendarEvents($start = null, $end = null)
    {
        if (!$this->isConnected) {
            return [];
        }

        try {
            $user = Auth::user();
            $timeMin = $start ? Carbon::parse($start)->toRfc3339String() : null;
            $timeMax = $end ? Carbon::parse($end)->toRfc3339String() : null;
            
            return $this->googleCalendarService->getCalendarEvents($user, $timeMin, $timeMax);
        } catch (\Exception $e) {
            \Log::error('Failed to load Google Calendar events: ' . $e->getMessage());
            return [];
        }
    }

    public function render()
    {
        return view('livewire.admin.calendar-component', [
            'tasks' => $this->tasks,
        ])->layout('layouts.app');
    }
}
