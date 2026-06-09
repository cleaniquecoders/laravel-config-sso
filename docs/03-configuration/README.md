# Configuration

Everything in `config/config-sso.php`. Publish it with `php artisan config-sso:install` (or
`vendor:publish --tag="laravel-config-sso-config"`).

## Contents

| Page | Description |
|---|---|
| [01 · Config reference](01-config-reference.md) | Every key, its default, and its env var |

## Quick map

| Key | Purpose |
|---|---|
| `feature` | Master toggle — hides login buttons when false |
| `user_model` | FQCN of the host's authenticatable |
| `tables` | `providers` / `user_providers` table names |
| `component` | The `<x-sso />` Blade tag (or `null` to skip) |
| `gate` | Ability/callable guarding the admin UI (`null` = no check) |
| `drivers` | `driver => ['label', 'fields']` map |
| `socialite_providers` | `driver => ...ExtendSocialite::class` (registered if installed) |
| `registration` | Self-register on first login + default role |
| `redirect` | `home` / `login` route names |
| `routes` | Auth route enable / prefix / middleware / controller |
| `admin` | Admin UI enable / route / prefix / name / middleware / component / layout |
