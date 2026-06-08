<?php

namespace CleaniqueCoders\ConfigSso\Models;

use CleaniqueCoders\ConfigSso\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $driver
 * @property string $client_id
 * @property string $client_secret
 * @property string|null $redirect_url
 * @property array<string, mixed>|null $config
 * @property bool $is_active
 * @property bool $show_logo
 * @property int $sort_order
 * @property string|null $icon
 * @property string|null $button_label
 */
class SsoProvider extends Model
{
    use HasUuid;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'client_secret' => 'encrypted',
            'config' => 'array',
            'is_active' => 'boolean',
            'show_logo' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function getTable(): string
    {
        return config('config-sso.tables.providers', 'sso_providers');
    }

    public function userProviders(): HasMany
    {
        return $this->hasMany(SsoUserProvider::class, 'sso_provider_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * The OAuth redirect (callback) URI registered with the identity provider.
     */
    public function getRedirectUrl(): string
    {
        return $this->redirect_url ?: route('sso.callback', $this->driver);
    }

    /**
     * Build the runtime Socialite "services.{driver}" configuration array.
     *
     * @return array<string, mixed>
     */
    public function toSocialiteConfig(): array
    {
        return array_merge([
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'redirect' => $this->getRedirectUrl(),
        ], $this->config ?? []);
    }
}
