<?php

namespace App\Support;

use App\Models\Store;

class StoreDomain
{
    public static function normalize(string $domain): string
    {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = rtrim($domain, '/');

        return preg_replace('/^www\./', '', $domain);
    }

    public static function platformDomain(): string
    {
        return self::normalize(config('domains.platform_domain', 'localhost'));
    }

    public static function serverIp(): ?string
    {
        $ip = trim((string) config('domains.server_ip', ''));

        return $ip !== '' ? $ip : null;
    }

    public static function isPlatformHost(string $host): bool
    {
        $host = self::normalize($host);

        if (in_array($host, ['localhost', '127.0.0.1'], true)) {
            return true;
        }

        $platform = self::platformDomain();

        if ($host === $platform) {
            return true;
        }

        return str_ends_with($host, '.' . $platform);
    }

    public static function resolveFromHost(string $host): ?Store
    {
        if (self::isPlatformHost($host)) {
            return null;
        }

        $normalized = self::normalize($host);

        return Store::query()
            ->where('is_active', true)
            ->whereNotNull('domain')
            ->where(function ($query) use ($host, $normalized) {
                $query->where('domain', $host)
                    ->orWhere('domain', $normalized)
                    ->orWhere('domain', 'www.' . $normalized);
            })
            ->first();
    }

    public static function defaultStoreUrl(Store $store): string
    {
        $platform = self::platformDomain();
        $scheme = parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'https';

        if (in_array($platform, ['localhost', '127.0.0.1'], true)) {
            return url('/store/' . $store->subdomain);
        }

        return $scheme . '://' . $store->subdomain . '.' . $platform;
    }

    public static function storeHomeUrl(Store $store): string
    {
        if ($store->domain) {
            $domain = trim($store->domain);

            if (!preg_match('#^https?://#i', $domain)) {
                $domain = 'https://' . $domain;
            }

            return rtrim($domain, '/');
        }

        return self::defaultStoreUrl($store);
    }

    public static function productUrl(Store $store, string $slug): string
    {
        if (self::isCurrentCustomDomainRequest($store)) {
            return url('/product/' . $slug);
        }

        if ($store->domain) {
            return self::storeHomeUrl($store) . '/product/' . $slug;
        }

        return route('store.product.show', [$store->subdomain, $slug]);
    }

    public static function homeUrl(Store $store): string
    {
        if (self::isCurrentCustomDomainRequest($store)) {
            return url('/');
        }

        if ($store->domain) {
            return self::storeHomeUrl($store);
        }

        return route('store.home', $store->subdomain);
    }

    public static function submitLeadUrl(Store $store, string $slug): string
    {
        if (self::isCurrentCustomDomainRequest($store)) {
            return url('/product/' . $slug . '/submit-lead');
        }

        return route('store.product.submit-lead', [$store->subdomain, $slug]);
    }

    public static function thankYouUrl(): string
    {
        return route('thank-you');
    }

    public static function categoryHomeUrl(Store $store, string $categorySlug): string
    {
        if (self::isCurrentCustomDomainRequest($store)) {
            return url('/?category=' . $categorySlug) . '#products';
        }

        return route('store.home', ['subdomain' => $store->subdomain, 'category' => $categorySlug]) . '#products';
    }

    private static function isCurrentCustomDomainRequest(Store $store): bool
    {
        $resolved = request()->attributes->get('resolved_store');

        return $resolved && $resolved->id === $store->id;
    }

    public static function cleanInput(?string $domain): ?string
    {
        if ($domain === null || trim($domain) === '') {
            return null;
        }

        $domain = preg_replace('#^https?://#', '', trim($domain));
        $domain = rtrim($domain, '/');

        return strtolower($domain);
    }
}
