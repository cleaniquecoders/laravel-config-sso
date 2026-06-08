# CLAUDE.md

Guidance for Claude Code (claude.ai/code) when working in this repository.

## Project Overview

**laravel-config-sso** (`cleaniquecoders/laravel-config-sso`) is a Laravel package that
manages **single sign-on (SSO) identity providers from the database** — with an encrypted
client secret, per-driver config, a Socialite-based redirect/callback auth flow, and an
optional **Livewire 4 + Flux** admin UI.

- **Package type**: Laravel package (Composer, built on `spatie/laravel-package-tools`)
- **Namespace**: `CleaniqueCoders\ConfigSso`
- **Origin**: extracted and decoupled from the g8stack-app "Admin > Settings > SSO" feature
- **Tracking issue**: cleaniquecoders/laravel-config-sso#1
- **Compatibility**: Laravel **12 & 13**, PHP **8.2+**, Livewire **3.5/4** + Flux **2**

### The core idea

OAuth/OIDC providers (Google, GitHub, GitLab, Bitbucket, Keycloak, Azure AD) are stored in
`sso_providers` with an encrypted `client_secret` and a JSON `config` for driver-specific
fields. On login the controller swaps the runtime `services.{driver}` config, runs Socialite,
then finds-or-creates the local user and links the identity in `sso_user_providers`
(encrypted `token_data`).

## Architecture

| Concern | Class |
|---|---|
| Provider model | `Models\SsoProvider` (`Concerns\HasUuid`, encrypted secret, `config` JSON, `active` scope, `toSocialiteConfig()`) |
| Identity link | `Models\SsoUserProvider` (encrypted `token_data`, `user()` → config user model) |
| Auth flow | `Http\Controllers\SsoController` — `redirect()`, `callback()` (find-or-create + link) |
| Public API | `ConfigSso` + `Facades\ConfigSso` — `enabled()`, `providers()`, `drivers()`, `driverFields()` |
| Admin UI | `Livewire\SsoProviders` + `resources/views/livewire/sso-providers.blade.php` (Flux, opt-in) |
| Wiring | `ConfigSsoServiceProvider` (config, views, routes, migrations, Socialite drivers, Livewire, install command) |
| Login buttons | `View\Components\Sso` + `resources/views/components/sso.blade.php` → `<x-sso />` (Flux-free, public-page safe) |

Routes: `sso.redirect` (`{provider}/redirect`), `sso.callback` (`{provider}/callback`), and an
optional full-page admin route. Livewire component alias: `config-sso.admin`.

## Drop-in onboarding (keep this easy)

The package is designed to install into any Laravel app in one step — do not regress this:

- **`php artisan config-sso:install`** (spatie `hasInstallCommand`) publishes config, publishes +
  runs migrations, and prints next steps. Tags: `laravel-config-sso-{config,migrations,views}`.
- **`<x-sso />`** — a class-based component (`View\Components\Sso` → `config-sso::components.sso`)
  registered as a short global tag in `packageBooted()` via `Blade::component(Sso::class, $tag)`,
  where `$tag = config('config-sso.component', 'sso')` (null/empty skips registration). Supports
  `<x-sso provider="github" />` (single) and `<x-sso only="github,keycloak" />` (subset, ordered).
  It is **pure Tailwind, no Flux**, because login pages are public and may not load Flux. Keep it
  dependency-free.
- The admin UI (`config-sso.admin`) is the only Flux-dependent surface and stays opt-in.

## Decoupling rules (DO NOT regress)

This package was extracted from an app. Keep it app-agnostic:

1. **No `App\Models\Base`** — models extend `Illuminate\Database\Eloquent\Model` and use the
   package's own `HasUuid` trait.
2. **No hardcoded `App\Models\User`** — resolve via `config('config-sso.user_model')`.
3. **No media-library coupling** — the original app uploaded provider logos via Spatie Media
   Library + `laravel-media-secure`. The package drops that; it uses a plain `icon` string
   (icon name or logo URL) + `show_logo`/`button_label`. Do **not** add a media dependency.
4. **Authorization** goes through `config('config-sso.gate')`, never a hardcoded gate string.
5. **Extra Socialite drivers are optional** — Keycloak/Azure listeners are registered only
   when their `socialiteproviders/*` class exists (`class_exists`). Core drivers
   (google/github/gitlab/bitbucket) need only `laravel/socialite`.
6. **Livewire + Flux UI is optional** — the component is registered only when Livewire is
   present; the controller + facade work with no UI installed.
