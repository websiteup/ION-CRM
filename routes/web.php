<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\ClientsComponent;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/clients', ClientsComponent::class)->name('clients.index');