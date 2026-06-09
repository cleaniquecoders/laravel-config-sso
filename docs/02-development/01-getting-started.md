# Getting started

## Requirements

- PHP **8.2+**
- Laravel **12 or 13**
- `laravel/socialite` (core drivers)
- Optional: Livewire **3.5 / 4** + Flux **2** (admin UI), `socialiteproviders/*` (Keycloak / Azure),
  `spatie/laravel-permission` (role assignment)

## Install

```bash
composer require cleaniquecoders/laravel-config-sso
```

Then run the install command — it publishes the config, publishes and runs the migrations, and
prints next steps:

```bash
php artisan config-sso:install
```

<details>
<summary>Manual install (without the command)</summary>

```bash
php artisan vendor:publish --tag="laravel-config-sso-config"
php artisan vendor:publish --tag="laravel-config-sso-migrations"
php artisan migrate
# optional, only to customise the admin UI markup:
php artisan vendor:publish --tag="laravel-config-sso-views"
```

</details>

## Configure

In `config/config-sso.php` set at least:

- `user_model` — your authenticatable, e.g. `App\Models\User::class`.
- `gate` — the ability that guards the admin UI (or `null` to disable the check).

See [Configuration](../03-configuration/README.md) for the full surface.

## Add login buttons

Drop the component into your login view. It is pure Tailwind (no Flux), so it is safe on a public page:

```blade
<x-sso />                              {{-- all active providers --}}
<x-sso provider="github" />           {{-- a single provider --}}
<x-sso only="github,keycloak" />      {{-- a subset, in the given order --}}
<x-sso class="mt-4" />                 {{-- merge your own classes --}}
```

The `sso.redirect` and `sso.callback` routes are registered automatically. On callback the package
finds a user by email (creating one when `registration.enabled` is true), links the identity, stores
the tokens, logs the user in, and redirects to the `redirect.home` route.

## Add the admin UI (optional)

With Livewire installed, a full-page management screen is registered at `config-sso.admin.prefix`
(default `admin/settings/sso`). Or embed it anywhere:

```blade
<livewire:config-sso.admin />
```

Details and layout overrides: [Admin UI](../04-api/04-admin-ui.md).

## Add a provider

Create a row through the admin UI, or directly:

```php
use CleaniqueCoders\ConfigSso\Models\SsoProvider;

SsoProvider::create([
    'name' => 'GitHub',
    'driver' => 'github',
    'client_id' => 'xxxx',
    'client_secret' => 'yyyy',     // encrypted automatically
    'is_active' => true,
]);
```

The OAuth callback URL to register at the provider is `…/auth/sso/github/callback`
(`route('sso.callback', 'github')`).