7. **Role assignment is best-effort** — `registration.default_role` is applied only when the
   user model has `assignRole()` (spatie/permission). Never hard-require permission.

## Config surface (`config/config-sso.php`)

- `feature` — master toggle (hides login buttons via `ConfigSso::providers()`)
- `user_model` — FQCN of the host's authenticatable
- `tables` — `providers` / `user_providers` table names
- `gate` — ability string / callable guarding the admin UI (null = no check)
- `drivers` — `driver => ['label', 'fields' => [...]]`; `fields` are the extra JSON `config` keys
- `socialite_providers` — `driver => SocialiteProviders ...ExtendSocialite::class` (registered if installed)
- `registration` — `enabled` (self-register on first login) + `default_role`
- `redirect` — `home` / `login` route names
- `routes` — auth route enable/prefix/middleware/controller
- `admin` — UI enable/route/prefix/name/middleware/component

## Conventions

- **PHP 8.2+**, strict, typed. Laravel 12 & 13.
- **Format**: `composer format` (Laravel Pint).
- **Static analysis**: `composer analyse` (Larastan/PHPStan level 5, `src` only). The Livewire
  component is excluded — it needs the host's Flux peer dep and a package-namespaced view that
  Larastan can't resolve to a `view-string`; its logic is covered by feature tests.
- **Tests**: `composer test` (Pest + `orchestra/testbench`). Arch test forbids `dd`/`dump`/`ray`.

## Gotchas

> Encrypted columns (`client_secret`, `token_data`) must be **`text`** in the migration, never
> `json`/`jsonb` — the cast stores an opaque encrypted string and PostgreSQL rejects it as
> invalid JSON (SQLite passes silently). Same gotcha as the origin app.

> The migrations use **no foreign-key constraints** on `user_id` / `sso_provider_id` because
> the users/providers table names are host-configurable; they use plain indexes instead.

> Full-page admin route relies on Livewire's default layout (`components.layouts.app`). Hosts
> without that layout should embed `<livewire:config-sso.admin />` in their own page instead
> (set `config-sso.admin.route = false`).

## Testing strategy

Cover at minimum: uuid + secret encryption, `active` scope, `toSocialiteConfig()`, callback
create-and-link, callback existing-link (no duplicate), self-registration disabled, inactive
driver 404, the Livewire CRUD + gate, the `<x-sso />` component (all/`provider`/`only`), and the full
end-to-end journey (`tests/Feature/EndToEndTest.php`). Socialite is mocked via
`Socialite::shouldReceive` — the helpers `fakeSocialiteUser()` / `mockSocialiteDriver()` live in
`tests/Pest.php`.

## Workbench (manual end-to-end)

`workbench/` is a runnable Testbench app for clicking through the package; `testbench.yaml` wires
it up. `composer serve` (= `build` + `testbench serve`) creates the SQLite DB, runs migrations,
seeds demo data, and boots at `:8000`.

- `WorkbenchServiceProvider` sets `config-sso.user_model` and defines the `admin.manage.sso` gate.
- `workbench/routes/web.php`: `/` (login + buttons), `/dashboard`, `/dev/login` (auth as seeded
  admin), `/logout`. The admin UI route comes from the package itself (`config-sso.admin`).
- `workbench/resources/views/components/layouts/app.blade.php` is the layout the full-page admin
  Livewire component renders into — it pulls in `@livewireScripts` + `@fluxScripts`.
- Workbench migrations `require` the package `.php.stub` files so the schema never drifts.
- **Gotcha:** do NOT add a `users` migration in `workbench/database/migrations` — Testbench
  already loads its own `testbench_create_users_table` and a second one collides.
- **Gotcha:** the `testbench.yaml` `build:` list only understands recipe tokens
  (`create-sqlite-db`, `asset-publish`, …), NOT arbitrary `migrate:fresh --seed`. Migrate + seed
  are run explicitly in the composer `build` script, and seeding needs the fully-qualified
  `--class='Workbench\Database\Seeders\DatabaseSeeder'` (plain `--seed` looks for a root
  `DatabaseSeeder` that doesn't exist).

## Workflow conventions

- Every change traces to a GitHub issue; reference it in commits/PRs.
- Conventional commits; `CHANGELOG.md` is auto-generated by the release workflow.
- Git tags use **no `v` prefix** (e.g. `1.0.0`).
- Keep the package free of any consuming-app coupling (see Decoupling rules).
