# Testing

```bash
composer test            # Pest + Orchestra Testbench
composer test-coverage   # with coverage
```

## What's covered

The suite (Pest + Orchestra Testbench) covers, at minimum:

- UUID generation and `client_secret` encryption
- the `active` scope and `toSocialiteConfig()`
- callback **create-and-link** and callback **existing-link** (no duplicate)
- self-registration disabled, and an inactive/unknown driver returning 404
- the Livewire admin CRUD and the authorization gate
- the `<x-sso />` component (all / `provider` / `only`)
- the full **end-to-end sign-in journey** (`tests/Feature/EndToEndTest.php`)
- the full-page admin **route** render (`tests/Feature/AdminPageTest.php`) — exercises the page layout
- a static **Flux icon guard** (`tests/Feature/FluxIconTest.php`)

## Mocking Socialite

Socialite is mocked via `Socialite::shouldReceive`. Helpers `fakeSocialiteUser()` and
`mockSocialiteDriver()` live in `tests/Pest.php`.

```php
mockSocialiteDriver('github', fakeSocialiteUser([
    'id' => '123',
    'email' => 'octocat@example.com',
    'name' => 'The Octocat',
]));
```

## Testing notes

- **Render the admin route over HTTP**, not just `Livewire::test()`. The component sets its own page
  layout; only an HTTP render exercises it. This is what caught the
  "No hint path defined for [layouts]" regression — see
  [patterns](../01-architecture/02-patterns.md).
- **Flux icons render as no-ops in the Testbench harness**, so a valid icon and a broken one both
  return `200` in tests. `FluxIconTest` therefore checks icon names statically against the shipped Flux
  icon stubs rather than relying on HTTP assertions.
- The architecture test (`tests/ArchTest.php`) forbids `dd` / `dump` / `ray`.
