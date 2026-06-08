<?php

use CleaniqueCoders\ConfigSso\Models\SsoProvider;
use CleaniqueCoders\ConfigSso\Models\SsoUserProvider;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Workbench\App\Models\User;

function fakeSocialiteUser(string $email = 'jane@example.com', string $id = 'remote-123'): SocialiteUser
{
    $user = (new SocialiteUser)->map([
        'id' => $id,
        'name' => 'Jane Doe',
        'nickname' => 'jane',
        'email' => $email,
    ]);

    $user->token = 'access-token';
    $user->refreshToken = 'refresh-token';
    $user->expiresIn = 3600;

    return $user;
}

function mockSocialiteDriver(string $driver, SocialiteUser $user): void
{
    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->andReturn($user);

    Socialite::shouldReceive('driver')->with($driver)->andReturn($provider);
}

beforeEach(function () {
    $this->provider = SsoProvider::create([
        'name' => 'GitHub',
        'driver' => 'github',
        'client_id' => 'cid',
        'client_secret' => 'secret',
        'is_active' => true,
    ]);
});

it('creates and links a new user on first sso login', function () {
    mockSocialiteDriver('github', fakeSocialiteUser());

    $this->get('/auth/sso/github/callback')
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
    $this->assertDatabaseHas('sso_user_providers', [
        'sso_provider_id' => $this->provider->id,
        'provider_user_id' => 'remote-123',
        'email' => 'jane@example.com',
    ]);
});

it('logs in the existing linked user without creating a duplicate', function () {
    $user = User::create([
        'name' => 'Jane',
        'email' => 'jane@example.com',
        'password' => bcrypt('x'),
    ]);

    SsoUserProvider::create([
        'user_id' => $user->id,
        'sso_provider_id' => $this->provider->id,
        'provider_user_id' => 'remote-123',
        'email' => 'jane@example.com',
        'token_data' => ['token' => 'old'],
    ]);

    mockSocialiteDriver('github', fakeSocialiteUser());

    $this->get('/auth/sso/github/callback')->assertRedirect(route('dashboard'));

    expect(User::count())->toBe(1);
    expect(SsoUserProvider::count())->toBe(1);
    $this->assertAuthenticatedAs($user);
});

it('does not create a user when self-registration is disabled', function () {
    config()->set('config-sso.registration.enabled', false);

    mockSocialiteDriver('github', fakeSocialiteUser('new@example.com', 'remote-999'));

    $this->get('/auth/sso/github/callback')->assertRedirect(route('login'));

    expect(User::where('email', 'new@example.com')->exists())->toBeFalse();
    $this->assertGuest();
});

it('404s for an unknown or inactive driver', function () {
    $this->provider->update(['is_active' => false]);

    mockSocialiteDriver('github', fakeSocialiteUser());

    $this->get('/auth/sso/github/callback')->assertNotFound();
});
