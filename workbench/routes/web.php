<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Workbench\App\Models\User;

// Serve the compiled Tailwind + Flux stylesheet for the demo (built via npm run build:css).
Route::get('/_config-sso/app.css', function () {
    $path = dirname(__DIR__).'/public/build/app.css';

    abort_unless(is_file($path), 404);

    return response()->file($path, ['Content-Type' => 'text/css']);
})->name('config-sso.demo.css');

// Public login page — shows the package's <x-sso /> component.
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
