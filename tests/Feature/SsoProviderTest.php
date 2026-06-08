<?php

use CleaniqueCoders\ConfigSso\Models\SsoProvider;
use Illuminate\Support\Facades\DB;

it('auto-generates a uuid and encrypts the client secret', function () {
    $provider = SsoProvider::create([
        'name' => 'Company Google',
        'driver' => 'google',
        'client_id' => 'client-id',
        'client_secret' => 'super-secret',
        'is_active' => true,
    ]);

    expect($provider->uuid)->not->toBeEmpty();
    expect($provider->client_secret)->toBe('super-secret');

    // The raw column value must not be the plaintext (it is encrypted).
    $raw = DB::table('sso_providers')->where('id', $provider->id)->value('client_secret');
    expect($raw)->not->toBe('super-secret');
});

it('only returns active providers through the active scope', function () {
    SsoProvider::create(['name' => 'A', 'driver' => 'google', 'client_id' => 'a', 'client_secret' => 's', 'is_active' => true]);
    SsoProvider::create(['name' => 'B', 'driver' => 'github', 'client_id' => 'b', 'client_secret' => 's', 'is_active' => false]);

    expect(SsoProvider::active()->count())->toBe(1);
});

it('builds a socialite config with the callback redirect', function () {
    $provider = SsoProvider::create([
        'name' => 'KC',
        'driver' => 'keycloak',
        'client_id' => 'kc',
        'client_secret' => 'secret',
        'config' => ['base_url' => 'https://kc.test', 'realms' => 'demo'],
        'is_active' => true,
    ]);

    $config = $provider->toSocialiteConfig();

    expect($config)->toMatchArray([
        'client_id' => 'kc',
        'client_secret' => 'secret',
        'base_url' => 'https://kc.test',
        'realms' => 'demo',
    ]);
    expect($config['redirect'])->toContain('keycloak/callback');
});
