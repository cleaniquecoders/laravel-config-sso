<?php

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

/** @var array<string, mixed> $config */
$config = config('config-sso');

// Authentication routes: redirect to the IdP and handle its callback.
if ($config['routes']['enabled'] ?? true) {
    Route::group([
        'prefix' => $config['routes']['prefix'] ?? 'auth/sso',
        'middleware' => $config['routes']['middleware'] ?? ['web'],
    ], function () use ($config): void {
        $controller = $config['routes']['controller'];

        Route::get('{provider}/redirect', [$controller, 'redirect'])->name('sso.redirect');
        Route::get('{provider}/callback', [$controller, 'callback'])->name('sso.callback');
    });
}

// Optional full-page admin UI route (requires Livewire).
if (($config['admin']['enabled'] ?? true)
    && ($config['admin']['route'] ?? true)
    && class_exists(Livewire::class)
) {
    Route::get($config['admin']['prefix'] ?? 'admin/settings/sso', $config['admin']['component'])
        ->middleware($config['admin']['middleware'] ?? ['web', 'auth'])
        ->name($config['admin']['name'] ?? 'config-sso.admin');
}
