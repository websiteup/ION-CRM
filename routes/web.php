<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Admin\ClientsComponent;
use App\Livewire\Admin\ServicesComponent;
use App\Livewire\Admin\DashboardComponent;
use App\Livewire\Admin\UsersComponent;
use App\Livewire\Admin\ProfileComponent;
use App\Livewire\Admin\SettingsComponent;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'index'])->name('dashboard');
    
    // Profil personal - accesibil pentru toți utilizatorii autentificați
    Route::get('/admin/profile', ProfileComponent::class)->name('admin.profile');
    
    // Admin routes - doar pentru administratori
    Route::middleware(['admin'])->group(function () {
        Route::get('/admin/dashboard', DashboardComponent::class)->name('admin.dashboard');
        Route::get('/admin/clients', ClientsComponent::class)->name('admin.clients.index');
        Route::get('/admin/services', ServicesComponent::class)->name('admin.services.index');
        Route::get('/admin/users', UsersComponent::class)->name('admin.users.index');
        Route::get('/admin/settings', SettingsComponent::class)->name('admin.settings.index');
    });
});