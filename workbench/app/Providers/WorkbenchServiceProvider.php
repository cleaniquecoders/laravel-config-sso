<?php

namespace Workbench\App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Workbench\App\Models\User;

class WorkbenchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Point the package at the workbench user model.
        config()->set('config-sso.user_model', User::class);
        config()->set('auth.providers.users.model', User::class);
    }

    public function boot(): void
    {
        // The admin UI is guarded by config-sso.gate (default 'admin.manage.sso').
        // A real app maps this to its own permission system; for the demo we allow
        // only the seeded admin account.
        Gate::define('admin.manage.sso', fn (User $user): bool => $user->email === 'admin@example.com');
    }
}
