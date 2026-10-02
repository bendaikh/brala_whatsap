<?php

namespace App\Services;

use App\Models\AiApiSetting;
use App\Models\Product;
use App\Models\Workspace;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiLandingPageService
{
    protected $user;
    protected $aiSetting;
    protected $isMoroccoWorkspace = false;
    protected ?Workspace $workspace = null;
    protected string $language = 'ar';
    protected string $currencyCode = 'MAD';
    protected string $currencySymbol = 'DHS';

    /**
     * @param  mixed  $user
     * @param  bool|Workspace|null  $isMoroccoWorkspaceOrWorkspace  Legacy bool, or Workspace instance
     * @param  Workspace|null  $workspace
     */
    public function __construct($user, $isMoroccoWorkspaceOrWorkspace = null, ?Workspace $workspace = null)
    {
        $this->user = $user;
        $this->aiSetting = AiApiSetting::where('user_id', $user->id)->first();

        if ($isMoroccoWorkspaceOrWorkspace instanceof Workspace) {
            $workspace = $isMoroccoWorkspaceOrWorkspace;
            $isMoroccoWorkspaceOrWorkspace = null;
        }

        $this->workspace = $workspace ?? self::resolveWorkspace();
        $this->language = $this->workspace?->getLanguage() ?? config('workspace.defaults.language', 'ar');
        $this->currencyCode = $this->workspace?->getCurrencyCode() ?? 'MAD';
        $this->currencySymbol = $this->workspace?->getCurrencySymbol() ?? 'DHS';

        if ($isMoroccoWorkspaceOrWorkspace !== null) {
            $this->isMoroccoWorkspace = (bool) $isMoroccoWorkspaceOrWorkspace;
        } else {
            $this->isMoroccoWorkspace = $this->workspace
                ? $this->workspace->usesDarija()
                : self::detectMoroccoWorkspace();
        }
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    public function getCurrencySymbol(): string
    {
        return $this->currencySymbol;
    }

    public static function resolveWorkspace(?Workspace $workspace = null): ?Workspace
    {
        if ($workspace) {
            return $workspace;
        }

        $workspaceId = session('active_workspace_id');

        return $workspaceId ? Workspace::find($workspaceId) : null;
    }

    public static function detectMoroccoWorkspace(?Workspace $workspace = null): bool
    {
        $workspace = self::resolveWorkspace($workspace);

        if (!$workspace) {
            return false;
        }

        return $workspace->usesDarija();
    }

    public function generateLandingPage(Product $product, array $languages = null)
    {
        if (!$this->aiSetting) {
            throw new \Exception('AI API settings not configured. Please configure your AI settings first.');
        }

        $languages = $languages ?? [$this->language];
        $categoryName = $product->category ? $product->category->name : 'General';

        $results = [];
        foreach ($languages as $language) {
            $prompt = $this->buildPrompt($product, $categoryName, $language);

            if (!empty($this->aiSetting->openai_api_key_encrypted)) {
                $results[$language] = $this->generateWithOpenAI($prompt);
            } elseif (!empty($this->aiSetting->anthropic_api_key_encrypted)) {
                $results[$language] = $this->generateWithAnthropic($prompt);
            } else {
                throw new \Exception('No AI API key configured. Please add an OpenAI or Anthropic API key.');
            }

            if (isset($results[$language]['testimonials']) && is_array($results[$language]['testimonials'])) {
                $results[$language]['testimonials'] = array_slice($results[$language]['testimonials'], 0, 3);
            }
        }

        return $results;
    }

    /**
     * Generate title + short description for each uploaded product image.
     */
    public function generateImageCaptions(string $productName, string $category, int $count): array
    {
        if (!$this->aiSetting) {
            throw new \Exception('AI API settings not configured. Please configure your AI settings first.');
        }

        $languageLine = match (true) {
            $this->isMoroccoWorkspace => 'Write titles and descriptions in Moroccan Darija (الدارجة المغربية), using Arabic script.',
            $this->language === 'fr' => 'Write titles and descriptions in French (Français).',
            $this->language === 'en' => 'Write titles and descriptions in English.',
            default => 'Write titles and descriptions in Arabic (العربية).',
        };

        $prompt = "You are a professional marketing copywriter. {$languageLine}

Product Name: {$productName}
Category: {$category}

Generate EXACTLY {$count} image captions for a product landing page description.
Each caption needs a short catchy title and a small description (1-2 sentences).

Return ONLY valid JSON:
{
    \"captions\": [
        {\"title\": \"Short catchy title\", \"description\": \"1-2 sentence benefit description\"}
    ]
}

Requirements:
- Exactly {$count} items in captions
- Each title unique, 4-8 words
- Each description persuasive and specific to this product
- No markdown, no extra text";

        if (!empty($this->aiSetting->openai_api_key_encrypted)) {
            $parsed = $this->generateWithOpenAI($prompt);
        } elseif (!empty($this->aiSetting->anthropic_api_key_encrypted)) {
            $parsed = $this->generateWithAnthropic($prompt);
        } else {
            throw new \Exception('No AI API key configured.');
        }

        $captions = $parsed['captions'] ?? [];
        if (!is_array($captions) || count($captions) === 0) {
            $captions = [];
            for ($i = 1; $i <= $count; $i++) {
                if ($this->language === 'fr') {
                    $captions[] = [
                        'title' => "Avantage {$i}",
                        'description' => "Découvrez la qualité de {$productName}.",
                    ];
                } elseif ($this->language === 'en') {
                    $captions[] = [
                        'title' => "Benefit {$i}",
                        'description' => "Discover the quality of {$productName}.",
                    ];
                } else {
                    $captions[] = [
                        'title' => "ميزة {$i}",
                        'description' => "اكتشف الجودة ديال {$productName}.",
                    ];
                }
            }
        }

        return array_slice(array_values($captions), 0, $count);
    }

    protected function buildPrompt(Product $product, string $categoryName, string $language = 'fr'): string
    {
        $languageInstructions = [
            'fr' => 'Generate all content in French (Français)',
            'en' => 'Generate all content in English',
            'ar' => 'Generate all content in Arabic (العربية)'
        ];

        $instruction = $languageInstructions[$language] ?? $languageInstructions['fr'];
        $priceLabel = $this->currencySymbol ?: $this->currencyCode;

        if ($this->isMoroccoWorkspace && $language === 'ar') {
            $testimonialBlock = '
    "testimonials": [
        {"name": "أحمد", "text": "والله المنتج زوين بزاف، الجودة عالية والتوصيل كان سريع. كنصح بيه أي واحد!", "rating": 5},
        {"name": "فاطمة", "text": "صراحة عجبني بزاف، الثمن مناسب والخدمة ممتازة. غادي نعاود نشري من عندهم.", "rating": 5},
        {"name": "يوسف", "text": "وصلني فالوقت والجودة أحسن مما توقعت. شكرا بزاف على الخدمة!", "rating": 5}
    ],';
            $testimonialInstructions = "
CRITICAL INSTRUCTIONS FOR TESTIMONIALS (MOROCCO / DARIJA):
- Generate EXACTLY 3 testimonials — no more, no less
- Write ALL testimonial text in Moroccan Darija (الدارجة المغربية) using Arabic script
- Sound natural like real Moroccan customers
- Use Moroccan first names
- DO NOT use French or English in testimonials
- DO NOT use generic placeholders";
        } elseif ($language === 'fr') {
            $testimonialBlock = '
    "testimonials": [
        {"name": "Amina", "text": "J\'ai commandé ce produit et je suis vraiment impressionnée par la qualité. Livraison rapide, je recommande!", "rating": 5},
        {"name": "Kofi", "text": "Exactement ce que je recherchais! Le rapport qualité-prix est imbattable.", "rating": 5},
        {"name": "Fatou", "text": "Produit conforme à la description. Je commanderai à nouveau sans hésiter.", "rating": 5}
    ],';
            $testimonialInstructions = "
CRITICAL INSTRUCTIONS FOR TESTIMONIALS:
- Generate EXACTLY 3 testimonials — no more, no less
- Write AUTHENTIC, DETAILED testimonials in French for this specific product
- Use natural local first names appropriate for a Francophone market
- DO NOT use generic placeholders";
        } elseif ($language === 'en') {
            $testimonialBlock = '
    "testimonials": [
        {"name": "Sarah", "text": "I ordered this product and I\'m really impressed with the quality. Fast delivery!", "rating": 5},
        {"name": "James", "text": "Exactly what I was looking for! Great value for money.", "rating": 5},
        {"name": "Emily", "text": "Product matches the description. I will order again without hesitation.", "rating": 5}
    ],';
            $testimonialInstructions = "
CRITICAL INSTRUCTIONS FOR TESTIMONIALS:
- Generate EXACTLY 3 testimonials — no more, no less
- Write AUTHENTIC, DETAILED testimonials in English for this specific product
- DO NOT use generic placeholders";
        } else {
            $testimonialBlock = '
    "testimonials": [
        {"name": "أحمد", "text": "طلبت هذا المنتج وأنا معجب جداً بالجودة والتوصيل السريع.", "rating": 5},
        {"name": "فاطمة", "text": "بالضبط ما كنت أبحث عنه! السعر مناسب والخدمة ممتازة.", "rating": 5},
        {"name": "يوسف", "text": "المنتج مطابق للوصف. سأطلب مرة أخرى بدون تردد.", "rating": 5}
    ],';
            $testimonialInstructions = "
CRITICAL INSTRUCTIONS FOR TESTIMONIALS:
- Generate EXACTLY 3 testimonials — no more, no less
- Write AUTHENTIC, DETAILED testimonials in Arabic for this specific product
- DO NOT use generic placeholders";
        }

        return "You are a professional marketing copywriter and landing page designer. {$instruction}.

Create compelling landing page content for the following product:

Product Name: {$product->name}
Category: {$categoryName}
Price: {$product->price} {$priceLabel}
" . ($product->compare_at_price ? "Original Price: {$product->compare_at_price} {$priceLabel}\n" : "") . "
Description: {$product->description}

Generate a professional, conversion-optimized landing page in JSON format with these fields:

{
    \"background_color\": \"#1e3a8a\",
    \"hero_description\": \"Write 2-3 compelling sentences highlighting the main benefits\",
    \"features\": [
        {\"title\": \"Feature name\", \"description\": \"Why this feature matters\", \"icon\": \"✓\"},
        {\"title\": \"Feature name\", \"description\": \"Why this feature matters\", \"icon\": \"⚡\"},
        {\"title\": \"Feature name\", \"description\": \"Why this feature matters\", \"icon\": \"🎯\"},
        {\"title\": \"Feature name\", \"description\": \"Why this feature matters\", \"icon\": \"💎\"}
    ],
    \"steps\": [
        {\"number\": \"1\", \"title\": \"First step\", \"description\": \"Explain what customer does\"},
        {\"number\": \"2\", \"title\": \"Second step\", \"description\": \"Explain what customer does\"},
        {\"number\": \"3\", \"title\": \"Third step\", \"description\": \"Explain what customer does\"}
    ],
    \"steps_title\": \"Section heading for the steps\",
{$testimonialBlock}
    \"testimonials_title\": \"Section heading for testimonials\",
    \"faqs\": [
        {\"question\": \"Write a common question\", \"answer\": \"Write a helpful detailed answer\"},
        {\"question\": \"Write a common question\", \"answer\": \"Write a helpful detailed answer\"},
        {\"question\": \"Write a common question\", \"answer\": \"Write a helpful detailed answer\"},
        {\"question\": \"Write a common question\", \"answer\": \"Write a helpful detailed answer\"}
    ],
    \"faqs_title\": \"Section heading for FAQs\",
    \"cta\": \"Action button text\",
    \"full_description\": \"Write 3-4 detailed persuasive paragraphs about the product\",
    \"form_title\": \"Contact form heading\",
    \"form_subtitle\": \"Contact form subheading\",
    \"form_name_placeholder\": \"Name input placeholder\",
    \"form_phone_placeholder\": \"Phone input placeholder\",
    \"form_note_placeholder\": \"Note input placeholder\",
    \"form_submit_button\": \"Submit button text\"
}
{$testimonialInstructions}

Other requirements:
- background_color MUST be a 6-digit hex color (e.g. #0f766e) that visually matches this product's category, mood, and typical brand colors. Prefer rich, saturated dark-to-medium tones that look premium behind white text. Avoid pure white, very pale pastels, neon, or pure black. Examples by vibe: cosmetics/beauty → soft rose or plum; tech/gadgets → deep navy or teal; food → warm amber or burgundy; sports → bold green or charcoal; home → sage or terracotta; kids → playful but still deep enough for white text.
- Make content specific to {$categoryName} category and this exact product
- Focus on real benefits, not just features
- Use persuasive, action-oriented language
- All FAQs should address real concerns about buying/ordering
- Return ONLY valid JSON, no markdown or extra text
- All content must be in {$instruction} (except testimonials when Darija is required above, and except background_color which is always a hex code)";
    }

    protected function generateWithOpenAI(string $prompt): array
    {
        try {
            $apiKey = Crypt::decryptString($this->aiSetting->openai_api_key_encrypted);
        } catch (\Throwable $e) {
            throw new \Exception('Failed to decrypt OpenAI API key.');
        }

        $model = $this->aiSetting->openai_model ?: 'gpt-4o-mini';

        $response = Http::withToken($apiKey)
            ->timeout(60)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a professional marketing copywriter. Always respond with valid JSON only, no markdown or additional text.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 2000,
            ]);

        if (!$response->successful()) {
            $error = $response->json('error.message') ?? $response->body();
            Log::error('OpenAI API Error', ['error' => $error, 'status' => $response->status()]);
            throw new \Exception('OpenAI API request failed: ' . $error);
        }

        $content = $response->json('choices.0.message.content');
        
        return $this->parseAiResponse($content);
    }

    protected function generateWithAnthropic(string $prompt): array
    {
        try {
            $apiKey = Crypt::decryptString($this->aiSetting->anthropic_api_key_encrypted);
        } catch (\Throwable $e) {
            throw new \Exception('Failed to decrypt Anthropic API key.');
        }

        $model = $this->aiSetting->anthropic_model ?: 'claude-3-5-sonnet-20241022';

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])
            ->timeout(60)
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => $model,
                'max_tokens' => 2000,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.7,
            ]);

        if (!$response->successful()) {
            $error = $response->json('error.message') ?? $response->body();
            Log::error('Anthropic API Error', ['error' => $error, 'status' => $response->status()]);
            throw new \Exception('Anthropic API request failed: ' . $error);
        }

        $content = $response->json('content.0.text');
        
        return $this->parseAiResponse($content);
    }

    protected function parseAiResponse(string $content): array
    {
        $content = trim($content);
        
        $content = preg_replace('/^```json\s*/s', '', $content);
        $content = preg_replace('/\s*```$/s', '', $content);
        $content = trim($content);

        try {
            $data = json_decode($content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response from AI: ' . json_last_error_msg());
            }

            // Captions responses only need "captions"; full landing pages need features/description
            if (!isset($data['captions']) && (!isset($data['features']) || !isset($data['full_description']))) {
                throw new \Exception('AI response missing required fields.');
            }

            return $data;
        } catch (\Throwable $e) {
            Log::error('Failed to parse AI response', ['content' => $content, 'error' => $e->getMessage()]);
            throw new \Exception('Failed to parse AI response: ' . $e->getMessage());
        }
    }

    public function saveLandingPageToProduct(Product $product, array $landingPageData): void
    {
        $updateData = [];
        $lang = $this->language;
        $column = 'landing_page_' . $lang;

        // Prefer workspace language; fall back to any generated language key
        $page = $landingPageData[$lang]
            ?? $landingPageData['ar']
            ?? $landingPageData['fr']
            ?? $landingPageData['en']
            ?? null;

        if ($page !== null) {
            if (in_array($column, ['landing_page_ar', 'landing_page_fr', 'landing_page_en'], true)) {
                $updateData[$column] = $page;
            }

            $updateData['landing_page_hero_description'] = $page['hero_description'] ?? null;
            $updateData['landing_page_features'] = $page['features'] ?? [];
            $updateData['landing_page_cta'] = $page['cta'] ?? null;
            $updateData['landing_page_content'] = $page['full_description'] ?? null;

            $bgColor = $this->normalizeHexColor($page['background_color'] ?? null);
            if ($bgColor) {
                $updateData['landing_page_background_color'] = $bgColor;
            }
        }

        // Landing page sections are no longer generated — content lives in product description
        $updateData['landing_page_sections'] = [];

        $product->update($updateData);

        // If the create form never synced images into description, build it now
        $this->ensureProductDescriptionFromImages($product->fresh());
        
        Log::info('Landing page data saved for product: ' . $product->id, [
            'language' => $lang,
            'currency' => $this->currencyCode,
        ]);
    }

    /**
     * Normalize AI/user hex color to #RRGGBB, or null if invalid.
     */
    public function normalizeHexColor(?string $color): ?string
    {
        if ($color === null) {
            return null;
        }

        $color = trim($color);
        if (preg_match('/^#([0-9A-Fa-f]{3})$/', $color, $m)) {
            $h = $m[1];
            $color = '#' . $h[0] . $h[0] . $h[1] . $h[1] . $h[2] . $h[2];
        }

        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            return null;
        }

        return strtolower($color);
    }

    /**
     * True when description has no real text/images (empty Quill HTML counts as empty).
     */
    public static function isDescriptionEmpty(?string $html): bool
    {
        if ($html === null || trim($html) === '') {
            return true;
        }

        $text = trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $hasImage = stripos($html, '<img') !== false;
        $hasVideo = stripos($html, '<video') !== false || stripos($html, '<iframe') !== false;

        return $text === '' && !$hasImage && !$hasVideo;
    }

    /**
     * Build Quill-compatible HTML: title + short text + image for each product photo.
     *
     * @param  array<int, array{title?: string, description?: string}>  $captions
     * @param  array<int, string>  $imagePaths  Storage paths relative to the public disk
     */
    public function buildDescriptionHtml(array $imagePaths, array $captions = []): string
    {
        $parts = [];

        foreach (array_values($imagePaths) as $index => $path) {
            if (!is_string($path) || $path === '') {
                continue;
            }

            $url = \Storage::url($path);
            $title = trim((string) ($captions[$index]['title'] ?? ''));
            $desc = trim((string) ($captions[$index]['description'] ?? ''));

            if ($title !== '') {
                $parts[] = '<h2 class="ql-align-center"><strong>' . e($title) . '</strong></h2>';
            }
            if ($desc !== '') {
                $parts[] = '<p class="ql-align-center">' . e($desc) . '</p>';
            }
            $parts[] = '<p class="ql-align-center"><img src="' . e($url) . '"></p>';
        }

        return implode('', $parts);
    }

    /**
     * Populate product.description from uploaded images + AI captions when it is empty.
     * First image is reserved for the landing-page hero; only images[1+] go into description.
     * Uses landing-page features as caption fallback when available.
     */
    public function ensureProductDescriptionFromImages(Product $product, bool $force = false): bool
    {
        if (!$force && !self::isDescriptionEmpty($product->description)) {
            return false;
        }

        $allImages = array_values(array_filter($product->images ?? [], fn ($p) => is_string($p) && $p !== ''));
        // Skip index 0 — that image is the main/hero image on the landing page
        $images = array_values(array_slice($allImages, 1));
        if (count($images) === 0) {
            return false;
        }

        $captions = [];
        $categoryName = $product->category ? $product->category->name : 'General';

        try {
            $captions = $this->generateImageCaptions($product->name, $categoryName, count($images));
        } catch (\Throwable $e) {
            Log::warning('ensureProductDescriptionFromImages captions failed for product ' . $product->id . ': ' . $e->getMessage());
        }

        // Prefer landing-page features when captions are missing
        $features = $product->landing_page_features
            ?? ($product->landing_page_ar['features'] ?? []);
        if (!is_array($features)) {
            $features = [];
        }

        for ($i = 0; $i < count($images); $i++) {
            if (!empty($captions[$i]['title']) && !empty($captions[$i]['description'])) {
                continue;
            }
            $captions[$i] = [
                'title' => $captions[$i]['title'] ?? ($features[$i]['title'] ?? ('ميزة ' . ($i + 1))),
                'description' => $captions[$i]['description'] ?? ($features[$i]['description'] ?? ''),
            ];
        }

        $html = $this->buildDescriptionHtml($images, $captions);
        if ($html === '') {
            return false;
        }

        $product->update(['description' => $html]);
        Log::info('Product description built from images for product: ' . $product->id);

        return true;
    }

    /**
     * @deprecated Landing page sections are no longer used
     */
    protected function generateLandingSectionsFromImages(Product $product, array $landingPageData): array
    {
        return [];
    }

    /**
     * Generate image section descriptions using AI
     * @deprecated
     */
    public function generateImageSections(Product $product, array $languages = ['ar']): array
    {
        return [];
    }
}
