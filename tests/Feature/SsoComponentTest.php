<?php

use CleaniqueCoders\ConfigSso\Models\SsoProvider;
use Illuminate\Support\Facades\Blade;

beforeEach(function () {
    SsoProvider::create(['name' => 'GitHub', 'driver' => 'github', 'client_id' => 'c', 'client_secret' => 's', 'is_active' => true, 'sort_order' => 1]);
    SsoProvider::create(['name' => 'Keycloak', 'driver' => 'keycloak', 'client_id' => 'c', 'client_secret' => 's', 'is_active' => true, 'sort_order' => 2]);
    SsoProvider::create(['name' => 'GitLab', 'driver' => 'gitlab', 'client_id' => 'c', 'client_secret' => 's', 'is_active' => true, 'sort_order' => 3]);
    SsoProvider::create(['name' => 'Disabled', 'driver' => 'bitbucket', 'client_id' => 'c', 'client_secret' => 's', 'is_active' => false]);
});

it('renders all active providers with <x-sso />', function () {
    $html = Blade::render('<x-sso />');

    expect($html)
        ->toContain('Continue with GitHub')
        ->toContain('Continue with Keycloak')
        ->toContain('Continue with GitLab')
        ->not->toContain('Disabled');
});

it('renders a single provider with <x-sso provider="github" />', function () {
    $html = Blade::render('<x-sso provider="github" />');

    expect($html)
        ->toContain('Continue with GitHub')
        ->toContain(route('sso.redirect', 'github'))
        ->not->toContain('Keycloak')
        ->not->toContain('GitLab');
});

it('renders a subset with <x-sso only="github,keycloak" />', function () {
    $html = Blade::render('<x-sso only="github,keycloak" />');

    expect($html)
        ->toContain('Continue with GitHub')
        ->toContain('Continue with Keycloak')
        ->not->toContain('GitLab');
});

it('honors the order given in only=', function () {
    $html = Blade::render('<x-sso only="keycloak,github" />');

    expect(strpos($html, 'Keycloak'))->toBeLessThan(strpos($html, 'GitHub'));
});

it('merges extra classes onto the wrapper', function () {
    expect(Blade::render('<x-sso class="mt-4" />'))->toContain('mt-4');
});

it('renders nothing when no provider matches', function () {
    expect(trim(Blade::render('<x-sso provider="nope" />')))->toBe('');
});
