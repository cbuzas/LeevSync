<?php

use Illuminate\Support\Facades\Route;

Route::get('/', \App\Livewire\Homepage::class)->name('home');

Route::get('/profile', \App\Livewire\Profile::class)->name('profile');

Route::get('/tasks', \App\Livewire\Tasks::class)->name('tasks');

Route::get('/history', \App\Livewire\History::class)->name('history');

Route::get('/about', \App\Livewire\About::class)->name('about');
