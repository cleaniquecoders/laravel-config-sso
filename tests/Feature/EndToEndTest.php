<?php

use CleaniqueCoders\ConfigSso\Models\SsoProvider;
use CleaniqueCoders\ConfigSso\Models\SsoUserProvider;
use Workbench\App\Models\User;

// End-to-end: the full journey a real app exercises — render the login button,
// follow the provider callback, land authenticated on the dashboard. This mirrors
// what you can click through manually via `composer serve` (the workbench app).

it('walks the full sso sign-in journey for a new user', function () {
    SsoProvider::create([
        'name' => 'GitHub',
        'driver' => 'github',
        'client_id' => 'cid',
        'client_secret' => 'secret',
        'is_active' => true,
    ]);

    // 1. The login page renders the SSO button linking to the redirect route.
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Continue with GitHub')
        ->assertSee(route('sso.redirect', 'github'));

    // 2. The provider sends the user back to the callback (Socialite mocked).
    mockSocialiteDriver('github', fakeSocialiteUser());

    $this->get('/auth/sso/github/callback')
        ->assertRedirect(route('dashboard'));

    // 3. The user now exists, is linked, and is authenticated.
    $this->assertAuthenticated();
    expect(User::where('email', 'jane@example.com')->exists())->toBeTrue();
    expect(SsoUserProvider::where('provider_user_id', 'remote-123')->exists())->toBeTrue();

    // 4. The dashboard is reachable as the signed-in user.
    $this->get(route('dashboard'))->assertOk()->assertSee('dashboard');
});

it('signs a returning user straight back in without duplicating records', function () {
    $provider = SsoProvider::create([
        'name' => 'GitHub', 'driver' => 'github', 'client_id' => 'cid', 'client_secret' => 'secret', 'is_active' => true,
    ]);

    $user = User::create(['name' => 'Jane', 'email' => 'jane@example.com', 'password' => bcrypt('x')]);
    SsoUserProvider::create([
        'user_id' => $user->id,
        'sso_provider_id' => $provider->id,
        'provider_user_id' => 'remote-123',
        'email' => 'jane@example.com',
        'token_data' => ['token' => 'old'],
    ]);

    mockSocialiteDriver('github', fakeSocialiteUser());

    $this->get('/auth/sso/github/callback')->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
    expect(User::count())->toBe(1);
    expect(SsoUserProvider::count())->toBe(1);
});
