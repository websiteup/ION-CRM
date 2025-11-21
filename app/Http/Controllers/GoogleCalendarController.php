<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\GoogleCalendarService;
use Illuminate\Support\Facades\Log;

class GoogleCalendarController extends Controller
{
    protected $googleCalendarService;

    public function __construct(GoogleCalendarService $googleCalendarService)
    {
        $this->googleCalendarService = $googleCalendarService;
    }

    /**
     * Redirect to Google OAuth
     */
    public function connect()
    {
        try {
            $authUrl = $this->googleCalendarService->getAuthUrl();
            return redirect($authUrl);
        } catch (\Exception $e) {
            Log::error('Google Calendar connect error: ' . $e->getMessage());
            return redirect()->route('admin.calendar')
                ->with('error', 'Eroare la conectarea cu Google Calendar: ' . $e->getMessage());
        }
    }

    /**
     * Handle OAuth callback
     */
    public function callback(Request $request)
    {
        try {
            if ($request->has('error')) {
                return redirect()->route('admin.calendar')
                    ->with('error', 'Conectarea cu Google Calendar a fost anulată.');
            }

            $code = $request->get('code');
            if (!$code) {
                return redirect()->route('admin.calendar')
                    ->with('error', 'Cod de autorizare lipsă.');
            }

            $accessToken = $this->googleCalendarService->handleCallback($code);
            
            $user = Auth::user();
            $user->update([
                'google_calendar_token' => json_encode($accessToken),
                'google_calendar_id' => 'primary', // Default calendar
            ]);

            return redirect()->route('admin.calendar')
                ->with('success', 'Conectat cu succes la Google Calendar!');
        } catch (\Exception $e) {
            Log::error('Google Calendar callback error: ' . $e->getMessage());
            return redirect()->route('admin.calendar')
                ->with('error', 'Eroare la conectarea cu Google Calendar: ' . $e->getMessage());
        }
    }

    /**
     * Disconnect Google Calendar
     */
    public function disconnect(Request $request)
    {
        try {
            $this->googleCalendarService->disconnect(Auth::user());
            return redirect()->route('admin.calendar')
                ->with('success', 'Deconectat cu succes de la Google Calendar!');
        } catch (\Exception $e) {
            Log::error('Google Calendar disconnect error: ' . $e->getMessage());
            return redirect()->route('admin.calendar')
                ->with('error', 'Eroare la deconectarea de la Google Calendar.');
        }
    }

    /**
     * Sync all tasks to Google Calendar
     */
    public function syncAll()
    {
        try {
            $user = Auth::user();
            
            // Check if user is connected
            if (!$this->googleCalendarService->isConnected($user)) {
                return redirect()->route('admin.calendar')
                    ->with('error', 'Nu ești conectat la Google Calendar. Conectează-te mai întâi!');
            }
            
            $results = $this->googleCalendarService->syncAllTasks($user);
            
            $message = sprintf(
                'Sincronizare completă! %d task-uri sincronizate, %d eșuate, %d omise.',
                $results['synced'],
                $results['failed'],
                $results['skipped']
            );
            
            // Add warning if tasks were skipped
            if ($results['skipped'] > 0) {
                $message .= ' Task-urile omise nu au data scadenței (due_date) sau nu sunt atribuite unui utilizator.';
            }
            
            // Add error info if failed
            if ($results['failed'] > 0) {
                $message .= ' Verifică log-urile pentru detalii despre erorile de sincronizare.';
            }
            
            $messageType = ($results['failed'] > 0) ? 'error' : (($results['skipped'] > 0 && $results['synced'] == 0) ? 'warning' : 'success');
            
            return redirect()->route('admin.calendar')
                ->with($messageType, $message);
        } catch (\Exception $e) {
            Log::error('Google Calendar sync all error: ' . $e->getMessage());
            
            // Check if it's the API not enabled error
            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, 'Google Calendar API has not been used') || str_contains($errorMessage, 'SERVICE_DISABLED')) {
                $errorMessage = 'Google Calendar API nu este activat în Google Cloud Console. Activează-l la: https://console.cloud.google.com/apis/api/calendar-json.googleapis.com/overview?project=68594690830';
            }
            
            return redirect()->route('admin.calendar')
                ->with('error', 'Eroare la sincronizare: ' . $errorMessage);
        }
    }
}
