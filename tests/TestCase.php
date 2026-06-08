<?php

namespace CleaniqueCoders\ConfigSso\Tests;

use CleaniqueCoders\ConfigSso\ConfigSsoServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Laravel\Socialite\SocialiteServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Workbench\App\Models\User;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            SocialiteServiceProvider::class,
            LivewireServiceProvider::class,
            ConfigSsoServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Use the workbench user model and disable the gate by default.
        $app['config']->set('config-sso.user_model', User::class);
        $app['config']->set('config-sso.gate', null);
        $app['config']->set('auth.providers.users.model', User::class);
    }

    protected function defineRoutes($router): void
    {
        /** @var Router $router */
        // A realistic login page that uses the package's login-buttons component.
        $router->get('login', fn () => Blade::render('<x-config-sso::buttons />'))
            ->name('login');
        $router->get('dashboard', fn () => 'dashboard')->name('dashboard');
    }

    protected function defineDatabaseMigrations(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        foreach ([
            'create_sso_providers_table',
            'create_sso_user_providers_table',
        ] as $migration) {
            (include __DIR__."/../database/migrations/{$migration}.php.stub")->up();
        }
    }
}
