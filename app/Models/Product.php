<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'user_id',
        'store_id',
        'google_sheet_connection_id',
        'theme',
        'theme_data',
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'compare_at_price',
        'stock',
        'sku',
        'has_variations',
        'has_promotions',
        'images',
        'main_image',
        'image_descriptions',
        'ai_generated_images',
        'ai_images_status',
        'ai_images_progress',
        'ai_images_total',
        'ai_images_generated',
        'is_active',
        'is_featured',
        'order',
        'landing_page_content',
        'landing_page_hero_title',
        'landing_page_hero_description',
        'landing_page_features',
        'landing_page_cta',
        'landing_page_fr',
        'landing_page_en',
        'landing_page_ar',
        'landing_page_status',
        'landing_page_sections',
        'landing_page_background_color',
        'form_fields'
    ];

    protected $casts = [
        'images' => 'array',
        'image_descriptions' => 'array',
        'ai_generated_images' => 'array',
        'theme_data' => 'array',
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'has_variations' => 'boolean',
        'has_promotions' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'landing_page_features' => 'array',
        'landing_page_fr' => 'array',
        'landing_page_en' => 'array',
        'landing_page_ar' => 'array',
        'landing_page_sections' => 'array',
        'form_fields' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function googleSheetConnection()
    {
        return $this->belongsTo(GoogleSheetConnection::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Resolve a stored path or URL to a usable browser URL (public disk, absolute URL, or site-relative).
     * Returns null for local paths where the underlying file is missing so callers can fall back.
     */
    public static function resolvePublicImageUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $path = trim($path);

        // If it's already a full URL, return as-is
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        // Normalize to a relative storage path (no leading slash, no "storage/" prefix)
        $relativePath = $path;
        if (str_starts_with($relativePath, '/storage/')) {
            $relativePath = substr($relativePath, strlen('/storage/'));
        } elseif (str_starts_with($relativePath, 'storage/')) {
            $relativePath = substr($relativePath, strlen('storage/'));
        } else {
            $relativePath = ltrim($relativePath, '/');
        }

        // Verify the file actually exists on the public disk; fall back if not
        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($relativePath)) {
            return null;
        }

        return '/storage/' . $relativePath;
    }

    public function getFirstImageAttribute()
    {
        // Always prefer the first uploaded product image as the landing-page hero
        $candidates = [];
        if (! empty($this->images) && isset($this->images[0])) {
            $candidates[] = $this->images[0];
        }
        if ($this->main_image) {
            $candidates[] = $this->main_image;
        }
        if (! empty($this->ai_generated_images) && isset($this->ai_generated_images[0])) {
            $candidates[] = $this->ai_generated_images[0];
        }

        foreach ($candidates as $raw) {
            $url = self::resolvePublicImageUrl($raw);
            if ($url !== null && $url !== '') {
                return $url;
            }
        }

        return self::placeholderImageUri();
    }

    /**
     * Remove <img> tags from HTML that duplicate the main/hero product image
     * (same URL or identical file content from a re-upload).
     */
    public function stripDuplicateMainImagesFromHtml(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        $heroUrl = $this->first_image;
        if ($heroUrl === null || $heroUrl === '' || str_starts_with($heroUrl, 'data:')) {
            return $html;
        }

        $heroPath = parse_url($heroUrl, PHP_URL_PATH) ?: $heroUrl;
        $heroBasename = basename($heroPath);
        $heroHash = null;

        $heroRelative = ltrim(str_replace('/storage/', '', $heroPath), '/');
        $heroFullPath = storage_path('app/public/' . $heroRelative);
        if (is_file($heroFullPath)) {
            $heroHash = md5_file($heroFullPath);
        }

        $cleaned = preg_replace_callback('/<img\b[^>]*>/i', function (array $match) use ($heroPath, $heroBasename, $heroHash) {
            if (! preg_match('/\bsrc\s*=\s*(["\'])([^"\']+)\1/i', $match[0], $srcMatch)
                && ! preg_match('/\bsrc\s*=\s*([^\s>]+)/i', $match[0], $srcMatch)) {
                return $match[0];
            }

            $src = html_entity_decode($srcMatch[count($srcMatch) - 1], ENT_QUOTES);
            $srcPath = parse_url($src, PHP_URL_PATH) ?: $src;
            $srcBasename = basename($srcPath);

            if ($srcPath === $heroPath || $srcBasename === $heroBasename) {
                return '';
            }

            if ($heroHash) {
                $srcRelative = ltrim(preg_replace('#^/?storage/#', '', $srcPath), '/');
                $srcFullPath = storage_path('app/public/' . $srcRelative);
                if (is_file($srcFullPath) && md5_file($srcFullPath) === $heroHash) {
                    return '';
                }
            }

            return $match[0];
        }, $html) ?? $html;

        // Drop empty paragraphs left behind after removing the duplicate image
        $cleaned = preg_replace('/<p[^>]*>\s*(?:<br\s*\/?>)?\s*<\/p>/i', '', $cleaned) ?? $cleaned;

        return $cleaned;
    }

    /**
     * Returns an inline SVG data URI placeholder so it works offline and
     * doesn't depend on any external service being reachable.
     */
    public static function placeholderImageUri(): string
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400">'
            . '<rect width="400" height="400" fill="#e5e7eb"/>'
            . '<g fill="#9ca3af">'
            . '<circle cx="200" cy="170" r="40"/>'
            . '<rect x="120" y="230" width="160" height="16" rx="8"/>'
            . '<rect x="140" y="258" width="120" height="10" rx="5"/>'
            . '</g>'
            . '<text x="200" y="330" text-anchor="middle" font-family="Arial, sans-serif" font-size="18" fill="#6b7280">No Image</text>'
            . '</svg>';

        return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
    }

    public function getDiscountPercentageAttribute()
    {
        if ($this->compare_at_price && $this->compare_at_price > $this->price) {
            return round((($this->compare_at_price - $this->price) / $this->compare_at_price) * 100);
        }
        return 0;
    }

    public function leads()
    {
        return $this->hasMany(ProductLead::class);
    }

    public function getAllImagesAttribute()
    {
        $images = [];
        
        if (! empty($this->images)) {
            foreach ($this->images as $image) {
                $resolved = self::resolvePublicImageUrl($image);
                if ($resolved) {
                    $images[] = $resolved;
                }
            }
        }

        if (! empty($this->ai_generated_images)) {
            foreach ($this->ai_generated_images as $image) {
                $resolved = self::resolvePublicImageUrl($image);
                if ($resolved) {
                    $images[] = $resolved;
                }
            }
        }
        
        if (empty($images)) {
            return [self::placeholderImageUri()];
        }

        return $images;
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function activeVariations()
    {
        return $this->hasMany(ProductVariation::class)->where('is_active', true)->orderBy('order');
    }

    public function defaultVariation()
    {
        return $this->hasOne(ProductVariation::class)->where('is_default', true);
    }

    public function getPriceRangeAttribute()
    {
        if (!$this->has_variations) {
            return null;
        }

        $variations = $this->activeVariations;
        if ($variations->isEmpty()) {
            return null;
        }

        $minPrice = $variations->min('price');
        $maxPrice = $variations->max('price');
        $currencySymbol = $this->store?->workspace?->getCurrencySymbol()
            ?? config('workspace.currencies.MAD.symbol', 'DHS');

        if ($minPrice == $maxPrice) {
            return number_format($minPrice, 2) . ' ' . $currencySymbol;
        }

        return number_format($minPrice, 2) . ' - ' . number_format($maxPrice, 2) . ' ' . $currencySymbol;
    }

    public function getTotalStockAttribute()
    {
        if (!$this->has_variations) {
            return $this->stock;
        }

        return $this->variations()->sum('stock');
    }

    public function promotions()
    {
        return $this->hasMany(ProductPromotion::class)->orderBy('min_quantity');
    }

    public function activePromotions()
    {
        return $this->hasMany(ProductPromotion::class)->where('is_active', true)->orderBy('min_quantity');
    }

    public function getPriceForQuantity($quantity, $variationId = null)
    {
        if (!$this->has_promotions) {
            return $variationId ? $this->variations()->find($variationId)?->price : $this->price;
        }

        $query = $this->promotions()->where('is_active', true);
        
        if ($variationId) {
            $query->where('product_variation_id', $variationId);
        } else {
            $query->whereNull('product_variation_id');
        }

        $promotion = $query->where('min_quantity', '<=', $quantity)
            ->where(function($q) use ($quantity) {
                $q->whereNull('max_quantity')
                  ->orWhere('max_quantity', '>=', $quantity);
            })
            ->orderBy('min_quantity', 'desc')
            ->first();

        if ($promotion) {
            return $promotion->price;
        }

        return $variationId ? $this->variations()->find($variationId)?->price : $this->price;
    }
}

