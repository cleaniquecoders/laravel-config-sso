# Laravel Config SSO

[![Latest Version on Packagist](https://img.shields.io/packagist/v/cleaniquecoders/laravel-config-sso.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/laravel-config-sso)
[![GitHub Tests Action Status](https://github.com/cleaniquecoders/laravel-config-sso/actions/workflows/run-tests.yml/badge.svg)](https://github.com/cleaniquecoders/laravel-config-sso/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://github.com/cleaniquecoders/laravel-config-sso/actions/workflows/fix-php-code-style-issues.yml/badge.svg)](https://github.com/cleaniquecoders/laravel-config-sso/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/cleaniquecoders/laravel-config-sso.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/laravel-config-sso)

Manage **single sign-on (SSO) identity providers from your database** — no `.env` juggling, no
redeploys. Providers (Google, GitHub, GitLab, Bitbucket, Keycloak, Azure AD) are stored with an
**encrypted client secret** and per-driver config, exposed through a [Laravel Socialite](https://laravel.com/docs/socialite)
redirect/callback flow that finds-or-creates and links the local user — plus an optional
**Livewire + Flux** admin screen to manage them.

- 🔐 DB-backed providers, encrypted `client_secret` and OAuth `token_data`
- 🧩 Core drivers via `laravel/socialite`; Keycloak / Azure via optional `socialiteproviders/*`
- 🖥️ Optional Livewire 4 + Flux admin UI (create / edit / enable / delete)
- 🧱 App-agnostic — configurable user model, table names, and authorization gate
- ✅ Laravel **12 & 13**, PHP **8.2+**

## Installation

```bash
composer require cleaniquecoders/laravel-config-sso
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="laravel-config-sso-migrations"
php artisan migrate
```

Publish the config:

```bash
php artisan vendor:publish --tag="laravel-config-sso-config"
```

Optionally publish the views to customize the admin UI:

```bash
php artisan vendor:publish --tag="laravel-config-sso-views"
```

### Optional drivers & UI

```bash
# Keycloak (OIDC) and/or Azure AD drivers
composer require socialiteproviders/keycloak socialiteproviders/microsoft-azure

# Admin UI (Livewire 4 + Flux)
composer require livewire/livewire livewire/flux
```

## Configuration

Set the host application's user model and the gate that protects the admin UI:

```php
// config/config-sso.php
'user_model' => App\Models\User::class,
'gate'       => 'admin.manage.sso',   // or null to disable the check
```

Everything else (drivers, table names, redirect routes, registration behaviour, route
prefixes/middleware) is documented inline in the published config file.

## Usage

### Render login buttons

```php
use CleaniqueCoders\ConfigSso\Facades\ConfigSso;

@foreach (ConfigSso::providers() as $provider)
    <a href="{{ route('sso.redirect', $provider->driver) }}">
        Continue with {{ $provider->name }}
    </a>
@endforeach
```

`sso.redirect` and `sso.callback` are registered automatically. On callback the package finds a
user by email (creating one when `registration.enabled` is true), links the identity, stores the
tokens, logs the user in, and redirects to the `redirect.home` route.

### Admin UI

When Livewire is installed, a full-page management screen is registered at
`config-sso.admin.prefix` (default `admin/settings/sso`). Or embed it anywhere:

```blade
<livewire:config-sso.admin />
```

### The `ConfigSso` facade

```php
ConfigSso::enabled();              // bool — master feature toggle
ConfigSso::providers();            // Collection<SsoProvider> — active, ordered
ConfigSso::drivers();              // ['google' => 'Google', ...]
ConfigSso::driverFields('keycloak'); // ['base_url', 'realms']
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Nasrul Hazim Bin Mohamad](https://github.com/nasrulhazim)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
