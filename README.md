# Laravel Config SSO

[![Latest Version on Packagist](https://img.shields.io/packagist/v/cleaniquecoders/laravel-config-sso.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/laravel-config-sso) [![GitHub Tests Action Status](https://github.com/cleaniquecoders/laravel-config-sso/actions/workflows/run-tests.yml/badge.svg)](https://github.com/cleaniquecoders/laravel-config-sso/actions?query=workflow%3Arun-tests+branch%3Amain) [![GitHub Code Style Action Status](https://github.com/cleaniquecoders/laravel-config-sso/actions/workflows/fix-php-code-style-issues.yml/badge.svg)](https://github.com/cleaniquecoders/laravel-config-sso/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain) [![Total Downloads](https://img.shields.io/packagist/dt/cleaniquecoders/laravel-config-sso.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/laravel-config-sso)

Manage **single sign-on (SSO) identity providers from your database** — no `.env` juggling, no
redeploys. Providers (Google, GitHub, GitLab, Bitbucket, Keycloak, Azure AD) are stored with an
**encrypted client secret**, exposed through a [Laravel Socialite](https://laravel.com/docs/socialite)
redirect/callback flow that finds-or-creates and links the local user — plus an optional
**Livewire + Flux** admin screen to manage them.

- 🔐 DB-backed providers, encrypted `client_secret` and OAuth `token_data`
- 🧩 Core drivers via `laravel/socialite`; Keycloak / Azure via optional `socialiteproviders/*`
- 🖥️ Optional Livewire 4 + Flux admin UI (create / edit / enable / delete)
- 🧱 App-agnostic — configurable user model, table names, and authorization gate
- ✅ Laravel **12 & 13**, PHP **8.2+**

## Quickstart

```bash
composer require cleaniquecoders/laravel-config-sso
php artisan config-sso:install   # publishes config + runs migrations
```

Point the package at your user model and admin gate in `config/config-sso.php`, then add the login
buttons to your login view:

```blade
<x-sso />
```

Add a provider (in the admin screen or by seeding `SsoProvider`) and it appears on your login page.

| Login page (`<x-sso />`) | Admin UI (Livewire + Flux) |
|---|---|
| ![Login buttons](assets/login-buttons.png) | ![Admin UI](assets/admin-ui.png) |

## Documentation

Full documentation lives in [`docs/`](docs/README.md):

- [Getting started](docs/02-development/01-getting-started.md) — install, configure, add buttons
- [Configuration reference](docs/03-configuration/01-config-reference.md) — every config key
- [API & usage](docs/04-api/README.md) — facade, routes, `<x-sso />`, admin UI
- [Architecture](docs/01-architecture/README.md) — design, data layer, decoupling rules
- [Local development & testing](docs/02-development/02-workflows.md) — the workbench app

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
