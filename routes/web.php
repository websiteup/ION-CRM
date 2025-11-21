<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Admin\ClientsComponent;
use App\Livewire\Admin\ServicesComponent;
use App\Livewire\Admin\DashboardComponent;
use App\Livewire\Admin\UsersComponent;
use App\Livewire\Admin\ProfileComponent;
use App\Livewire\Admin\SettingsComponent;
use App\Livewire\Admin\BoardsComponent;
use App\Livewire\Admin\BoardViewComponent;
use App\Livewire\Public\PublicBoardComponent;
use App\Livewire\Admin\ProjectsComponent;
use App\Livewire\Admin\ProjectViewComponent;
use App\Livewire\Admin\ProposalsComponent;
use App\Livewire\Admin\ProposalViewComponent;
use App\Livewire\Admin\ProposalTemplatesComponent;
use App\Livewire\Admin\SentEmailsComponent;
use App\Livewire\Admin\CalendarComponent;

Route::get('/', function () {
    return view('home');
});

Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'index'])->name('dashboard');
    
    // Profil personal - accesibil pentru toți utilizatorii autentificați
    Route::get('/admin/profile', ProfileComponent::class)->name('admin.profile');
    Route::post('/admin/profile/toggle-dark-mode', [App\Http\Controllers\DarkModeController::class, 'toggle'])->name('admin.profile.toggle-dark-mode');
    
    // Admin routes - doar pentru administratori
    Route::middleware(['admin'])->group(function () {
        Route::get('/admin/dashboard', DashboardComponent::class)->name('admin.dashboard');
        Route::get('/admin/clients', ClientsComponent::class)->name('admin.clients.index');
        Route::get('/admin/services', ServicesComponent::class)->name('admin.services.index');
        Route::get('/admin/users', UsersComponent::class)->name('admin.users.index');
        Route::get('/admin/settings', SettingsComponent::class)->name('admin.settings.index');
        Route::get('/admin/emails', SentEmailsComponent::class)->name('admin.emails.index');
        
        // Proposals routes
        Route::get('/admin/proposals', ProposalsComponent::class)->name('admin.proposals.index');
        Route::get('/admin/proposals/create', ProposalViewComponent::class)->name('admin.proposals.create');
        Route::get('/admin/proposals/templates', ProposalTemplatesComponent::class)->name('admin.proposals.templates');
        Route::get('/admin/proposals/{id}/pdf', [App\Http\Controllers\ProposalController::class, 'pdf'])->name('admin.proposals.pdf');
        Route::get('/admin/proposals/{id}', ProposalViewComponent::class)->name('admin.proposals.view');
    });

    // Board routes - pentru admin și manager
    Route::middleware(['manager'])->group(function () {
        Route::get('/admin/boards', BoardsComponent::class)->name('admin.boards.index');
        Route::get('/admin/boards/{id}', BoardViewComponent::class)->name('admin.boards.view');
        Route::get('/admin/projects', ProjectsComponent::class)->name('admin.projects.index');
        Route::get('/admin/projects/{id}', ProjectViewComponent::class)->name('admin.projects.view');
    });

    // Calendar routes - pentru toți utilizatorii autentificați
    Route::get('/admin/calendar', CalendarComponent::class)->name('admin.calendar');
    Route::get('/admin/calendar/connect', [App\Http\Controllers\GoogleCalendarController::class, 'connect'])->name('admin.calendar.connect');
    Route::get('/admin/calendar/callback', [App\Http\Controllers\GoogleCalendarController::class, 'callback'])->name('admin.calendar.callback');
    Route::post('/admin/calendar/disconnect', [App\Http\Controllers\GoogleCalendarController::class, 'disconnect'])->name('admin.calendar.disconnect');
    Route::post('/admin/calendar/sync', [App\Http\Controllers\GoogleCalendarController::class, 'syncAll'])->name('admin.calendar.sync');
});

// Public routes
Route::get('/public/board/{hash}', PublicBoardComponent::class)->name('public.board');