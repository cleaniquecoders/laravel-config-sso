<?php

namespace CleaniqueCoders\ConfigSso\Models;

use CleaniqueCoders\ConfigSso\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property int $sso_provider_id
 * @property string $provider_user_id
 * @property string|null $email
 * @property array<string, mixed>|null $token_data
 */
class SsoUserProvider extends Model
{
    use HasUuid;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'token_data' => 'encrypted:array',
        ];
    }

    public function getTable(): string
    {
        return config('config-sso.tables.user_providers', 'sso_user_providers');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('config-sso.user_model', 'App\\Models\\User'), 'user_id');
    }

    public function ssoProvider(): BelongsTo
    {
        return $this->belongsTo(SsoProvider::class, 'sso_provider_id');
    }
}
