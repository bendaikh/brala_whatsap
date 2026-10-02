<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\AiApiSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Support\WebpImage;

class GenerateProductImagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $product;
    protected $userId;
    protected $numberOfImages;

    public function __construct(Product $product, $userId, int $numberOfImages = 5)
    {
        $this->product = $product;
        $this->userId = $userId;
        $this->numberOfImages = $numberOfImages;
    }

    public function handle(): void
    {
        try {
            $this->product->update([
                'ai_images_status' => 'generating',
                'ai_images_total' => $this->numberOfImages,
                'ai_images_generated' => 0,
                'ai_images_progress' => 0,
            ]);

            $aiSetting = AiApiSetting::where('user_id', $this->userId)->first();
            
            if (!$aiSetting || empty($aiSetting->openai_api_key_encrypted)) {
                throw new \Exception('OpenAI API key not configured');
            }

            $apiKey = Crypt::decryptString($aiSetting->openai_api_key_encrypted);
            $generatedImages = [];

            // First, analyze the uploaded product image with GPT-4 Vision to get accurate description
            $detailedDescription = $this->analyzeProductImage($apiKey);
            
            Log::info("Product image analysis for product {$this->product->id}: " . $detailedDescription);

            for ($i = 0; $i < $this->numberOfImages; $i++) {
                try {
                    $prompt = $this->generateImagePrompt($i, $detailedDescription);
                    
                    $response = Http::withToken($apiKey)
                        ->timeout(120)
                        ->post('https://api.openai.com/v1/images/generations', [
                            'model' => 'dall-e-3',
                            'prompt' => $prompt,
                            'n' => 1,
                            'size' => '1024x1024',
                            'quality' => 'standard',
                        ]);

                    if ($response->successful()) {
                        $imageUrl = $response->json()['data'][0]['url'];
                        
                        $imageContent = Http::timeout(60)->get($imageUrl)->body();
                        $filename = WebpImage::storeBinary($imageContent, 'products/ai-generated');
                        
                        $generatedImages[] = Storage::url($filename);
                        
                        $progress = (int) ((($i + 1) / $this->numberOfImages) * 100);
                        
                        $this->product->update([
                            'ai_generated_images' => $generatedImages,
                            'ai_images_generated' => $i + 1,
                            'ai_images_progress' => $progress,
                        ]);

                        Log::info("Generated image " . ($i + 1) . " of {$this->numberOfImages} for product: {$this->product->id}");
                        
                        sleep(2);
                    } else {
                        Log::error("Failed to generate image " . ($i + 1) . " for product {$this->product->id}: " . $response->body());
                    }
                } catch (\Exception $e) {
                    Log::error("Error generating image " . ($i + 1) . " for product {$this->product->id}: " . $e->getMessage());
                }
            }

            if (count($generatedImages) > 0) {
                $this->product->update([
                    'ai_images_status' => 'completed',
                    'ai_images_progress' => 100,
                ]);
                Log::info("Successfully generated " . count($generatedImages) . " images for product: {$this->product->id}");

                // If description is still empty, rebuild it from uploaded product images
                try {
                    $user = \App\Models\User::find($this->userId);
                    if ($user) {
                        $aiService = new \App\Services\AiLandingPageService($user);
                        $aiService->ensureProductDescriptionFromImages($this->product->fresh());
                    }
                } catch (\Throwable $e) {
                    Log::warning('Could not sync description after AI images for product ' . $this->product->id . ': ' . $e->getMessage());
                }
            } else {
                throw new \Exception('No images were generated successfully');
            }

        } catch (\Exception $e) {
            $this->product->update(['ai_images_status' => 'failed']);
            Log::error('Failed to generate images for product ' . $this->product->id . ': ' . $e->getMessage());
            throw $e;
        }
    }

    private function analyzeProductImage(string $apiKey): string
    {
        // Get the first uploaded product image
        $images = $this->product->images ?? [];
        
        if (empty($images)) {
            // No image uploaded, use product name and description
            return $this->buildProductDescription();
        }

        $imagePath = $images[0];
        
        // Get the full URL or base64 of the image
        try {
            $imageContent = Storage::disk('public')->get($imagePath);
            $base64Image = base64_encode($imageContent);
            $mimeType = 'image/jpeg';
            
            // Detect mime type
            $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
            if (in_array(strtolower($extension), ['png'])) {
                $mimeType = 'image/png';
            } elseif (in_array(strtolower($extension), ['gif'])) {
                $mimeType = 'image/gif';
            } elseif (in_array(strtolower($extension), ['webp'])) {
                $mimeType = 'image/webp';
            }

            // Use GPT-4 Vision to analyze the image
            $response = Http::withToken($apiKey)
                ->timeout(60)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o',
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => "Analyze this product image in detail. Describe the EXACT product shown including:
1. What type of product is it (be very specific - exact item type, not generic)
2. The exact colors (primary and secondary colors)
3. The materials/textures visible
4. The shape and design elements
5. Any text, logos, or patterns visible
6. The style (modern, vintage, minimal, etc.)
7. Size indication if visible

Product name for context: {$this->product->name}

Provide a detailed, specific description that could be used to recreate this EXACT product in an image generation AI. Focus on visual accuracy. Be concise but complete."
                                ],
                                [
                                    'type' => 'image_url',
                                    'image_url' => [
                                        'url' => "data:{$mimeType};base64,{$base64Image}",
                                        'detail' => 'high'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'max_tokens' => 500
                ]);

            if ($response->successful()) {
                $analysis = $response->json()['choices'][0]['message']['content'] ?? '';
                return trim($analysis);
            }

        } catch (\Exception $e) {
            Log::warning("Could not analyze product image: " . $e->getMessage());
        }

        // Fallback to basic description
        return $this->buildProductDescription();
    }

    private function buildProductDescription(): string
    {
        return trim($this->product->name . '. ' . ($this->product->description ?? ''));
    }

    private function generateImagePrompt(int $index, string $detailedDescription): string
    {
        $productName = $this->product->name;
        
        // Create prompts that use the detailed description from image analysis
        $angles = [
            "Professional e-commerce product photography. Create an image of EXACTLY this product: {$detailedDescription}. Front view, centered on pure white background, professional studio lighting, high quality, sharp details, commercial photography style.",
            
            "Professional e-commerce product photography. Create an image of EXACTLY this product: {$detailedDescription}. 45-degree angle view, pure white background, soft studio lighting, detailed textures visible, commercial quality.",
            
            "Lifestyle product photography. Show EXACTLY this product: {$detailedDescription}. In an elegant, minimal setting appropriate for the product type. Natural lighting, professional composition, high-end commercial photography style.",
            
            "Professional product photography detail shot. Create EXACTLY this product: {$detailedDescription}. Close-up showing textures and details, pure white background, sharp focus, studio lighting, high resolution commercial photography.",
            
            "Professional flat lay product photography. Create EXACTLY this product: {$detailedDescription}. Overhead view, aesthetically arranged on white background, soft shadows, commercial e-commerce style photography."
        ];

        if ($index < count($angles)) {
            return $angles[$index];
        }
        
        return "Professional product photography. Create EXACTLY this product: {$detailedDescription}. White background, studio lighting, e-commerce style, high quality commercial photography.";
    }

    public function failed(\Throwable $exception): void
    {
        $this->product->update(['ai_images_status' => 'failed']);
        Log::error('Image generation job failed for product ' . $this->product->id . ': ' . $exception->getMessage());
    }
}
