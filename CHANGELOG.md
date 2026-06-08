# Changelog

All notable changes to `laravel-config-sso` will be documented in this file.

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
