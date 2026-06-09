# The `<x-sso />` component

`View\Components\Sso` renders provider login buttons. It is **pure Tailwind, no Flux**, so it is safe
on a public login page that may not load Flux.

## Usage

```blade
<x-sso />                              {{-- all active providers --}}
<x-sso provider="github" />           {{-- a single provider --}}
<x-sso only="github,keycloak" />      {{-- a subset, in the given order --}}
<x-sso class="mt-4" />                 {{-- merge your own classes onto the wrapper --}}
```

## Attributes

| Attribute | Effect |
|---|---|
| _(none)_ | All active providers, ordered by `sort_order` then `name` |
| `provider="github"` | Just that one driver |
| `only="github,keycloak"` | Only those drivers, **in the order given** |
| `class="…"` | Merged onto the wrapper element |

When no provider matches, the component renders nothing.

## The tag name

The tag is registered from `config('config-sso.component')` (default `sso`). Change it to avoid a
clash, or set it to `null` to skip registering a global tag and reference the class component
directly.

## Customising the markup

Publish the views and edit `resources/views/vendor/config-sso/components/sso.blade.php`:

```bash
php artisan vendor:publish --tag="laravel-config-sso-views"
```

Or build the buttons yourself with the [facade](01-facade.md).
