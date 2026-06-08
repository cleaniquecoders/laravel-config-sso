<?php

use CleaniqueCoders\ConfigSso\Models\SsoProvider;
use Illuminate\Support\Facades\Blade;

it('renders active providers as login buttons', function () {
    SsoProvider::create([
        'name' => 'GitHub', 'driver' => 'github', 'client_id' => 'c', 'client_secret' => 's', 'is_active' => true,
    ]);
    SsoProvider::create([
        'name' => 'Hidden', 'driver' => 'gitlab', 'client_id' => 'h', 'client_secret' => 's', 'is_active' => false,
    ]);

    $html = Blade::render('<x-config-sso::buttons />');

    expect($html)
        ->toContain('Continue with GitHub')
        ->toContain(route('sso.redirect', 'github'))
        ->not->toContain('Hidden');
});

it('uses the custom button label when set', function () {
    SsoProvider::create([
        'name' => 'Company SSO', 'driver' => 'keycloak', 'client_id' => 'c', 'client_secret' => 's',
        'is_active' => true, 'button_label' => 'Sign in with Company SSO',
    ]);

    expect(Blade::render('<x-config-sso::buttons />'))->toContain('Sign in with Company SSO');
});

it('renders nothing when there are no active providers', function () {
    expect(trim(Blade::render('<x-config-sso::buttons />')))->toBe('');
});
