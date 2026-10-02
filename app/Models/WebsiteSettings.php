<?php

namespace App\Models;

use App\Support\StorefrontCopy;
use Illuminate\Database\Eloquent\Model;

class WebsiteSettings extends Model
{
    protected $fillable = [
        'user_id',
        'store_id',
        'site_name',
        'site_description',
        'site_logo',
        'site_favicon',
        'hero_title',
        'hero_subtitle',
        'hero_button_text',
        'hero_button_link',
        'hero_background_color',
        'hero_background_image',
        'show_top_banner',
        'banner_text',
        'banner_icon',
        'banner_bg_color',
        'primary_color',
        'secondary_color',
        'accent_color',
        'contact_phone',
        'contact_email',
        'contact_address',
        'whatsapp_number',
        'facebook_url',
        'instagram_url',
        'twitter_url',
        'youtube_url',
        'footer_about',
        'footer_copyright',
        'features',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'show_top_banner' => 'boolean',
        'features' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public static function getSettings($userId, $storeId = null)
    {
        $where = ['user_id' => $userId];
        if ($storeId) {
            $where['store_id'] = $storeId;
        }

        $workspace = null;
        if ($storeId) {
            $store = Store::with('workspace')->find($storeId);
            $workspace = $store?->workspace;
        }

        $defaults = StorefrontCopy::settingsDefaults($workspace);

        $settings = static::firstOrCreate(
            $where,
            array_merge([
                'site_name' => config('app.name'),
            ], $defaults)
        );

        // If this store belongs to a non-Arabic workspace but still has Arabic seed
        // content (created before language support), refresh the default copy once.
        if (
            $workspace
            && $workspace->getLanguage() !== 'ar'
            && StorefrontCopy::looksLikeArabicSeed($settings->hero_title)
        ) {
            $settings->fill($defaults);
            $settings->save();
        }

        return $settings;
    }
}
