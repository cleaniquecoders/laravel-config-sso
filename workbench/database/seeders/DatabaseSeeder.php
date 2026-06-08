<?php

namespace Workbench\Database\Seeders;

use CleaniqueCoders\ConfigSso\Models\SsoProvider;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Workbench\App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // A couple of sample providers so the login page + admin UI have data.
        // Replace client_id/client_secret with real credentials to complete a
        // full OAuth round trip.
        SsoProvider::query()->updateOrCreate(
            ['driver' => 'github'],
            [
                'name' => 'GitHub',
                'client_id' => 'demo-client-id',
                'client_secret' => 'demo-client-secret',
                'is_active' => true,
                'sort_order' => 1,
                'button_label' => 'Continue with GitHub',
            ]
        );

        SsoProvider::query()->updateOrCreate(
            ['driver' => 'keycloak'],
            [
                'name' => 'Keycloak',
                'client_id' => 'demo-client-id',
                'client_secret' => 'demo-client-secret',
                'config' => ['base_url' => 'https://keycloak.example.com', 'realms' => 'demo'],
                'is_active' => false,
                'sort_order' => 2,
            ]
        );
    }
}
