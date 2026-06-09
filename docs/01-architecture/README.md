# Architecture

How `laravel-config-sso` is put together and the rules that keep it decoupled from any host app.

## Contents

| Page | Description |
|---|---|
| [01 · Overview](01-overview.md) | The core idea, component map, and request lifecycle |
| [02 · Patterns & decoupling rules](02-patterns.md) | The constraints that keep the package app-agnostic |
| [03 · Data layer](03-data-layer.md) | Models, migrations, encryption, and the no-foreign-key choice |

## Cross-references

- Configuration that drives all of this: [03 · Configuration](../03-configuration/README.md)
- The public surface built on top: [04 · API & Usage](../04-api/README.md)
