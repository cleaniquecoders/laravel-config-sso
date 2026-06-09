# Auth routes & flow

Registered automatically (when `config-sso.routes.enabled` is true) under the
`config-sso.routes.prefix` (default `auth/sso`).

| Name | Method & path | Action |
|---|---|---|
| `sso.redirect` | `GET {prefix}/{provider}/redirect` | `SsoController@redirect` |
| `sso.callback` | `GET {prefix}/{provider}/callback` | `SsoController@callback` |

`{provider}` is the driver key (`github`, `keycloak`, …). Only **active** providers resolve; an
unknown or inactive driver returns `404`.

## `redirect()`

1. Resolves the active `SsoProvider` for the driver.
2. Swaps the runtime `services.{driver}` config with the row's `toSocialiteConfig()`.
3. For `gitlab`, applies `instance_url` via `setHost()` when present.
4. Returns Socialite's redirect to the IdP.

## `callback()`

1. Resolves the provider and swaps the runtime config again.
2. Fetches the Socialite user. Failures are logged and redirect to the login route with a status
   message:
   - `InvalidStateException` → "SSO session expired. Please try signing in again."
   - any other `Throwable` → "SSO authentication failed. Please try again."
3. **Existing link** (`sso_user_providers` row matching provider + `provider_user_id`): refresh
   `email` + `token_data`, log the user in, redirect home.
4. **No link**: find a user by email, or create one when `registration.enabled` is true (assigning
   `registration.default_role` if the model supports `assignRole()`). Then create the link, log in,
   redirect home. If self-registration is disabled and no user matches, redirect to login with
   "No matching account found for this SSO identity."

`token_data` stores `{ token, refresh_token, expires_in }`, encrypted at rest.

## Redirect targets

- Success → `redirect()->intended(route(config('config-sso.redirect.home')))` (default `dashboard`).
- Failure → `route(config('config-sso.redirect.login'))` (default `login`) with a `status` flash.

## The callback URL to register at the IdP

`route('sso.callback', $driver)` — e.g. `https://your-app.test/auth/sso/github/callback`. Override per
provider with the `redirect_url` column when needed.

## Troubleshooting `InvalidStateException`

The session cookie didn't survive the round trip. Check `APP_URL`, `SESSION_DOMAIN`, and SameSite /
Secure cookie settings, and that you return to the same host/scheme you left from. The controller logs
the relevant values at `error` level when this happens.
