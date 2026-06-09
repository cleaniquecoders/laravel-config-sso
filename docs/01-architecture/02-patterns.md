# Patterns & decoupling rules

The package was extracted from an application, so a set of rules keep it app-agnostic. Treat these
as invariants — regressing one re-couples the package to a specific host.

## Decoupling rules

1. **No `App\Models\Base`.** Models extend `Illuminate\Database\Eloquent\Model` and use the
   package's own `HasUuid` trait.
2. **No hardcoded `App\Models\User`.** The authenticatable is resolved via
   `config('config-sso.user_model')`.
3. **No media-library coupling.** Provider branding is a plain `icon` string (icon name or logo URL)
   plus `show_logo` / `button_label`. There is no Spatie Media Library dependency.
4. **Authorization goes through `config('config-sso.gate')`** — never a hardcoded gate string.
5. **Extra Socialite drivers are optional.** Keycloak / Azure listeners are registered only when the
   matching `socialiteproviders/*` class exists (`class_exists`). Core drivers
   (google / github / gitlab / bitbucket) need only `laravel/socialite`.
6. **Livewire + Flux UI is optional.** The component is registered only when Livewire is present; the
   controller and facade work with no UI installed.
7. **Role assignment is best-effort.** `registration.default_role` is applied only when the user model
   has `assignRole()` (spatie/permission). The package never hard-requires permission.

## Conditional registration

`ConfigSsoServiceProvider::packageBooted()` wires three optional surfaces defensively:

- **Socialite extensions** — `Event::listen(SocialiteWasCalled::class, ...)` only when both the
  manager and the specific listener class are installed.
- **Livewire admin** — `Livewire::component('config-sso.admin', SsoProviders::class)` only when
  Livewire is installed and `config-sso.admin.enabled` is true.
- **`<x-sso />` Blade tag** — `Blade::component(Sso::class, $tag)` where
  `$tag = config('config-sso.component', 'sso')`; a `null`/empty tag skips registration.

## Full-page admin layout (do not regress)

The `SsoProviders` component **must set its own layout** in `render()` via
`$view->layout(config('config-sso.admin.layout'))`, defaulting to the package's own
`config-sso::layouts.app`. Never rely on Livewire's implicit page layout:
`config('livewire.component_layout')` defaults to `layouts::app`, an unregistered namespaced view, so
the route 500s with **"No hint path defined for [layouts]"** in any app that doesn't define it (the
exact bug shipped in 1.0.0, fixed in 1.0.1).

Hosts override `config-sso.admin.layout` to their own layout (e.g. `components.layouts.app`), or embed
`<livewire:config-sso.admin />` and set `config-sso.admin.route = false`. This is covered by
`tests/Feature/AdminPageTest.php`, which renders the route over HTTP — `Livewire::test()` alone does
**not** exercise the page layout.

## Flux icons are Heroicons

The admin UI uses Flux, and Flux ships **Heroicons** — not Lucide. Use Heroicon names
(`ellipsis-horizontal`, `trash`, `eye-slash`), never Lucide names (`ellipsis`, `trash-2`, `eye-off`).
An unknown name throws `Flux component [icon.x] does not exist` under a full boot. Because Flux renders
icons as no-ops in the Testbench harness, an HTTP test can't catch this — `tests/Feature/FluxIconTest.php`
statically guards every icon referenced in the admin view against the shipped Flux icon stubs.

## Quality gates

- **Format**: `composer format` (Laravel Pint).
- **Static analysis**: `composer analyse` (Larastan / PHPStan level 5, `src` only). The Livewire
  component is excluded — it needs the host's Flux peer dependency and a package-namespaced view that
  Larastan can't resolve to a `view-string`; its logic is covered by feature tests instead.
- **Architecture test**: `tests/ArchTest.php` forbids `dd` / `dump` / `ray`.
