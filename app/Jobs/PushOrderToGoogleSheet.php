<?php

namespace App\Jobs;

use App\Models\ProductLead;
use App\Services\GoogleSheetService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class PushOrderToGoogleSheet implements ShouldQueue
{
    use Queueable;

    public function __construct(protected ProductLead $lead)
    {
    }

    public function handle(GoogleSheetService $service): void
    {
        $lead = $this->lead->fresh(['product.googleSheetConnection', 'variation', 'promotion']);

        if (!$lead) {
            Log::warning('Lead not found for Google Sheet push');
            return;
        }

        $connection = $lead->product?->googleSheetConnection;

        if (!$connection || !$connection->isConfigured()) {
            Log::info('No Google Sheet connected for lead ' . $lead->id, [
                'product_id' => $lead->product_id,
            ]);
            return;
        }

        $result = $service->appendLead($connection, $lead);

        if ($result['success']) {
            Log::info('Lead pushed to Google Sheet', [
                'lead_id' => $lead->id,
                'connection_id' => $connection->id,
            ]);
        } else {
            Log::error('Failed to push lead to Google Sheet', [
                'lead_id' => $lead->id,
                'connection_id' => $connection->id,
                'error' => $result['message'],
            ]);
        }
    }
}
