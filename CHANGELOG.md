# Changelog

All notable changes to `laravel-config-sso` will be documented in this file.

## 1.0.1 - 2026-06-08

### Fixed

- **Admin route 500 — `No hint path defined for [layouts]`.** The full-page admin component (`/admin/settings/sso`) inherited Livewire's implicit page layout (`config('livewire.component_layout')`, which defaults to the unregistered namespaced view `layouts::app`), so the route crashed in any app that doesn't define it.

### What changed

- `SsoProviders::render()` now sets its own layout via `$view->layout(...)`, defaulting to the package's self-contained **`config-sso::layouts.app`** — the admin route no longer depends on the host's Livewire layout.
- New **`config-sso.admin.layout`** (env `CONFIG_SSO_ADMIN_LAYOUT`) — point it at your app layout (e.g. `components.layouts.app`) to embed the admin UI in your own chrome, or set `null` for Livewire's default.
- Ships a fallback layout (`resources/views/layouts/app.blade.php`) with Tailwind + Livewire + Flux assets so the route works out of the box.
- Added `tests/Feature/AdminPageTest.php` — renders the **route over HTTP** (which `Livewire::test()` does not), so this regression is now covered. 28 tests passing.

Upgrade from 1.0.0 is drop-in — no API or migration changes.

**Full Changelog**: https://github.com/cleaniquecoders/laravel-config-sso/compare/1.0.0...1.0.1

## 1.0.0 - 2026-06-08

Initial release of **laravel-config-sso** — database-backed SSO provider management for Laravel (Socialite) with a Livewire + Flux admin UI. Laravel 12 & 13, PHP 8.2+.

### Highlights

- **DB-backed providers** — `SsoProvider` / `SsoUserProvider` with encrypted `client_secret` and OAuth `token_data`.
  
- **Auth flow** — `SsoController` redirect/callback: find-or-create user, link identity, store tokens.
  
- **`<x-sso />` login buttons** — pure-Tailwind, Flux-free component (safe on public login pages):
  
  - `<x-sso />` — all active providers
  - `<x-sso provider="github" />` — a single provider
  - `<x-sso only="github,keycloak" />` — an ordered subset
  - Tag configurable via `config-sso.component`.
  
- **Admin UI** — optional Livewire 4 + Flux screen (`config-sso.admin`), gate-protected.
  
- **Drivers** — Google/GitHub/GitLab/Bitbucket via `laravel/socialite`; Keycloak/Azure via optional `socialiteproviders/*` (auto-wired when installed).
  
- **Drop-in onboarding** — `php artisan config-sso:install` (publish config + migrations, run migrations).
  
- **App-agnostic** — configurable user model, table names, and authorization gate.
  
- **Tested** — 25 Pest tests incl. a full end-to-end sign-in journey; runnable Testbench workbench (`composer serve`); PHPStan level 5; Pint.
  

### Install

```bash
composer require cleaniquecoders/laravel-config-sso
php artisan config-sso:install


```
**Full Changelog**: https://github.com/cleaniquecoders/laravel-config-sso/commits/1.0.0
