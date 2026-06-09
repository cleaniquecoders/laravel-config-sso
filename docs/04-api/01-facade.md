# The `ConfigSso` facade

`CleaniqueCoders\ConfigSso\Facades\ConfigSso` is the read API for building your own login UI without
the bundled component.

## Methods

### `enabled(): bool`

Whether the SSO feature is on (`config('config-sso.feature')`).

### `providers(): Collection<int, SsoProvider>`

Active providers ordered by `sort_order` then `name`. Returns an empty collection when the feature is
disabled.

```blade
@foreach (\CleaniqueCoders\ConfigSso\Facades\ConfigSso::providers() as $provider)
    <a href="{{ route('sso.redirect', $provider->driver) }}">
        Continue with {{ $provider->name }}
    </a>
@endforeach
```

### `drivers(): array<string, string>`

The configured drivers as a `[driver => label]` map (label falls back to `ucfirst(key)`).

### `driverFields(string $driver): array<int, string>`

The extra config field names a driver requires — e.g. `driverFields('keycloak')` returns
`['base_url', 'realms']`. Useful when building a custom provider form.

## Without the facade

The same data is on the model directly:

```php
use CleaniqueCoders\ConfigSso\Models\SsoProvider;

$providers = SsoProvider::query()->active()->orderBy('sort_order')->get();
```
