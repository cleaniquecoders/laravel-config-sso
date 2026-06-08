<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Workbench\App\Models\User;

// Public login page — shows the package's <x-config-sso::buttons /> component.
Route::get('/', fn () => view('login'))->name('login');

// Authenticated landing page.
Route::get('/dashboard', fn () => view('dashboard'))
    ->middleware('auth')
    ->name('dashboard');

// Dev shortcut: log in as the seeded admin so you can reach the admin UI without
// a real OAuth round trip. (Demo only — never ship a route like this.)
Route::get('/dev/login', function () {
    Auth::login(User::where('email', 'admin@example.com')->firstOrFail());

    return redirect()->route('config-sso.admin');
})->name('dev.login');

Route::post('/logout', function () {
    Auth::logout();

    return redirect()->route('login');
})->name('logout');
