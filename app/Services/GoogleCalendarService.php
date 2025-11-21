<?php

namespace App\Services;

use App\Models\User;
use App\Models\Task;
use App\Models\Setting;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventDateTime;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GoogleCalendarService
{
    protected $client;
    protected $calendarService;

    /**
     * Initialize Google Client
     */
    public function __construct()
    {
        $this->client = new Google_Client();
        
        // Get settings from database, fallback to config
        $settings = Setting::getSettings();
        $clientId = $settings->google_calendar_client_id ?? config('services.google.client_id');
        $clientSecret = $settings->google_calendar_client_secret ?? config('services.google.client_secret');
        
        // Get redirect URI from settings or config, always replace localhost with 127.0.0.1
        $redirectUri = $settings->google_calendar_redirect_uri ?? config('services.google.redirect_uri');
        
        if (!$redirectUri) {
            $redirectUri = config('app.url') . '/admin/calendar/callback';
        }
        
        // Always replace localhost with 127.0.0.1 to match Google Cloud Console
        $redirectUri = str_replace('localhost', '127.0.0.1', $redirectUri);
        
        $this->client->setClientId($clientId);
        $this->client->setClientSecret($clientSecret);
        $this->client->setRedirectUri($redirectUri);
        $this->client->setScopes([
            Google_Service_Calendar::CALENDAR,
            Google_Service_Calendar::CALENDAR_EVENTS,
        ]);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');
    }

    /**
     * Get authorization URL
     */
    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    /**
     * Set access token for user
     */
    public function setAccessToken(User $user): void
    {
        if ($user->google_calendar_token) {
            $this->client->setAccessToken(json_decode($user->google_calendar_token, true));
            
            // Refresh token if expired
            if ($this->client->isAccessTokenExpired()) {
                $this->refreshToken($user);
            }
        }
    }

    /**
     * Refresh access token
     */
    protected function refreshToken(User $user): void
    {
        try {
            $refreshToken = json_decode($user->google_calendar_token, true)['refresh_token'] ?? null;
            
            if ($refreshToken) {
                $this->client->refreshToken($refreshToken);
                $newToken = $this->client->getAccessToken();
                
                $user->update([
                    'google_calendar_token' => json_encode($newToken)
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to refresh Google Calendar token: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle OAuth callback
     */
    public function handleCallback(string $code): array
    {
        try {
            $accessToken = $this->client->fetchAccessTokenWithAuthCode($code);
            
            if (array_key_exists('error', $accessToken)) {
                throw new \Exception($accessToken['error_description'] ?? 'Error fetching access token');
            }
            
            return $accessToken;
        } catch (\Exception $e) {
            Log::error('Google Calendar OAuth callback error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Initialize calendar service for user
     */
    protected function initCalendarService(User $user): void
    {
        $this->setAccessToken($user);
        $this->calendarService = new Google_Service_Calendar($this->client);
    }

    /**
     * Sync task to Google Calendar
     */
    public function syncTaskToCalendar(Task $task, User $user): ?string
    {
        try {
            $this->initCalendarService($user);
            
            if (!$task->due_date) {
                Log::info("Task {$task->id} skipped: no due_date");
                return null; // Skip tasks without due date
            }
            
            if (!$task->assigned_to) {
                Log::info("Task {$task->id} skipped: no assigned_to");
                return null; // Skip tasks without assigned user
            }

            $calendarId = $user->google_calendar_id ?? 'primary';
            
            // Check if task already has a Google Calendar event ID
            $eventId = $task->google_calendar_event_id;
            
            $event = new Google_Service_Calendar_Event([
                'summary' => $task->title,
                'description' => strip_tags($task->description_html ?? ''),
                'start' => new Google_Service_Calendar_EventDateTime([
                    'date' => $task->due_date->format('Y-m-d'),
                    'timeZone' => config('app.timezone', 'Europe/Bucharest'),
                ]),
                'end' => new Google_Service_Calendar_EventDateTime([
                    'date' => $task->due_date->format('Y-m-d'),
                    'timeZone' => config('app.timezone', 'Europe/Bucharest'),
                ]),
            ]);

            // Set color based on priority
            $colorId = $this->getPriorityColorId($task->priority);
            if ($colorId) {
                $event->setColorId($colorId);
            }

            if ($eventId) {
                // Update existing event
                $updatedEvent = $this->calendarService->events->update($calendarId, $eventId, $event);
                return $updatedEvent->getId();
            } else {
                // Create new event
                $createdEvent = $this->calendarService->events->insert($calendarId, $event);
                return $createdEvent->getId();
            }
        } catch (\Exception $e) {
            Log::error('Failed to sync task to Google Calendar: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete task from Google Calendar
     */
    public function deleteTaskFromCalendar(Task $task, User $user): bool
    {
        try {
            $this->initCalendarService($user);
            
            if (!$task->google_calendar_event_id) {
                return true; // Nothing to delete
            }

            $calendarId = $user->google_calendar_id ?? 'primary';
            $this->calendarService->events->delete($calendarId, $task->google_calendar_event_id);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete task from Google Calendar: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Sync all user tasks to Google Calendar
     */
    public function syncAllTasks(User $user): array
    {
        $results = [
            'synced' => 0,
            'failed' => 0,
            'skipped' => 0,
        ];

        try {
            $this->initCalendarService($user);
            
            // Get all tasks assigned to user or created by user
            $tasks = Task::where(function($query) use ($user) {
                $query->where('assigned_to', $user->id)
                      ->orWhere('created_by', $user->id);
            })
            ->whereNotNull('due_date')
            ->get();

            foreach ($tasks as $task) {
                try {
                    // Check if task has required fields
                    if (!$task->due_date) {
                        Log::info("Task {$task->id} ({$task->title}) skipped: no due_date");
                        $results['skipped']++;
                        continue;
                    }
                    
                    if (!$task->assigned_to) {
                        Log::info("Task {$task->id} ({$task->title}) skipped: no assigned_to");
                        $results['skipped']++;
                        continue;
                    }
                    
                    $eventId = $this->syncTaskToCalendar($task, $user);
                    if ($eventId) {
                        $task->update(['google_calendar_event_id' => $eventId]);
                        $results['synced']++;
                        Log::info("Task {$task->id} ({$task->title}) synced successfully to Google Calendar");
                    } else {
                        Log::warning("Task {$task->id} ({$task->title}) sync returned null");
                        $results['skipped']++;
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to sync task {$task->id} ({$task->title}): " . $e->getMessage());
                    $results['failed']++;
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to sync all tasks: ' . $e->getMessage());
        }

        return $results;
    }

    /**
     * Get color ID based on priority
     */
    protected function getPriorityColorId(string $priority): ?string
    {
        return match($priority) {
            'urgent' => '11', // Red
            'high' => '6',    // Orange
            'medium' => '5',  // Yellow
            'low' => '10',    // Green
            default => null,
        };
    }

    /**
     * Check if user is connected to Google Calendar
     */
    public function isConnected(User $user): bool
    {
        return !empty($user->google_calendar_token);
    }

    /**
     * Get events from Google Calendar
     */
    public function getCalendarEvents(User $user, $timeMin = null, $timeMax = null): array
    {
        try {
            $this->initCalendarService($user);
            
            $calendarId = $user->google_calendar_id ?? 'primary';
            
            // Set default time range if not provided (current month ± 1 month for better coverage)
            if (!$timeMin) {
                $timeMin = Carbon::now()->subMonth()->startOfMonth()->toRfc3339String();
            }
            if (!$timeMax) {
                $timeMax = Carbon::now()->addMonth()->endOfMonth()->toRfc3339String();
            }
            
            $optParams = [
                'timeMin' => $timeMin,
                'timeMax' => $timeMax,
                'maxResults' => 2500,
                'singleEvents' => true,
                'orderBy' => 'startTime',
            ];
            
            $events = $this->calendarService->events->listEvents($calendarId, $optParams);
            
            $calendarEvents = [];
            foreach ($events->getItems() as $event) {
                $start = $event->getStart();
                $end = $event->getEnd();
                
                $startDate = $start->getDateTime() ?: $start->getDate();
                $endDate = $end->getDateTime() ?: $end->getDate();
                
                $calendarEvents[] = [
                    'id' => 'gc_' . $event->getId(),
                    'title' => $event->getSummary() ?? '(Fără titlu)',
                    'start' => $startDate,
                    'end' => $endDate,
                    'color' => $this->getGoogleCalendarEventColor($event->getColorId()),
                    'url' => $event->getHtmlLink(),
                    'extendedProps' => [
                        'source' => 'google_calendar',
                        'description' => $event->getDescription() ?? '',
                        'location' => $event->getLocation() ?? '',
                        'organizer' => $event->getOrganizer() ? $event->getOrganizer()->getEmail() : '',
                    ],
                ];
            }
            
            return $calendarEvents;
        } catch (\Exception $e) {
            Log::error('Failed to get Google Calendar events: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get color for Google Calendar event based on colorId
     */
    protected function getGoogleCalendarEventColor($colorId): string
    {
        // Google Calendar color IDs mapping
        $colors = [
            '1' => '#a4bdfc', // Lavender
            '2' => '#7ae7bf', // Sage
            '3' => '#dbadff', // Grape
            '4' => '#ff887c', // Flamingo
            '5' => '#fbd75b', // Banana
            '6' => '#ffb878', // Tangerine
            '7' => '#46d6db', // Peacock
            '8' => '#e1e1e1', // Graphite
            '9' => '#5484ed', // Blueberry
            '10' => '#51b749', // Basil
            '11' => '#dc2127', // Tomato
        ];
        
        return $colors[$colorId] ?? '#3788d8'; // Default Google Calendar blue
    }

    /**
     * Disconnect Google Calendar
     */
    public function disconnect(User $user): void
    {
        $user->update([
            'google_calendar_token' => null,
            'google_calendar_id' => null,
        ]);
    }
}

