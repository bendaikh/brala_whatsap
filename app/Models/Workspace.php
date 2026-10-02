<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workspace extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'language',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function stores()
    {
        return $this->hasMany(Store::class);
    }

    public function whatsappProfiles()
    {
        return $this->hasMany(WhatsappProfile::class);
    }

    public function facebookAdAccounts()
    {
        return $this->hasMany(FacebookAdAccount::class);
    }

    public function tiktokAdAccounts()
    {
        return $this->hasMany(TikTokAdAccount::class);
    }

    public function aiApiSetting()
    {
        return $this->hasOne(AiApiSetting::class);
    }

    public function serviceIntegrations()
    {
        return $this->hasMany(WorkspaceServiceIntegration::class);
    }

    public function googleSheetConnections()
    {
        return $this->hasMany(GoogleSheetConnection::class);
    }

    public function getLanguage(): string
    {
        $language = $this->language ?: config('workspace.defaults.language', 'ar');
        $languages = config('workspace.languages', []);

        return array_key_exists($language, $languages) ? $language : 'ar';
    }

    public function getCurrency(): string
    {
        $currency = $this->currency ?: config('workspace.defaults.currency', 'MAD');
        $currencies = config('workspace.currencies', []);

        return array_key_exists($currency, $currencies) ? $currency : 'MAD';
    }

    public function getLanguageConfig(): array
    {
        return config('workspace.languages.' . $this->getLanguage(), config('workspace.languages.ar'));
    }

    public function getCurrencyConfig(): array
    {
        return config('workspace.currencies.' . $this->getCurrency(), config('workspace.currencies.MAD'));
    }

    public function getCurrencySymbol(): string
    {
        return $this->getCurrencyConfig()['symbol'] ?? $this->getCurrency();
    }

    public function getCurrencyCode(): string
    {
        return $this->getCurrencyConfig()['code'] ?? $this->getCurrency();
    }

    public function isRtl(): bool
    {
        return ($this->getLanguageConfig()['dir'] ?? 'ltr') === 'rtl';
    }

    public function getHtmlLang(): string
    {
        return $this->getLanguageConfig()['html_lang'] ?? $this->getLanguage();
    }

    public function getLandingPageColumn(): string
    {
        return 'landing_page_' . $this->getLanguage();
    }

    /**
     * Moroccan Darija for Arabic + MAD (or legacy Morocco-named workspaces).
     */
    public function usesDarija(): bool
    {
        if ($this->getLanguage() === 'ar' && $this->getCurrency() === 'MAD') {
            return true;
        }

        $name = mb_strtolower((string) $this->name);

        return str_contains($name, 'maroc')
            || str_contains($name, 'morocco')
            || str_contains($name, 'مغرب')
            || str_contains($name, 'المغرب');
    }

    public function getLanguageLabel(): string
    {
        return $this->getLanguageConfig()['label'] ?? strtoupper($this->getLanguage());
    }

    public function getCurrencyLabel(): string
    {
        return $this->getCurrencyConfig()['label'] ?? $this->getCurrency();
    }
}
