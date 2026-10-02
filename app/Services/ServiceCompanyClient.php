<?php

namespace App\Services;

use App\Models\Store;
use App\Models\User;
use App\Models\WorkspaceServiceIntegration;

/**
 * Resolves the correct order-push client for a store's affected service.
 */
class ServiceCompanyClient
{
    /**
     * @return ExternalApiService|SureshipApiService
     */
    public static function forStore(?Store $store, ?User $user = null)
    {
        $integration = $store?->relationLoaded('serviceIntegration')
            ? $store->serviceIntegration
            : $store?->serviceIntegration()->first();

        if (
            $integration
            && $integration->is_enabled
            && $integration->api_url
            && $integration->hasApiKey()
        ) {
            return self::fromIntegration($integration);
        }

        return ExternalApiService::forStore($store, $user);
    }

    /**
     * @return ExternalApiService|SureshipApiService
     */
    public static function fromIntegration(WorkspaceServiceIntegration $integration)
    {
        if ($integration->provider === WorkspaceServiceIntegration::PROVIDER_SURESHIP) {
            return SureshipApiService::fromIntegration($integration);
        }

        return ExternalApiService::fromIntegration($integration);
    }
}
