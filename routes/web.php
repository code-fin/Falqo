<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard', ['section' => 'overview'])->name('dashboard');
    Route::view('projects', 'dashboard', ['section' => 'projects'])->name('projects.index');
    Route::view('projects/{showId}', 'dashboard', ['section' => 'project'])->name('projects.show');
    Route::view('tickets', 'dashboard', ['section' => 'tickets'])->name('tickets.index');
    Route::view('tickets/{showId}', 'dashboard', ['section' => 'ticket'])->name('tickets.show');
    Route::view('tasks', 'dashboard', ['section' => 'tasks'])->name('tasks.index');
    Route::view('calendar', 'dashboard', ['section' => 'calendar'])->name('calendar.index');
    Route::view('time-tracker', 'dashboard', ['section' => 'time'])->name('time.index');
    Route::view('customers', 'dashboard', ['section' => 'customers'])->name('customers.index');
    Route::view('customers/{showId}', 'dashboard', ['section' => 'customer'])->name('customers.show');
});

require __DIR__.'/settings.php';
