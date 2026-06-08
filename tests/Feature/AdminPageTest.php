<?php

use CleaniqueCoders\ConfigSso\Models\SsoProvider;
use Workbench\App\Models\User;

// Renders the full-page admin ROUTE (not just the Livewire component) so the page
// layout is exercised end-to-end — this is what caught the "No hint path defined
// for [layouts]" regression where the route inherited Livewire's default layout.

it('renders the full-page admin route through the package layout', function () {
    $user = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => bcrypt('x')]);

    SsoProvider::create([
        'name' => 'GitHub', 'driver' => 'github', 'client_id' => 'c', 'client_secret' => 's', 'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get('/admin/settings/sso')
        ->assertOk()
        ->assertSee('SSO Providers')
        ->assertSee('GitHub');
});

it('renders the admin route through a host-provided layout override', function () {
    config()->set('config-sso.admin.layout', 'config-sso::layouts.app');

    $user = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => bcrypt('x')]);

    $this->actingAs($user)
        ->get('/admin/settings/sso')
        ->assertOk()
        ->assertSee('SSO Providers');
});

it('blocks guests from the admin route', function () {
    $this->get('/admin/settings/sso')->assertRedirect();
});
