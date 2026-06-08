<?php

use CleaniqueCoders\ConfigSso\Tests\TestCase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

uses(TestCase::class)->in(__DIR__);

/**
 * Build a fake Socialite user as returned by an identity provider.
 */
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

/**
 * Make Socialite::driver($driver)->user() return the given fake user.
 */
function mockSocialiteDriver(string $driver, SocialiteUser $user): void
{
    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->andReturn($user);

    Socialite::shouldReceive('driver')->with($driver)->andReturn($provider);
}
