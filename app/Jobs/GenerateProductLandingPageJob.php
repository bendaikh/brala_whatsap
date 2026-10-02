<?php

namespace App\Jobs;

use App\Models\Product;
use App\Services\AiLandingPageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateProductLandingPageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $product;
    protected $userId;
    /** @var bool|null Legacy Morocco flag kept for backwards-compatible job payloads */
    protected $isMoroccoWorkspace;

    /**
     * Create a new job instance.
     */
    public function __construct(Product $product, $userId, $isMoroccoWorkspace = null)
    {
        $this->product = $product;
        $this->userId = $userId;
        $this->isMoroccoWorkspace = $isMoroccoWorkspace;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->product->update(['landing_page_status' => 'processing']);

            $user = \App\Models\User::find($this->userId);

            if (!$user) {
                throw new \Exception('User not found');
            }

            $this->product->loadMissing('store.workspace');
            $workspace = $this->product->store?->workspace;

            $aiService = new AiLandingPageService($user, $this->isMoroccoWorkspace, $workspace);
            $landingPageData = $aiService->generateLandingPage($this->product);
            $aiService->saveLandingPageToProduct($this->product, $landingPageData);

            $this->product->update(['landing_page_status' => 'completed']);

            Log::info('Landing page generated successfully for product: ' . $this->product->id, [
                'language' => $aiService->getLanguage(),
                'currency' => $aiService->getCurrencyCode(),
            ]);
        } catch (\Exception $e) {
            $this->product->update(['landing_page_status' => 'failed']);

            Log::error('Failed to generate landing page for product ' . $this->product->id . ': ' . $e->getMessage());

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $this->product->update(['landing_page_status' => 'failed']);

        Log::error('Job failed for product ' . $this->product->id . ': ' . $exception->getMessage());
    }
}
