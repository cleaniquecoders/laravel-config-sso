<?php

use CleaniqueCoders\ConfigSso\Facades\ConfigSso;
use CleaniqueCoders\ConfigSso\Models\SsoProvider;

it('returns active providers ordered for the login screen', function () {
    SsoProvider::create(['name' => 'Zeta', 'driver' => 'github', 'client_id' => 'z', 'client_secret' => 's', 'is_active' => true, 'sort_order' => 2]);
    SsoProvider::create(['name' => 'Alpha', 'driver' => 'google', 'client_id' => 'a', 'client_secret' => 's', 'is_active' => true, 'sort_order' => 1]);
    SsoProvider::create(['name' => 'Hidden', 'driver' => 'gitlab', 'client_id' => 'h', 'client_secret' => 's', 'is_active' => false]);

    $providers = ConfigSso::providers();

    expect($providers)->toHaveCount(2);
    expect($providers->first()->name)->toBe('Alpha');
});

it('returns an empty collection when the feature is disabled', function () {
    config()->set('config-sso.feature', false);

    SsoProvider::create(['name' => 'A', 'driver' => 'google', 'client_id' => 'a', 'client_secret' => 's', 'is_active' => true]);

    expect(ConfigSso::providers())->toHaveCount(0);
});

it('exposes the configured driver labels and fields', function () {
    expect(ConfigSso::drivers())->toHaveKey('keycloak');
    expect(ConfigSso::driverFields('keycloak'))->toBe(['base_url', 'realms']);
});
