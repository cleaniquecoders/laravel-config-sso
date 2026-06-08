<?php

namespace CleaniqueCoders\ConfigSso\View\Components;

use CleaniqueCoders\ConfigSso\Facades\ConfigSso;
use CleaniqueCoders\ConfigSso\Models\SsoProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Sso extends Component
{
    public function __construct(
        public ?string $provider = null,
        public ?string $only = null,
    ) {}

    /**
     * The providers to render, narrowed by the `provider` or `only` attribute.
     *
     * @return Collection<int, SsoProvider>
     */
    public function providers(): Collection
    {
        $providers = ConfigSso::providers();

        // <x-sso provider="github" /> — a single provider.
        if ($this->provider !== null && $this->provider !== '') {
            return $providers->where('driver', $this->provider)->values();
        }

        // <x-sso only="github,keycloak" /> — a subset, in the requested order.
        if ($this->only !== null && $this->only !== '') {
            $drivers = collect(explode(',', $this->only))
                ->map(fn (string $driver): string => trim($driver))
                ->filter()
                ->all();

            return $providers
                ->whereIn('driver', $drivers)
                ->sortBy(fn (SsoProvider $provider): int => array_search($provider->driver, $drivers, true))
                ->values();
        }

        // <x-sso /> — every active provider.
        return $providers;
    }

    public function render(): View
    {
        return view('config-sso::components.sso', [
            'providers' => $this->providers(),
        ]);
    }
}
