<?php

namespace CleaniqueCoders\ConfigSso;

use CleaniqueCoders\ConfigSso\Livewire\SsoProviders;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use SocialiteProviders\Manager\SocialiteWasCalled;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ConfigSsoServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-config-sso')
            ->hasConfigFile('config-sso')
            ->hasViews('config-sso')
            ->hasRoute('web')
            ->hasMigrations([
                'create_sso_providers_table',
                'create_sso_user_providers_table',
            ])
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command
                    ->startWith(function (InstallCommand $command): void {
                        $command->info('Installing Laravel Config SSO…');
                    })
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('cleaniquecoders/laravel-config-sso')
                    ->endWith(function (InstallCommand $command): void {
                        $command->info('Next steps:');
                        $command->line('  1. Set config-sso.user_model + config-sso.gate in config/config-sso.php');
                        $command->line('  2. Add <x-config-sso::buttons /> to your login view');
                        $command->line('  3. (optional) composer require livewire/livewire livewire/flux for the admin UI');
                    });
            });
    }

    public function packageBooted(): void
    {
        $this->registerSocialiteProviders();
        $this->registerLivewireComponents();
    }

    /**
     * Wire up non-core Socialite drivers (Keycloak, Azure, …) via their
     * socialiteproviders/* listeners — but only when both the SocialiteProviders
     * manager and the specific listener class are installed.
     */
    protected function registerSocialiteProviders(): void
    {
        if (! class_exists(SocialiteWasCalled::class)) {
            return;
        }

        foreach ((array) config('config-sso.socialite_providers', []) as $listener) {
            if (is_string($listener) && class_exists($listener)) {
                Event::listen(SocialiteWasCalled::class, $listener);
            }
        }
    }

    /**
     * Register the optional Livewire + Flux admin UI component.
     */
    protected function registerLivewireComponents(): void
    {
        if (class_exists(Livewire::class) && config('config-sso.admin.enabled', true)) {
            Livewire::component('config-sso.admin', SsoProviders::class);
        }
    }
}
