# Config reference

Each section of `config/config-sso.php`, with defaults and environment variables.

## `feature`

Master switch for the SSO feature. When `false`, `ConfigSso::providers()` returns an empty collection
so login buttons disappear.

- Default: `true` · Env: `CONFIG_SSO_FEATURE`

## `user_model`

The application's authenticatable model. Used to find an existing user by email and to create one on
first SSO login. Keep it app-agnostic — the package never references `App\Models\User` directly.

- Default: `App\Models\User` · Env: `CONFIG_SSO_USER_MODEL`

## `tables`

Table names, so the package can coexist with host naming conventions.

```php
'tables' => [
    'providers' => 'sso_providers',
    'user_providers' => 'sso_user_providers',
],
```

## `component`

The Blade tag for the login-buttons component (default `<x-sso />`). Set to `null` to skip registering
a global tag — e.g. to avoid a name clash.

- Default: `'sso'`

## `gate`

Ability string (or callable) checked before the admin UI loads. Set to `null` to disable the check
(not recommended in production).

- Default: `'admin.manage.sso'` · Env: `CONFIG_SSO_GATE`

## `drivers`

The providers offered in the admin UI. `fields` lists the extra, driver-specific keys stored in the
JSON `config` column.

```php
'drivers' => [
    'google'    => ['label' => 'Google'],
    'github'    => ['label' => 'GitHub'],
    'gitlab'    => ['label' => 'GitLab',   'fields' => ['instance_url']],
    'bitbucket' => ['label' => 'Bitbucket'],
    'keycloak'  => ['label' => 'Keycloak', 'fields' => ['base_url', 'realms']],
    'azure'     => ['label' => 'Azure AD', 'fields' => ['tenant']],
],
```

## `socialite_providers`

Non-core Socialite drivers, registered via `socialiteproviders/*` listeners. Each is wired only when
its listener class is installed (`class_exists`).

```php
'socialite_providers' => [
    'keycloak' => SocialiteProviders\Keycloak\KeycloakExtendSocialite::class,
    'azure'    => SocialiteProviders\Azure\AzureExtendSocialite::class,
],
```

Install the matching package to enable one, e.g. `composer require socialiteproviders/keycloak`.

## `registration`

User provisioning on first login.

```php
'registration' => [
    'enabled' => true,        // create a local user when no account matches the SSO email
    'default_role' => 'user', // assigned via spatie/permission if installed; null to skip
],
```

Role assignment is best-effort — applied only when the user model has `assignRole()`.

## `redirect`

Route names used after a successful login and on failure.

```php
'redirect' => [
    'home'  => 'dashboard',
    'login' => 'login',
],
```

## `routes`

Registers `sso.redirect` (`{provider}/redirect`) and `sso.callback` (`{provider}/callback`).

```php
'routes' => [
    'enabled' => true,
    'prefix' => 'auth/sso',
    'middleware' => ['web'],
    'controller' => CleaniqueCoders\ConfigSso\Http\Controllers\SsoController::class,
],
```

## `admin`

The Livewire + Flux admin UI, registered as the Livewire component `config-sso.admin`. When `route` is
`true` a full-page GET route is registered; otherwise embed the component with
`<livewire:config-sso.admin />`.

```php
'admin' => [
    'enabled' => true,
    'route' => true,
    'prefix' => 'admin/settings/sso',
    'name' => 'config-sso.admin',
    'middleware' => ['web', 'auth'],
    'component' => CleaniqueCoders\ConfigSso\Livewire\SsoProviders::class,
    'layout' => 'config-sso::layouts.app',
],
```

- `layout` env: `CONFIG_SSO_ADMIN_LAYOUT`. Point it at your own layout (e.g.
  `components.layouts.app`) to embed the admin screen in your app chrome, or set `null` to use
  Livewire's configured default. **Do not** rely on the implicit default — see
  [patterns](../01-architecture/02-patterns.md#full-page-admin-layout-do-not-regress).
