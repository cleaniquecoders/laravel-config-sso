<?php

namespace CleaniqueCoders\ConfigSso\Livewire;

use CleaniqueCoders\ConfigSso\Models\SsoProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class SsoProviders extends Component
{
    public bool $showModal = false;

    public bool $isEditing = false;

    public ?string $editingId = null;

    public string $name = '';

    public string $driver = '';

    public string $client_id = '';

    public string $client_secret = '';

    public string $redirect_url = '';

    public string $icon = '';

    public bool $show_logo = true;

    public string $button_label = '';

    public int $sort_order = 0;

    public bool $is_active = false;

    /** @var array<string, string> Driver-specific config values keyed by field name. */
    public array $driverConfig = [];

    public bool $showDeleteModal = false;

    public ?string $deletingId = null;

    public function mount(): void
    {
        $gate = config('config-sso.gate');

        if ($gate) {
            abort_unless(Gate::allows($gate), 403);
        }
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEditModal(string $uuid): void
    {
        $provider = SsoProvider::where('uuid', $uuid)->firstOrFail();

        $this->editingId = $provider->uuid;
        $this->isEditing = true;
        $this->name = $provider->name;
        $this->driver = $provider->driver;
        $this->client_id = $provider->client_id;
        $this->client_secret = '';
        $this->redirect_url = $provider->redirect_url ?? '';
        $this->icon = $provider->icon ?? '';
        $this->button_label = $provider->button_label ?? '';
        $this->sort_order = $provider->sort_order;
        $this->is_active = $provider->is_active;
        $this->show_logo = $provider->show_logo;
        $this->driverConfig = (array) ($provider->config ?? []);

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate($this->rules());

        $config = $this->buildConfig();

        $data = [
            'name' => $this->name,
            'driver' => $this->driver,
            'client_id' => $this->client_id,
            'redirect_url' => $this->redirect_url ?: null,
            'config' => $config ?: null,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'icon' => $this->icon ?: null,
            'show_logo' => $this->show_logo,
            'button_label' => $this->button_label ?: null,
        ];

        if ($this->client_secret !== '') {
            $data['client_secret'] = $this->client_secret;
        }

        if ($this->isEditing) {
            $provider = SsoProvider::where('uuid', $this->editingId)->firstOrFail();
            $data['updated_by'] = auth()->id();
            $provider->update($data);
            $message = 'SSO provider updated successfully.';
        } else {
            $data['client_secret'] = $this->client_secret;
            $data['created_by'] = auth()->id();
            SsoProvider::create($data);
            $message = 'SSO provider created successfully.';
        }

        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('toast', type: 'success', message: $message);
    }

    public function toggleActive(string $uuid): void
    {
        $provider = SsoProvider::where('uuid', $uuid)->firstOrFail();
        $provider->update(['is_active' => ! $provider->is_active]);

        $status = $provider->is_active ? 'enabled' : 'disabled';
        $this->dispatch('toast', type: 'success', message: "SSO provider {$status}.");
    }

    public function confirmDelete(string $uuid): void
    {
        $this->deletingId = $uuid;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if (! $this->deletingId) {
            return;
        }

        SsoProvider::where('uuid', $this->deletingId)->firstOrFail()->delete();

        $this->showDeleteModal = false;
        $this->deletingId = null;
        $this->dispatch('toast', type: 'success', message: 'SSO provider deleted.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        $drivers = implode(',', array_keys((array) config('config-sso.drivers', [])));

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'driver' => ['required', 'string', 'in:'.$drivers],
            'client_id' => ['required', 'string', 'max:255'],
            'client_secret' => [$this->isEditing ? 'nullable' : 'required', 'string'],
            'redirect_url' => ['nullable', 'url', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'button_label' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['integer', 'min:0'],
        ];

        foreach ($this->driverFields() as $field) {
            $rules["driverConfig.{$field}"] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }

    /**
     * @return array<int, string>
     */
    public function driverFields(): array
    {
        if ($this->driver === '') {
            return [];
        }

        return (array) config("config-sso.drivers.{$this->driver}.fields", []);
    }

    /**
     * @return array<string, string>
     */
    protected function buildConfig(): array
    {
        $config = [];

        foreach ($this->driverFields() as $field) {
            $value = $this->driverConfig[$field] ?? '';

            if ($value !== '') {
                $config[$field] = $value;
            }
        }

        return $config;
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingId', 'name', 'driver', 'client_id', 'client_secret',
            'redirect_url', 'icon', 'show_logo', 'button_label', 'sort_order',
            'is_active', 'driverConfig',
        ]);
        $this->show_logo = true;
        $this->resetValidation();
    }

    public function render(): View
    {
        $view = view('config-sso::livewire.sso-providers', [
            'providers' => SsoProvider::orderBy('sort_order')->orderBy('name')->get(),
            'driverOptions' => collect(config('config-sso.drivers', []))
                ->map(fn (array $d, string $k): string => $d['label'] ?? ucfirst($k))
                ->all(),
        ]);

        // Render full-page into the configured layout (default: the package's own),
        // so the route never depends on the host's Livewire `component_layout`.
        $layout = config('config-sso.admin.layout', 'config-sso::layouts.app');

        if (is_string($layout) && $layout !== '') {
            $view->layout($layout);
        }

        return $view;
    }
}
