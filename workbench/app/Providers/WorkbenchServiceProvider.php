<?php

namespace Workbench\App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Workbench\App\Models\User;

class WorkbenchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Point the package at the workbench user model.
        config()->set('config-sso.user_model', User::class);
        config()->set('auth.providers.users.model', User::class);

        // Demonstrate the host-layout override: render the admin UI inside the
        // workbench's own layout (with nav) instead of the package's fallback.
        config()->set('config-sso.admin.layout', 'components.layouts.app');
    }

    public function boot(): void
    {
        // The admin UI is guarded by config-sso.gate (default 'admin.manage.sso').
        // A real app maps this to its own permission system; for the demo we allow
        // only the seeded admin account.
        Gate::define('admin.manage.sso', fn (User $user): bool => $user->email === 'admin@example.com');

        // Path to the compiled Tailwind + Flux stylesheet (built via `npm run build:css`).
        View::share('configSsoDemoCss', dirname(__DIR__, 2).'/public/build/app.css');
    }
}
