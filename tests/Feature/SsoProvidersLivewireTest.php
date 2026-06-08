<?php

use CleaniqueCoders\ConfigSso\Livewire\SsoProviders;
use CleaniqueCoders\ConfigSso\Models\SsoProvider;
use Livewire\Livewire;

it('creates a provider through the livewire component', function () {
    Livewire::test(SsoProviders::class)
        ->call('openCreateModal')
        ->set('name', 'Company Google')
        ->set('driver', 'google')
        ->set('client_id', 'client-id')
        ->set('client_secret', 'client-secret')
        ->set('is_active', true)
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('toast');

    expect(SsoProvider::count())->toBe(1);
    expect(SsoProvider::first())
        ->name->toBe('Company Google')
        ->is_active->toBeTrue();
});

it('validates required fields', function () {
    Livewire::test(SsoProviders::class)
        ->call('save')
        ->assertHasErrors(['name', 'driver', 'client_id', 'client_secret']);
});

it('requires the driver-specific config fields to be present in the form', function () {
    Livewire::test(SsoProviders::class)
        ->set('driver', 'keycloak')
        ->assertSet('driver', 'keycloak')
        ->call('save')
        ->assertHasErrors(['name', 'client_id']);
});

it('toggles a provider active state', function () {
    $provider = SsoProvider::create([
        'name' => 'GitHub', 'driver' => 'github', 'client_id' => 'c', 'client_secret' => 's', 'is_active' => false,
    ]);

    Livewire::test(SsoProviders::class)
        ->call('toggleActive', $provider->uuid)
        ->assertDispatched('toast');

    expect($provider->fresh()->is_active)->toBeTrue();
});

it('deletes a provider', function () {
    $provider = SsoProvider::create([
        'name' => 'GitHub', 'driver' => 'github', 'client_id' => 'c', 'client_secret' => 's',
    ]);

    Livewire::test(SsoProviders::class)
        ->call('confirmDelete', $provider->uuid)
        ->assertSet('showDeleteModal', true)
        ->call('delete')
        ->assertDispatched('toast');

    expect(SsoProvider::count())->toBe(0);
});

it('enforces the authorization gate when configured', function () {
    config()->set('config-sso.gate', 'config-sso.manage');

    Livewire::test(SsoProviders::class)->assertForbidden();
});
