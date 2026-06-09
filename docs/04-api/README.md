# API & Usage

The public surface: the facade, the auth routes, the login-buttons component, and the admin UI.

## Contents

| Page | Description |
|---|---|
| [01 · `ConfigSso` facade](01-facade.md) | `enabled()`, `providers()`, `drivers()`, `driverFields()` |
| [02 · Auth routes & flow](02-routes.md) | `sso.redirect` / `sso.callback` and the callback behaviour |
| [03 · `<x-sso />` component](03-blade-component.md) | Rendering login buttons |
| [04 · Admin UI](04-admin-ui.md) | The Livewire + Flux management screen and layout overrides |

## Cross-references

- Config behind these: [03 · Configuration](../03-configuration/README.md)
- Data model: [Data layer](../01-architecture/03-data-layer.md)
