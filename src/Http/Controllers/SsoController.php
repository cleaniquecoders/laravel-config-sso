<?php

namespace CleaniqueCoders\ConfigSso\Http\Controllers;

use CleaniqueCoders\ConfigSso\Events\UnverifiedAccountClaimed;
use CleaniqueCoders\ConfigSso\Models\SsoProvider;
use CleaniqueCoders\ConfigSso\Models\SsoUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Symfony\Component\HttpFoundation\Response;

class SsoController
{
    /**
     * Redirect the user to the identity provider's authorization page.
     */
    public function redirect(string $provider): RedirectResponse|Response
    {
        $ssoProvider = $this->resolveProvider($provider);

        Config::set("services.{$provider}", $ssoProvider->toSocialiteConfig());

        return $this->driver($provider, $ssoProvider)->redirect();
    }

    /**
     * Handle the identity provider callback: link or create the local user.
     */
    public function callback(string $provider): RedirectResponse
    {
        $ssoProvider = $this->resolveProvider($provider);

        Config::set("services.{$provider}", $ssoProvider->toSocialiteConfig());

        try {
            $socialiteUser = $this->driver($provider, $ssoProvider)->user();
        } catch (InvalidStateException $e) {
            // State mismatch: the session cookie did not survive the round trip to the
            // IdP and back (host/scheme mismatch, SESSION_DOMAIN, SameSite/Secure cookie).
            Log::error('SSO callback failed: invalid state', [
                'provider' => $provider,
                'session_has_state' => session()->has('state'),
                'returned_state' => request()->input('state'),
                'app_url' => config('app.url'),
                'redirect_url' => $ssoProvider->getRedirectUrl(),
                'request_url' => request()->fullUrl(),
            ]);

            return $this->toLogin('SSO session expired. Please try signing in again.');
        } catch (\Throwable $e) {
            Log::error('SSO callback failed', [
                'provider' => $provider,
                'exception' => $e::class,
                'error' => $e->getMessage(),
            ]);

            return $this->toLogin('SSO authentication failed. Please try again.');
        }

        $link = SsoUserProvider::query()
            ->where('sso_provider_id', $ssoProvider->getKey())
            ->where('provider_user_id', $socialiteUser->getId())
            ->first();

        if ($link) {
            $link->update([
                'email' => $socialiteUser->getEmail(),
                'token_data' => $this->tokenData($socialiteUser),
            ]);

            /** @var Authenticatable $user */
            $user = $link->user;
            Auth::login($user, remember: true);

            return $this->toHome();
        }

        $user = $this->findOrCreateUser($socialiteUser);

        if (! $user) {
            return $this->toLogin('No matching account found for this SSO identity.');
        }

        SsoUserProvider::create([
            'user_id' => $user->getKey(),
            'sso_provider_id' => $ssoProvider->getKey(),
            'provider_user_id' => $socialiteUser->getId(),
            'email' => $socialiteUser->getEmail(),
            'token_data' => $this->tokenData($socialiteUser),
        ]);

        Auth::login($user, remember: true);

        return $this->toHome();
    }

    protected function resolveProvider(string $provider): SsoProvider
    {
        return SsoProvider::query()->active()->where('driver', $provider)->firstOrFail();
    }

    protected function driver(string $provider, SsoProvider $ssoProvider): Provider
    {
        $driver = Socialite::driver($provider);
        $config = $ssoProvider->config ?? [];

        if ($provider === 'gitlab' && ! empty($config['instance_url']) && method_exists($driver, 'setHost')) {
            $driver->setHost($config['instance_url']);
        }

        return $driver;
    }

    /**
     * Find a local user by email, or create one when self-registration is enabled.
     */
    protected function findOrCreateUser(object $socialiteUser): ?Authenticatable
    {
        /** @var class-string<Model&Authenticatable> $model */
        $model = config('config-sso.user_model', 'App\\Models\\User');

        /** @var (Model&Authenticatable)|null $user */
        $user = $model::query()->where('email', $socialiteUser->getEmail())->first();

        if ($user) {
            if ($this->isUnverified($user)) {
                $this->claimUnverifiedAccount($user);
            }

            return $user;
        }

        if (! config('config-sso.registration.enabled', true)) {
            return null;
        }

        // forceFill, not create(): email_verified_at is (rightly) not mass-assignable on most
        // user models, and create() would silently drop it — leaving SSO users on verify-email.
        /** @var Model&Authenticatable $user */
        $user = new $model;
        $user->forceFill([
            'name' => $socialiteUser->getName() ?? $socialiteUser->getNickname() ?? 'SSO User',
            'email' => $socialiteUser->getEmail(),
            'password' => bcrypt(Str::random(32)),
            'email_verified_at' => now(),
        ])->save();

        $role = config('config-sso.registration.default_role');

        if ($role && method_exists($user, 'assignRole')) {
            $user->assignRole($role);
        }

        return $user;
    }

    /**
     * An account whose email was never verified is a claim, not an identity: whoever registered
     * it may not own the address (pre-registering a victim's email and keeping the password).
     */
    protected function isUnverified(Model $user): bool
    {
        if (method_exists($user, 'hasVerifiedEmail')) {
            return ! $user->hasVerifiedEmail();
        }

        return array_key_exists('email_verified_at', $user->getAttributes())
            && $user->getAttribute('email_verified_at') === null;
    }

    /**
     * The SSO sign-in proves the real owner controls the address, so revoke every credential
     * the registrant could hold before signing the owner in. App-specific credentials (2FA,
     * API tokens, passkeys) are revoked by listening for UnverifiedAccountClaimed.
     */
    protected function claimUnverifiedAccount(Model $user): void
    {
        $user->forceFill([
            'password' => bcrypt(Str::random(64)),
            'remember_token' => Str::random(60),
            'email_verified_at' => now(),
        ])->save();

        /** @var Model&Authenticatable $user */
        UnverifiedAccountClaimed::dispatch($user);
    }

    /**
     * @return array{token: string|null, refresh_token: string|null, expires_in: int|null}
     */
    protected function tokenData(object $socialiteUser): array
    {
        return [
            'token' => $socialiteUser->token ?? null,
            'refresh_token' => $socialiteUser->refreshToken ?? null,
            'expires_in' => $socialiteUser->expiresIn ?? null,
        ];
    }

    protected function toHome(): RedirectResponse
    {
        return redirect()->intended(route(config('config-sso.redirect.home', 'dashboard')));
    }

    protected function toLogin(string $status): RedirectResponse
    {
        return redirect()->route(config('config-sso.redirect.login', 'login'))->with('status', $status);
    }
}
