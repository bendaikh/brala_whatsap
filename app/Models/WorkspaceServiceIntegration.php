<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class WorkspaceServiceIntegration extends Model
{
    use HasFactory;

    public const PROVIDER_CUSTOM = 'custom';
    public const PROVIDER_ALFA_COD = 'alfa_cod';
    public const PROVIDER_SURESHIP = 'sureship';

    public const PROVIDERS = [
        self::PROVIDER_SURESHIP => 'Sureship',
        self::PROVIDER_ALFA_COD => 'Alfa COD',
        self::PROVIDER_CUSTOM => 'Custom API',
    ];

    public const TYPES = [
        'delivery' => 'Delivery Company',
        'payment' => 'Payment Provider',
        'logistics' => 'Logistics',
        'other' => 'Other Service',
    ];

    protected $fillable = [
        'workspace_id',
        'user_id',
        'name',
        'type',
        'provider',
        'api_url',
        'api_key_encrypted',
        'is_enabled',
        'notes',
        'settings',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'settings' => 'array',
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function stores()
    {
        return $this->hasMany(Store::class, 'service_integration_id');
    }

    public function getApiKeyAttribute(): ?string
    {
        if (empty($this->api_key_encrypted)) {
            return null;
        }

        try {
            return Crypt::decryptString($this->api_key_encrypted);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function hasApiKey(): bool
    {
        return !empty($this->api_key_encrypted);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst((string) $this->type);
    }

    public function getProviderLabelAttribute(): string
    {
        return self::PROVIDERS[$this->provider] ?? ucfirst((string) $this->provider);
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings ?? [], $key, $default);
    }

    public function isConfigured(): bool
    {
        return $this->is_enabled
            && !empty($this->api_url)
            && $this->hasApiKey();
    }
}
