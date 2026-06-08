<?php

namespace CleaniqueCoders\ConfigSso;

use CleaniqueCoders\ConfigSso\Models\SsoProvider;
use Illuminate\Support\Collection;

class ConfigSso
{
    /**
     * Whether the SSO feature is enabled.
     */
    public function enabled(): bool
    {
        return (bool) config('config-sso.feature', true);
    }

    /**
     * Active providers ordered for rendering login buttons.
     *
     * @return Collection<int, SsoProvider>
     */
    public function providers(): Collection
    {
        if (! $this->enabled()) {
            return new Collection;
        }

        return SsoProvider::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * The configured drivers as a [driver => label] map.
     *
     * @return array<string, string>
     */
    public function drivers(): array
    {
        return collect(config('config-sso.drivers', []))
            ->map(fn (array $driver, string $key): string => $driver['label'] ?? ucfirst($key))
            ->all();
    }

    /**
     * The extra config field names required by a given driver (e.g. keycloak => base_url, realms).
     *
     * @return array<int, string>
     */
    public function driverFields(string $driver): array
    {
        return (array) config("config-sso.drivers.{$driver}.fields", []);
    }
}
