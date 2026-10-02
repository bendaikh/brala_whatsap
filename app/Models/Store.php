<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Store extends Model
{
    protected $fillable = [
        'workspace_id',
        'service_integration_id',
        'user_id',
        'name',
        'subdomain',
        'domain',
        'description',
        'logo',
        'is_active',
        'facebook_pixel_id',
        'facebook_pixel_enabled',
        'tiktok_pixel_id',
        'tiktok_pixel_enabled',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'facebook_pixel_enabled' => 'boolean',
        'tiktok_pixel_enabled' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($store) {
            if (empty($store->subdomain)) {
                $store->subdomain = Str::slug($store->name);
            }
        });
    }

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function serviceIntegration()
    {
        return $this->belongsTo(WorkspaceServiceIntegration::class, 'service_integration_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function websiteSettings()
    {
        return $this->hasOne(WebsiteSettings::class);
    }

    public function facebookPixels()
    {
        return $this->hasMany(FacebookPixel::class);
    }

    public function activeFacebookPixels()
    {
        return $this->hasMany(FacebookPixel::class)->where('is_enabled', true);
    }

    public function blockedIps()
    {
        return $this->hasMany(BlockedIp::class);
    }
}
