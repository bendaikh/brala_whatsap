<?php

namespace App\Http\Controllers;

use App\Models\AiApiSetting;
use App\Models\WhatsappProfile;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\AiLandingPageService;
use App\Jobs\GenerateProductLandingPageJob;
use App\Support\LandingFormFields;
use App\Support\WebpImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

class CustomerDashboardController extends Controller
{
    protected function getActiveStoreId()
    {
        return session('active_store_id');
    }

    protected function workspaceGoogleSheets(?int $workspaceId = null)
    {
        $workspaceId = $workspaceId ?: session('active_workspace_id');

        if (!$workspaceId) {
            $storeId = $this->getActiveStoreId();
            if ($storeId) {
                $workspaceId = \App\Models\Store::where('id', $storeId)->value('workspace_id');
            }
        }

        if (!$workspaceId) {
            return collect();
        }

        return \App\Models\GoogleSheetConnection::query()
            ->where('workspace_id', $workspaceId)
            ->where('user_id', auth()->id())
            ->orderBy('name')
            ->get();
    }

    protected function validateGoogleSheetConnectionId($value): array
    {
        $workspaceId = session('active_workspace_id');
        if (!$workspaceId) {
            $storeId = $this->getActiveStoreId();
            $workspaceId = $storeId
                ? \App\Models\Store::where('id', $storeId)->value('workspace_id')
                : null;
        }

        $rule = \Illuminate\Validation\Rule::exists('google_sheet_connections', 'id')
            ->where('user_id', auth()->id());

        if ($workspaceId) {
            $rule->where('workspace_id', $workspaceId);
        }

        return ['nullable', 'integer', $rule];
    }

    protected function getBlockedIpAddresses(): array
    {
        $storeId = $this->getActiveStoreId();

        if (!$storeId) {
            return [];
        }

        return \App\Models\BlockedIp::where('store_id', $storeId)
            ->pluck('ip_address')
            ->all();
    }

    public function dashboard()
    {
        $user = auth()->user();
        $storeId = $this->getActiveStoreId();
        
        $stats = [
            'conversations' => Conversation::whereHas('whatsappProfile', function($q) use ($user, $storeId) {
                $q->where('user_id', $user->id)
                  ->when($storeId, function($q) use ($storeId) {
                      $q->where('store_id', $storeId);
                  });
            })->count(),
            'messages' => Message::whereHas('whatsappProfile', function($q) use ($user, $storeId) {
                $q->where('user_id', $user->id)
                  ->when($storeId, function($q) use ($storeId) {
                      $q->where('store_id', $storeId);
                  });
            })->count(),
            'orders' => \App\Models\ProductLead::where('user_id', $user->id)
                ->when($storeId, function($q) use ($storeId) {
                    $q->whereHas('product', function($q) use ($storeId) {
                        $q->where('store_id', $storeId);
                    });
                })
                ->count(),
            'products' => \App\Models\Product::where('user_id', $user->id)
                ->when($storeId, function($q) use ($storeId) {
                    $q->where('store_id', $storeId);
                })
                ->count(),
            'categories' => \App\Models\Category::when($storeId, function($q) use ($storeId) {
                    $q->where('store_id', $storeId);
                })
                ->count(),
            'active_profiles' => WhatsappProfile::where('user_id', $user->id)
                ->when($storeId, function($q) use ($storeId) {
                    $q->where('store_id', $storeId);
                })
                ->where('status', 'connected')
                ->count(),
            'ai_tokens' => 0,
            'sales_percentage' => 0,
        ];
        
        $recent_conversations = Conversation::whereHas('whatsappProfile', function($q) use ($user, $storeId) {
            $q->where('user_id', $user->id)
              ->when($storeId, function($q) use ($storeId) {
                  $q->where('store_id', $storeId);
              });
        })
        ->with(['whatsappProfile'])
        ->latest('last_message_at')
        ->take(10)
        ->get();
        
        $whatsapp_profiles = WhatsappProfile::where('user_id', $user->id)
            ->when($storeId, function($q) use ($storeId) {
                $q->where('store_id', $storeId);
            })
            ->get();
        
        return view('customer.dashboard', compact('stats', 'recent_conversations', 'whatsapp_profiles'));
    }
    
    public function whatsapp()
    {
        $user = auth()->user();
        $storeId = $this->getActiveStoreId();
        
        $profiles = WhatsappProfile::where('user_id', $user->id)
            ->when($storeId, function($q) use ($storeId) {
                $q->where('store_id', $storeId);
            })
            ->get();
        
        return view('customer.whatsapp', compact('profiles'));
    }
    
    public function conversations()
    {
        $user = auth()->user();
        $storeId = $this->getActiveStoreId();
        
        $conversations = Conversation::whereHas('whatsappProfile', function($q) use ($user, $storeId) {
            $q->where('user_id', $user->id)
              ->when($storeId, function($q) use ($storeId) {
                  $q->where('store_id', $storeId);
              });
        })
        ->with(['whatsappProfile'])
        ->latest('last_message_at')
        ->paginate(20);
        
        return view('customer.conversations', compact('conversations'));
    }
    
    public function conversationDetail($id)
    {
        $user = auth()->user();
        $storeId = $this->getActiveStoreId();
        
        $conversation = Conversation::whereHas('whatsappProfile', function($q) use ($user, $storeId) {
            $q->where('user_id', $user->id)
              ->when($storeId, function($q) use ($storeId) {
                  $q->where('store_id', $storeId);
              });
        })
        ->with(['messages', 'whatsappProfile'])
        ->findOrFail($id);
        
        return view('customer.conversation-detail', compact('conversation'));
    }
    
    public function aiSettings()
    {
        $user = auth()->user();
        $aiSetting = AiApiSetting::where('user_id', $user->id)->first();

        return view('workspaces.ai-settings', compact('aiSetting'));
    }

    public function saveOpenAiSettings(Request $request)
    {
        $validated = $request->validate([
            'openai_api_key' => 'nullable|string|max:2048',
            'openai_model' => 'required|string|max:128',
            'clear_openai_key' => 'nullable|boolean',
            'auto_reply_enabled' => 'nullable|boolean',
        ]);

        $user = auth()->user();
        
        $setting = AiApiSetting::firstOrNew(['user_id' => $user->id]);
        $setting->openai_model = $validated['openai_model'];
        $setting->auto_reply_enabled = $request->boolean('auto_reply_enabled');

        if ($request->boolean('clear_openai_key')) {
            $setting->openai_api_key_encrypted = null;
        } elseif (! empty($validated['openai_api_key'])) {
            $setting->openai_api_key_encrypted = Crypt::encryptString(trim($validated['openai_api_key']));
        }

        $setting->save();

        return redirect()
            ->route('workspaces.ai-settings')
            ->withFragment('openai-connect')
            ->with('success', 'Paramètres OpenAI enregistrés.');
    }

    public function testOpenAiConnection(Request $request)
    {
        $user = auth()->user();
        $setting = AiApiSetting::where('user_id', $user->id)->first();

        if (! $setting || empty($setting->openai_api_key_encrypted)) {
            return redirect()
                ->route('workspaces.ai-settings')
                ->withFragment('openai-connect')
                ->with('openai_error', 'Enregistrez d\'abord une clé API OpenAI.');
        }

        try {
            $apiKey = Crypt::decryptString($setting->openai_api_key_encrypted);
        } catch (\Throwable) {
            return redirect()
                ->route('workspaces.ai-settings')
                ->withFragment('openai-connect')
                ->with('openai_error', 'Impossible de lire la clé enregistrée. Saisissez-la à nouveau et enregistrez.');
        }

        $response = Http::withToken($apiKey)
            ->timeout(20)
            ->acceptJson()
            ->get('https://api.openai.com/v1/models');

        if (! $response->successful()) {
            $message = data_get($response->json(), 'error.message') ?: $response->body();

            return redirect()
                ->route('workspaces.ai-settings')
                ->withFragment('openai-connect')
                ->with('openai_error', is_string($message) ? $message : 'La requête vers OpenAI a échoué.');
        }

        return redirect()
            ->route('workspaces.ai-settings')
            ->withFragment('openai-connect')
            ->with('openai_success', 'Connexion OpenAI réussie.');
    }

    public function saveAnthropicSettings(Request $request)
    {
        $validated = $request->validate([
            'anthropic_api_key' => 'nullable|string|max:2048',
            'anthropic_model' => 'required|string|max:128',
            'clear_anthropic_key' => 'nullable|boolean',
        ]);

        $user = auth()->user();
        
        $setting = AiApiSetting::firstOrNew(['user_id' => $user->id]);
        $setting->anthropic_model = $validated['anthropic_model'];

        if ($request->boolean('clear_anthropic_key')) {
            $setting->anthropic_api_key_encrypted = null;
        } elseif (! empty($validated['anthropic_api_key'])) {
            $setting->anthropic_api_key_encrypted = Crypt::encryptString(trim($validated['anthropic_api_key']));
        }

        $setting->save();

        return redirect()
            ->route('workspaces.ai-settings')
            ->withFragment('anthropic-connect')
            ->with('success', 'Paramètres Anthropic enregistrés.');
    }

    public function testAnthropicConnection(Request $request)
    {
        $user = auth()->user();
        $setting = AiApiSetting::where('user_id', $user->id)->first();

        if (! $setting || empty($setting->anthropic_api_key_encrypted)) {
            return redirect()
                ->route('workspaces.ai-settings')
                ->withFragment('anthropic-connect')
                ->with('anthropic_error', 'Enregistrez d\'abord une clé API Anthropic.');
        }

        try {
            $apiKey = Crypt::decryptString($setting->anthropic_api_key_encrypted);
        } catch (\Throwable) {
            return redirect()
                ->route('workspaces.ai-settings')
                ->withFragment('anthropic-connect')
                ->with('anthropic_error', 'Impossible de lire la clé enregistrée. Saisissez-la à nouveau et enregistrez.');
        }

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])
            ->timeout(20)
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => $setting->anthropic_model ?: 'claude-3-5-sonnet-20241022',
                'max_tokens' => 10,
                'messages' => [
                    ['role' => 'user', 'content' => 'Hello']
                ]
            ]);

        if (! $response->successful()) {
            $message = data_get($response->json(), 'error.message') ?: $response->body();

            return redirect()
                ->route('workspaces.ai-settings')
                ->withFragment('anthropic-connect')
                ->with('anthropic_error', is_string($message) ? $message : 'La requête vers Anthropic a échoué.');
        }

        return redirect()
            ->route('workspaces.ai-settings')
            ->withFragment('anthropic-connect')
            ->with('anthropic_success', 'Connexion Anthropic réussie.');
    }
    
    public function orders(Request $request)
    {
        $user = auth()->user();
        $storeId = $this->getActiveStoreId();
        
        $query = \App\Models\ProductLead::with(['product', 'promotion', 'variation'])
            ->where('user_id', $user->id);
        
        if ($storeId) {
            $query->whereHas('product', function($q) use ($storeId) {
                $q->where('store_id', $storeId);
            });
        }
        
        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Search by name or phone
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        $orders = $query->latest()->paginate(20)->withQueryString();
        $blockedIpAddresses = $this->getBlockedIpAddresses();

        return view('customer.orders', compact('orders', 'blockedIpAddresses'));
    }

    public function editOrder(\App\Models\ProductLead $lead)
    {
        $this->authorizeLead($lead);

        $lead->load(['product', 'promotion', 'variation']);

        return view('customer.orders-edit', compact('lead'));
    }

    public function updateOrder(Request $request, \App\Models\ProductLead $lead)
    {
        $this->authorizeLead($lead);

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'note' => 'nullable|string|max:1000',
            'status' => 'required|in:pending,confirmed,shipped,delivered,cancelled',
        ]);

        $lead->update($validated);

        return redirect()
            ->route('app.orders')
            ->with('success', 'Commande mise à jour avec succès.');
    }

    public function destroyOrder(\App\Models\ProductLead $lead)
    {
        $this->authorizeLead($lead);

        $lead->delete();

        return $this->redirectAfterLeadDelete('Commande supprimée avec succès.');
    }

    public function bulkDestroyOrders(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:product_leads,id',
        ]);

        $user = auth()->user();
        $storeId = $this->getActiveStoreId();

        $query = \App\Models\ProductLead::where('user_id', $user->id)
            ->whereIn('id', $validated['ids']);

        if ($storeId) {
            $query->whereHas('product', function ($q) use ($storeId) {
                $q->where('store_id', $storeId);
            });
        }

        $deleted = $query->delete();

        return $this->redirectAfterLeadDelete($deleted . ' commande(s) supprimée(s) avec succès.');
    }

    protected function redirectAfterLeadDelete(string $message)
    {
        $from = request()->input('from');

        if ($from === 'leads') {
            return redirect()->route('app.leads')->with('success', $message);
        }

        if ($from === 'orders') {
            return redirect()->route('app.orders')->with('success', $message);
        }

        return redirect()->back()->with('success', $message);
    }

    protected function authorizeLead(\App\Models\ProductLead $lead): void
    {
        $user = auth()->user();
        $storeId = $this->getActiveStoreId();

        if ($lead->user_id !== $user->id) {
            abort(403);
        }

        if ($storeId && $lead->product && $lead->product->store_id != $storeId) {
            abort(403);
        }
    }

    public function products()
    {
        $user = auth()->user();
        $storeId = $this->getActiveStoreId();
        
        $query = \App\Models\Product::where('user_id', $user->id);
        
        $store = null;
        if ($storeId) {
            $query->where('store_id', $storeId);
            $store = \App\Models\Store::find($storeId);
        }
        
        $products = $query->latest()->paginate(10);
        
        return view('customer.products', compact('products', 'store'));
    }
    
    public function productsSelectTheme()
    {
        // Theme selection removed - redirect to products create with theme1
        return redirect()->route('app.products.create', ['theme' => 'theme1']);
    }

    public function productsCreate(Request $request)
    {
        $theme = 'theme1'; // Always use theme1

        $storeId = $this->getActiveStoreId();
        $store = $storeId ? \App\Models\Store::with('workspace')->find($storeId) : null;
        $workspaceLang = $store?->workspace?->getLanguage() ?? 'ar';

        $query = \App\Models\Category::where('is_active', true);

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        $categories = $query->orderBy('order')->orderBy('name')->get();
        $googleSheets = $this->workspaceGoogleSheets($store?->workspace_id);

        return view('customer.products-create', compact('categories', 'theme', 'store', 'googleSheets', 'workspaceLang'));
    }
    
    public function productsStore(Request $request)
    {
        // Check if has_variations is enabled
        $hasVariations = $request->boolean('has_variations');
        $hasPromotions = $request->boolean('has_promotions');
        
        // Debug logging
        \Log::info('Product creation - has_promotions: ' . ($hasPromotions ? 'true' : 'false'));
        \Log::info('Product creation - promotions data: ' . json_encode($request->input('promotions')));
        \Log::info('Product creation - promotions_json: ' . $request->input('promotions_json'));
        \Log::info('Product creation - all request keys: ' . json_encode(array_keys($request->all())));
        
        // Check for promotions - first try the regular array, then fallback to JSON field
        $promotionsData = $request->input('promotions');
        
        // If regular promotions array is empty but we have JSON data, use that instead
        if (empty($promotionsData) && $request->has('promotions_json')) {
            $jsonData = json_decode($request->input('promotions_json'), true);
            if (!empty($jsonData) && is_array($jsonData)) {
                $promotionsData = [];
                foreach ($jsonData as $index => $promo) {
                    $promotionsData[$index] = $promo;
                }
                // Merge back into request so validation works
                $request->merge(['promotions' => $promotionsData]);
                \Log::info('Product creation - using promotions from JSON fallback: ' . json_encode($promotionsData));
            }
        }
        
        // Only require promotions array if has_promotions is true AND promotions were submitted
        $promotionsSubmitted = $hasPromotions && !empty($promotionsData);
        
        \Log::info('Product creation - promotionsSubmitted: ' . ($promotionsSubmitted ? 'true' : 'false'));
        \Log::info('Product creation - promotions count: ' . ($promotionsSubmitted ? count($promotionsData) : 0));

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'theme' => 'nullable|string|in:theme1',
            'theme_data' => 'nullable|array',
            'price' => $hasVariations ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'images' => 'nullable|array',
            'images.*' => 'image|max:10240',
            'preuploaded_images' => 'nullable|array',
            'preuploaded_images.*' => 'nullable|string|max:500',
            'stock' => 'nullable|integer|min:0',
            'sku' => 'nullable|string|max:255',
            'has_variations' => 'nullable|boolean',
            'has_promotions' => 'nullable|boolean',
            'variations' => $hasVariations ? 'required|array|min:1' : 'nullable|array',
            'variations.*.price' => 'required_with:variations|numeric|min:0',
            'variations.*.compare_at_price' => 'nullable|numeric|min:0',
            'variations.*.stock' => 'required_with:variations|integer|min:0',
            'variations.*.sku' => 'nullable|string|max:255',
            'variations.*.attributes' => 'nullable|array',
            'variations.*.is_default' => 'nullable|boolean',
            'variations.*.is_active' => 'nullable|boolean',
            'promotions' => 'nullable|array',
            'promotions.*.label' => 'nullable|string|max:255',
            'promotions.*.min_quantity' => 'required_with:promotions|integer|min:1',
            'promotions.*.max_quantity' => 'nullable|integer|min:1',
            'promotions.*.price' => 'required_with:promotions|numeric|min:0',
            'promotions.*.compare_at_price' => 'nullable|numeric|min:0',
            'landing_sections' => 'nullable|array',
            'landing_sections.*.image' => 'nullable|image|max:10240',
            'landing_sections.*.title_fr' => 'nullable|string|max:255',
            'landing_sections.*.description_fr' => 'nullable|string',
            'landing_sections.*.title_en' => 'nullable|string|max:255',
            'landing_sections.*.description_en' => 'nullable|string',
            'landing_sections.*.title_ar' => 'nullable|string|max:255',
            'landing_sections.*.description_ar' => 'nullable|string',
            'landing_page_background_color' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'google_sheet_connection_id' => $this->validateGoogleSheetConnectionId(null),
        ]);

        $validated['user_id'] = auth()->id();
        $validated['store_id'] = $this->getActiveStoreId();
        $validated['google_sheet_connection_id'] = $validated['google_sheet_connection_id'] ?? null;
        $validated['theme'] = $validated['theme'] ?? 'theme1';
        $validated['theme_data'] = $request->input('theme_data');
        // Always auto-generate slug from name (ignore any submitted value)
        $validated['slug'] = \Str::slug($validated['name']) . '-' . time();
        $validated['is_active'] = $request->has('is_active');
        $validated['is_featured'] = $request->has('is_featured');
        $validated['has_variations'] = $hasVariations;
        $validated['has_promotions'] = $hasPromotions;

        // If has variations, stock/sku/price will be managed by variations
        if ($hasVariations) {
            $validated['stock'] = 0;
            $validated['sku'] = null;
            $validated['price'] = 0; // Set to 0 as it won't be used
        } else {
            $validated['stock'] = $validated['stock'] ?? 0;
        }
        
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $imagePaths[] = WebpImage::store($image, 'products');
            }
            $validated['images'] = $imagePaths;
        } elseif ($request->filled('preuploaded_images')) {
            // Images already uploaded via AJAX for description sync (already WebP)
            $validated['images'] = array_values(array_filter((array) $request->input('preuploaded_images')));
        }
        
        // Landing page sections removed from create UI — content lives in description
        $validated['landing_page_sections'] = [];
        
        if ($request->boolean('generate_landing_page')) {
            $validated['landing_page_status'] = 'pending';
        } else {
            $validated['landing_page_status'] = 'none';
        }

        if ($request->boolean('generate_product_images')) {
            $validated['ai_images_status'] = 'pending';
        } else {
            $validated['ai_images_status'] = 'none';
        }

        // Handle form fields configuration
        if ($request->has('form_fields')) {
            $formFieldsJson = $request->input('form_fields');
            $formFields = json_decode($formFieldsJson, true);

            // Validate and clean the form fields
            if (is_array($formFields)) {
                $validated['form_fields'] = LandingFormFields::cleanFields($formFields);
            }
        }
        
        $product = \App\Models\Product::create($validated);

        // Safety net: if create-form Quill sync failed, build description from uploaded images
        if (AiLandingPageService::isDescriptionEmpty($product->description)
            && !empty($product->images)
        ) {
            try {
                $aiService = new AiLandingPageService(auth()->user());
                $aiService->ensureProductDescriptionFromImages($product);
            } catch (\Throwable $e) {
                \Log::warning('Failed to backfill description on create for product ' . $product->id . ': ' . $e->getMessage());
            }
        }
        
        // Handle product variations
        if ($hasVariations && $request->has('variations')) {
            $hasDefault = false;
            
            foreach ($request->input('variations', []) as $variationData) {
                $attributes = [];
                if (!empty($variationData['attributes'])) {
                    foreach ($variationData['attributes'] as $attr) {
                        if (!empty($attr['name']) && !empty($attr['value'])) {
                            $attributes[$attr['name']] = $attr['value'];
                        }
                    }
                }
                
                $isDefault = isset($variationData['is_default']) && $variationData['is_default'];
                
                // Only one variation can be default
                if ($isDefault && $hasDefault) {
                    $isDefault = false;
                } elseif ($isDefault) {
                    $hasDefault = true;
                }
                
                \App\Models\ProductVariation::create([
                    'product_id' => $product->id,
                    'sku' => $variationData['sku'] ?? null,
                    'price' => $variationData['price'],
                    'compare_at_price' => $variationData['compare_at_price'] ?? null,
                    'stock' => $variationData['stock'] ?? 0,
                    'attributes' => $attributes,
                    'is_active' => isset($variationData['is_active']) && $variationData['is_active'],
                    'is_default' => $isDefault,
                ]);
            }
            
            // If no default was set, make the first variation default
            if (!$hasDefault) {
                $firstVariation = $product->variations()->first();
                if ($firstVariation) {
                    $firstVariation->update(['is_default' => true]);
                }
            }
        }

        // Handle product promotions
        if ($promotionsSubmitted) {
            foreach ($request->input('promotions', []) as $promotionData) {
                \App\Models\ProductPromotion::create([
                    'product_id' => $product->id,
                    'label' => $promotionData['label'] ?? null,
                    'min_quantity' => $promotionData['min_quantity'],
                    'max_quantity' => $promotionData['max_quantity'] ?? null,
                    'price' => $promotionData['price'],
                    'compare_at_price' => !empty($promotionData['compare_at_price']) ? $promotionData['compare_at_price'] : null,
                    'is_active' => true,
                ]);
            }
        }

        $jobsDispatched = [];
        
        if ($request->boolean('generate_landing_page')) {
            GenerateProductLandingPageJob::dispatch($product, auth()->id());
            $jobsDispatched[] = 'AI landing page';
        }

        if ($request->boolean('generate_product_images')) {
            \App\Jobs\GenerateProductImagesJob::dispatch($product, auth()->id(), 5);
            $jobsDispatched[] = 'AI product images';
        }
        
        if (!empty($jobsDispatched)) {
            $message = 'Product created successfully! ' . implode(' and ', $jobsDispatched) . ' generation started in the background.';
        } else {
            $message = 'Product created successfully!';
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'product_id' => $product->id,
                'redirect' => route('app.products'),
            ]);
        }
        
        return redirect()->route('app.products')->with('success', $message);
    }
    
    public function productsEdit($id)
    {
        $storeId = $this->getActiveStoreId();

        $product = \App\Models\Product::with(['variations', 'promotions'])
            ->where('user_id', auth()->id())
            ->when($storeId, function($q) use ($storeId) {
                $q->where('store_id', $storeId);
            })
            ->findOrFail($id);

        $categories = \App\Models\Category::where('is_active', true)
            ->when($storeId, function($q) use ($storeId) {
                $q->where('store_id', $storeId);
            })
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $store = $storeId
            ? \App\Models\Store::with('workspace')->find($storeId)
            : null;

        if (!$store && $product->store_id) {
            $store = \App\Models\Store::with('workspace')->find($product->store_id);
        } elseif ($store && !$store->relationLoaded('workspace')) {
            $store->load('workspace');
        }

        $workspaceLang = $store?->workspace?->getLanguage() ?? 'ar';
        $googleSheets = $this->workspaceGoogleSheets($store?->workspace_id);

        return view('customer.products-edit', compact('product', 'categories', 'googleSheets', 'store', 'workspaceLang'));
    }
    
    public function productsUpdate(Request $request, $id)
    {
        $storeId = $this->getActiveStoreId();

        $product = \App\Models\Product::where('user_id', auth()->id())
            ->when($storeId, function($q) use ($storeId) {
                $q->where('store_id', $storeId);
            })
            ->findOrFail($id);

        // Check if has_variations is enabled
        $hasVariations = $request->boolean('has_variations');
        $hasPromotions = $request->boolean('has_promotions');
        
        // Debug logging
        \Log::info('Product update - ID: ' . $id . ' - has_promotions: ' . ($hasPromotions ? 'true' : 'false'));
        \Log::info('Product update - ID: ' . $id . ' - promotions data: ' . json_encode($request->input('promotions')));
        \Log::info('Product update - ID: ' . $id . ' - promotions_json: ' . $request->input('promotions_json'));
        
        // Check for promotions - first try the regular array, then fallback to JSON field
        $promotionsData = $request->input('promotions');
        
        // If regular promotions array is empty but we have JSON data, use that instead
        if (empty($promotionsData) && $request->has('promotions_json')) {
            $jsonData = json_decode($request->input('promotions_json'), true);
            if (!empty($jsonData) && is_array($jsonData)) {
                $promotionsData = [];
                foreach ($jsonData as $index => $promo) {
                    $promotionsData[$index] = $promo;
                }
                // Merge back into request so validation works
                $request->merge(['promotions' => $promotionsData]);
                \Log::info('Product update - ID: ' . $id . ' - using promotions from JSON fallback: ' . json_encode($promotionsData));
            }
        }
        
        // Only require promotions array if has_promotions is true AND promotions were submitted
        $promotionsSubmitted = $hasPromotions && !empty($promotionsData);
        
        \Log::info('Product update - ID: ' . $id . ' - promotionsSubmitted: ' . ($promotionsSubmitted ? 'true' : 'false'));
        \Log::info('Product update - ID: ' . $id . ' - promotions count: ' . ($promotionsSubmitted ? count($promotionsData) : 0));

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => $hasVariations ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'images' => 'nullable|array',
            'images.*' => 'image|max:10240',
            'stock' => 'nullable|integer|min:0',
            'sku' => 'nullable|string|max:255',
            'has_variations' => 'nullable|boolean',
            'has_promotions' => 'nullable|boolean',
            'variations' => $hasVariations ? 'required|array|min:1' : 'nullable|array',
            'variations.*.id' => 'nullable|exists:product_variations,id',
            'variations.*.price' => 'required_with:variations|numeric|min:0',
            'variations.*.compare_at_price' => 'nullable|numeric|min:0',
            'variations.*.stock' => 'required_with:variations|integer|min:0',
            'variations.*.sku' => 'nullable|string|max:255',
            'variations.*.attributes' => 'nullable|array',
            'variations.*.is_default' => 'nullable|boolean',
            'variations.*.is_active' => 'nullable|boolean',
            'promotions' => 'nullable|array',
            'promotions.*.id' => 'nullable|exists:product_promotions,id',
            'promotions.*.label' => 'nullable|string|max:255',
            'promotions.*.min_quantity' => 'required_with:promotions|integer|min:1',
            'promotions.*.max_quantity' => 'nullable|integer|min:1',
            'promotions.*.price' => 'required_with:promotions|numeric|min:0',
            'promotions.*.compare_at_price' => 'nullable|numeric|min:0',
            'landing_sections' => 'nullable|array',
            'landing_sections.*.image' => 'nullable|image|max:10240',
            'landing_sections.*.existing_image' => 'nullable|string',
            'landing_sections.*.title_fr' => 'nullable|string|max:255',
            'landing_sections.*.description_fr' => 'nullable|string',
            'landing_sections.*.title_en' => 'nullable|string|max:255',
            'landing_sections.*.description_en' => 'nullable|string',
            'landing_sections.*.title_ar' => 'nullable|string|max:255',
            'landing_sections.*.description_ar' => 'nullable|string',
            'landing_page_background_color' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'testimonials' => 'nullable|array',
            'testimonials.*.name' => 'nullable|string|max:255',
            'google_sheet_connection_id' => $this->validateGoogleSheetConnectionId(null),
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['is_featured'] = $request->has('is_featured');
        $validated['has_variations'] = $hasVariations;
        $validated['has_promotions'] = $hasPromotions;
        $validated['google_sheet_connection_id'] = $validated['google_sheet_connection_id'] ?? null;

        // Testimonials are stored inside landing page JSON — strip from mass assignment
        $testimonialsInput = $validated['testimonials'] ?? null;
        unset($validated['testimonials']);

        // If has variations, stock/sku/price will be managed by variations
        if ($hasVariations) {
            $validated['stock'] = 0;
            $validated['sku'] = null;
            $validated['price'] = 0; // Set to 0 as it won't be used
        } else {
            $validated['stock'] = $validated['stock'] ?? 0;
        }
        
        if ($request->hasFile('images')) {
            $imagePaths = $product->images ?? [];
            foreach ($request->file('images') as $image) {
                $imagePaths[] = WebpImage::store($image, 'products');
            }
            $validated['images'] = $imagePaths;
        }

        // Landing page sections are no longer used
        $validated['landing_page_sections'] = [];
        
        // Handle form fields configuration
        if ($request->has('form_fields')) {
            $formFieldsJson = $request->input('form_fields');
            $formFields = json_decode($formFieldsJson, true);
            
            // Validate and clean the form fields
            if (is_array($formFields)) {
                $validated['form_fields'] = LandingFormFields::cleanFields($formFields);
            }
        }
        
        if ($request->has('delete_images')) {
            $currentImages = $product->images ?? [];
            $deleteImages = $request->input('delete_images', []);
            $validated['images'] = array_values(array_diff($currentImages, $deleteImages));
            
            foreach ($deleteImages as $image) {
                \Storage::disk('public')->delete($image);
            }
        }
        
        $product->update($validated);
        
        // Handle product variations
        if ($hasVariations && $request->has('variations')) {
            $submittedIds = [];
            $hasDefault = false;
            
            foreach ($request->input('variations', []) as $variationData) {
                $attributes = [];
                if (!empty($variationData['attributes'])) {
                    foreach ($variationData['attributes'] as $attr) {
                        if (!empty($attr['name']) && !empty($attr['value'])) {
                            $attributes[$attr['name']] = $attr['value'];
                        }
                    }
                }
                
                $isDefault = isset($variationData['is_default']) && $variationData['is_default'];
                
                if ($isDefault && $hasDefault) {
                    $isDefault = false;
                } elseif ($isDefault) {
                    $hasDefault = true;
                }
                
                $variationParams = [
                    'product_id' => $product->id,
                    'sku' => $variationData['sku'] ?? null,
                    'price' => $variationData['price'],
                    'compare_at_price' => $variationData['compare_at_price'] ?? null,
                    'stock' => $variationData['stock'] ?? 0,
                    'attributes' => $attributes,
                    'is_active' => isset($variationData['is_active']) && $variationData['is_active'],
                    'is_default' => $isDefault,
                ];
                
                if (!empty($variationData['id'])) {
                    $variation = \App\Models\ProductVariation::where('id', $variationData['id'])
                        ->where('product_id', $product->id)
                        ->first();
                    
                    if ($variation) {
                        $variation->update($variationParams);
                        $submittedIds[] = $variation->id;
                    }
                } else {
                    $variation = \App\Models\ProductVariation::create($variationParams);
                    $submittedIds[] = $variation->id;
                }
            }
            
            // Delete variations that were not submitted
            $product->variations()->whereNotIn('id', $submittedIds)->delete();
            
            // If no default was set, make the first variation default
            if (!$hasDefault) {
                $firstVariation = $product->variations()->first();
                if ($firstVariation) {
                    $firstVariation->update(['is_default' => true]);
                }
            }
        } else {
            // If variations are disabled, delete all existing variations
            $product->variations()->delete();
        }

        // Handle product promotions
        if ($hasPromotions) {
            if ($promotionsSubmitted) {
                $submittedPromotionIds = [];

                foreach ($request->input('promotions', []) as $promotionData) {
                    $promotionParams = [
                        'product_id' => $product->id,
                        'label' => $promotionData['label'] ?? null,
                        'min_quantity' => $promotionData['min_quantity'],
                        'max_quantity' => $promotionData['max_quantity'] ?? null,
                        'price' => $promotionData['price'],
                        'compare_at_price' => !empty($promotionData['compare_at_price']) ? $promotionData['compare_at_price'] : null,
                        'is_active' => true,
                    ];

                    if (!empty($promotionData['id'])) {
                        $promotion = \App\Models\ProductPromotion::where('id', $promotionData['id'])
                            ->where('product_id', $product->id)
                            ->first();

                        if ($promotion) {
                            $promotion->update($promotionParams);
                            $submittedPromotionIds[] = $promotion->id;
                        }
                    } else {
                        $promotion = \App\Models\ProductPromotion::create($promotionParams);
                        $submittedPromotionIds[] = $promotion->id;
                    }
                }

                // Delete promotions that were not submitted
                $product->promotions()->whereNotIn('id', $submittedPromotionIds)->delete();
            }
            // If has_promotions is true but no promotions submitted, keep existing promotions
        } else {
            // Only delete promotions if has_promotions checkbox is explicitly unchecked
            $product->promotions()->delete();
        }

        // Update reviewer names on landing page testimonials (keep each language's text/rating)
        if (is_array($testimonialsInput)) {
            $nameByIndex = [];
            foreach ($testimonialsInput as $index => $testimonial) {
                if (!is_array($testimonial)) {
                    continue;
                }
                $nameByIndex[(int) $index] = trim((string) ($testimonial['name'] ?? ''));
            }

            $landingDirty = false;
            foreach (['landing_page_ar', 'landing_page_fr', 'landing_page_en'] as $landingColumn) {
                $landingData = $product->{$landingColumn};
                if (!is_array($landingData) || empty($landingData['testimonials']) || !is_array($landingData['testimonials'])) {
                    continue;
                }

                $changed = false;
                foreach ($landingData['testimonials'] as $index => &$existing) {
                    if (!is_array($existing) || !array_key_exists($index, $nameByIndex)) {
                        continue;
                    }
                    if (($existing['name'] ?? '') !== $nameByIndex[$index]) {
                        $existing['name'] = $nameByIndex[$index];
                        $changed = true;
                    }
                }
                unset($existing);

                if ($changed) {
                    $product->{$landingColumn} = $landingData;
                    $landingDirty = true;
                }
            }

            if ($landingDirty) {
                $product->save();
            }
        }

        return redirect()->route('app.products')->with('success', 'Product updated successfully!');
    }
    
    public function productsDestroy($id)
    {
        $storeId = $this->getActiveStoreId();
        
        $product = \App\Models\Product::where('user_id', auth()->id())
            ->when($storeId, function($q) use ($storeId) {
                $q->where('store_id', $storeId);
            })
            ->findOrFail($id);
        
        // Delete product images from storage
        if ($product->images) {
            foreach ($product->images as $image) {
                \Storage::disk('public')->delete($image);
            }
        }
        
        // Delete AI generated images
        if ($product->ai_generated_images) {
            foreach ($product->ai_generated_images as $image) {
                $path = str_replace('/storage/', '', $image);
                \Storage::disk('public')->delete($path);
            }
        }
        
        $product->delete();
        
        return redirect()->route('app.products')->with('success', 'Product deleted successfully!');
    }

    public function generateLandingPage($productId)
    {
        $storeId = $this->getActiveStoreId();
        
        $product = \App\Models\Product::where('user_id', auth()->id())
            ->when($storeId, function($q) use ($storeId) {
                $q->where('store_id', $storeId);
            })
            ->findOrFail($productId);
        
        $product->update(['landing_page_status' => 'pending']);
        
        GenerateProductLandingPageJob::dispatch($product, auth()->id());
        
        return response()->json([
            'success' => true,
            'message' => 'Landing page generation started! It will be ready in a few moments.'
        ]);
    }

    /**
     * Generate AI titles + short descriptions for product images (used on create form).
     */
    public function generateImageCaptions(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'count' => 'required|integer|min:1|max:20',
        ]);

        try {
            $aiService = new AiLandingPageService(auth()->user());
            $captions = $aiService->generateImageCaptions(
                $validated['product_name'],
                $validated['category'] ?? 'General',
                (int) $validated['count']
            );

            return response()->json([
                'success' => true,
                'captions' => $captions,
            ]);
        } catch (\Throwable $e) {
            \Log::error('generateImageCaptions failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function generateProductImages($productId)
    {
        $storeId = $this->getActiveStoreId();
        
        $product = \App\Models\Product::where('user_id', auth()->id())
            ->when($storeId, function($q) use ($storeId) {
                $q->where('store_id', $storeId);
            })
            ->findOrFail($productId);
        
        $product->update([
            'ai_images_status' => 'pending',
            'ai_images_progress' => 0,
            'ai_images_generated' => 0,
        ]);
        
        \App\Jobs\GenerateProductImagesJob::dispatch($product, auth()->id(), 5);
        
        return response()->json([
            'success' => true,
            'message' => 'AI image generation started! This may take a few minutes.'
        ]);
    }

    public function checkImageGenerationProgress($productId)
    {
        $storeId = $this->getActiveStoreId();
        
        $product = \App\Models\Product::where('user_id', auth()->id())
            ->when($storeId, function($q) use ($storeId) {
                $q->where('store_id', $storeId);
            })
            ->findOrFail($productId);
        
        return response()->json([
            'status' => $product->ai_images_status,
            'progress' => $product->ai_images_progress,
            'generated' => $product->ai_images_generated,
            'total' => $product->ai_images_total,
            'images' => $product->ai_generated_images ?? [],
        ]);
    }

    public function landingPageBuilder($id)
    {
        $storeId = $this->getActiveStoreId();
        
        $product = \App\Models\Product::where('user_id', auth()->id())
            ->when($storeId, function($q) use ($storeId) {
                $q->where('store_id', $storeId);
            })
            ->findOrFail($id);
        
        $store = null;
        if ($storeId) {
            $store = \App\Models\Store::find($storeId);
        }
        
        return view('customer.products-landing-builder', compact('product', 'store'));
    }

    public function saveLandingPageBuilder(Request $request, $id)
    {
        $storeId = $this->getActiveStoreId();
        
        $product = \App\Models\Product::where('user_id', auth()->id())
            ->when($storeId, function($q) use ($storeId) {
                $q->where('store_id', $storeId);
            })
            ->findOrFail($id);
        
        $validated = $request->validate([
            'sections' => 'nullable|array',
            'sections.*.title_fr' => 'nullable|string|max:255',
            'sections.*.description_fr' => 'nullable|string',
            'sections.*.title_en' => 'nullable|string|max:255',
            'sections.*.description_en' => 'nullable|string',
            'sections.*.title_ar' => 'nullable|string|max:255',
            'sections.*.description_ar' => 'nullable|string',
            'sections.*.image' => 'nullable|string',
            'page_data' => 'nullable|array',
            'page_data.fr' => 'nullable|array',
            'page_data.en' => 'nullable|array',
            'page_data.ar' => 'nullable|array',
            'show_product_sections' => 'nullable|boolean',
            'landing_page_background_color' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);
        
        // Add show_product_sections to each language's page data
        $pageDataFr = $validated['page_data']['fr'] ?? [];
        $pageDataEn = $validated['page_data']['en'] ?? [];
        $pageDataAr = $validated['page_data']['ar'] ?? [];
        
        $showSections = $validated['show_product_sections'] ?? true;
        $pageDataFr['show_product_sections'] = $showSections;
        $pageDataEn['show_product_sections'] = $showSections;
        $pageDataAr['show_product_sections'] = $showSections;
        
        $updatePayload = [
            'landing_page_sections' => $validated['sections'] ?? [],
            'landing_page_fr' => $pageDataFr,
            'landing_page_en' => $pageDataEn,
            'landing_page_ar' => $pageDataAr,
        ];

        if (!empty($validated['landing_page_background_color'])) {
            $updatePayload['landing_page_background_color'] = strtolower($validated['landing_page_background_color']);
        }

        $product->update($updatePayload);
        
        return response()->json([
            'success' => true,
            'message' => 'Landing page updated successfully!'
        ]);
    }

    public function uploadQuillImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:10240'
        ]);
        
        $path = WebpImage::store($request->file('image'), 'products');
        $url = \Storage::url($path);
        
        return response()->json([
            'success' => true,
            'url' => $url,
            'path' => $path
        ]);
    }

    public function uploadProductImage(Request $request, $id)
    {
        $storeId = $this->getActiveStoreId();
        
        $product = \App\Models\Product::where('user_id', auth()->id())
            ->when($storeId, function($q) use ($storeId) {
                $q->where('store_id', $storeId);
            })
            ->findOrFail($id);
        
        $request->validate([
            'image' => 'required|image|max:10240'
        ]);
        
        $path = WebpImage::store($request->file('image'), 'products/landing-sections');
        $url = \Storage::url($path);
        
        return response()->json([
            'success' => true,
            'url' => $url,
            'path' => $path
        ]);
    }

    public function setMainImage(Request $request, $id)
    {
        $storeId = $this->getActiveStoreId();
        
        $product = \App\Models\Product::where('user_id', auth()->id())
            ->when($storeId, function($q) use ($storeId) {
                $q->where('store_id', $storeId);
            })
            ->findOrFail($id);
        
        $validated = $request->validate([
            'image_path' => 'required|string'
        ]);
        
        $images = $product->images ?? [];
        $imagePath = $validated['image_path'];
        
        // Remove from current position and add to front
        $images = array_values(array_diff($images, [$imagePath]));
        array_unshift($images, $imagePath);
        
        $product->update(['images' => $images]);
        
        return response()->json([
            'success' => true,
            'message' => 'Main image updated successfully!'
        ]);
    }

    public function updateImageDescription(Request $request, $id)
    {
        $storeId = $this->getActiveStoreId();
        
        $product = \App\Models\Product::where('user_id', auth()->id())
            ->when($storeId, function($q) use ($storeId) {
                $q->where('store_id', $storeId);
            })
            ->findOrFail($id);
        
        $validated = $request->validate([
            'image_path' => 'required|string',
            'description_fr' => 'nullable|string',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
        ]);
        
        $imageDescriptions = $product->image_descriptions ?? [];
        $imageDescriptions[$validated['image_path']] = [
            'fr' => $validated['description_fr'] ?? '',
            'en' => $validated['description_en'] ?? '',
            'ar' => $validated['description_ar'] ?? '',
        ];
        
        $product->update(['image_descriptions' => $imageDescriptions]);
        
        return response()->json([
            'success' => true,
            'message' => 'Image description updated successfully!'
        ]);
    }
    
    public function campaigns()
    {
        return view('customer.campaigns');
    }

    public function leads()
    {
        $user = auth()->user();
        $storeId = $this->getActiveStoreId();
        
        $leads = \App\Models\ProductLead::with(['product', 'promotion', 'variation'])
            ->where('user_id', $user->id)
            ->when($storeId, function($q) use ($storeId) {
                $q->whereHas('product', function($q) use ($storeId) {
                    $q->where('store_id', $storeId);
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $blockedIpAddresses = $this->getBlockedIpAddresses();
        
        return view('customer.leads', compact('leads', 'blockedIpAddresses'));
    }
    
    public function categories()
    {
        $storeId = $this->getActiveStoreId();
        
        $query = \App\Models\Category::orderBy('order')->orderBy('name');
        
        if ($storeId) {
            $query->where('store_id', $storeId);
        }
        
        $categories = $query->get();
        return view('customer.categories', compact('categories'));
    }
    
    public function categoriesStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        
        $validated['slug'] = \Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');
        $validated['store_id'] = $this->getActiveStoreId();
        
        \App\Models\Category::create($validated);
        
        return redirect()->route('app.categories')->with('success', 'Category created successfully!');
    }
    
    public function categoriesUpdate(Request $request, $id)
    {
        $storeId = $this->getActiveStoreId();
        
        $category = \App\Models\Category::when($storeId, function($q) use ($storeId) {
                $q->where('store_id', $storeId);
            })
            ->findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        
        $validated['slug'] = \Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');
        
        $category->update($validated);
        
        return redirect()->route('app.categories')->with('success', 'Category updated successfully!');
    }
    
    public function categoriesDestroy($id)
    {
        $storeId = $this->getActiveStoreId();
        
        $category = \App\Models\Category::when($storeId, function($q) use ($storeId) {
                $q->where('store_id', $storeId);
            })
            ->findOrFail($id);
        
        // Check if category has products
        if ($category->products()->count() > 0) {
            return redirect()->route('app.categories')->with('error', 'Cannot delete category with associated products.');
        }
        
        $category->delete();
        
        return redirect()->route('app.categories')->with('success', 'Category deleted successfully!');
    }

    public function externalApiSettings()
    {
        $user = auth()->user();
        return view('customer.external-api-settings', compact('user'));
    }

    public function saveExternalApiSettings(Request $request)
    {
        $validated = $request->validate([
            'external_api_url' => 'nullable|url|max:500',
            'external_api_key' => 'nullable|string|max:2048',
            'external_api_enabled' => 'nullable|boolean',
            'clear_external_api_key' => 'nullable|boolean',
        ]);

        $user = $request->user();
        
        $user->external_api_url = $validated['external_api_url'] ?? null;
        $user->external_api_enabled = $request->boolean('external_api_enabled');

        if ($request->boolean('clear_external_api_key')) {
            $user->external_api_key_encrypted = null;
        } elseif (!empty($validated['external_api_key'])) {
            $user->external_api_key_encrypted = \Crypt::encryptString(trim($validated['external_api_key']));
        }

        $user->save();

        return redirect()
            ->route('app.external-api-settings')
            ->with('success', 'External API settings saved successfully!');
    }

    public function testExternalApiConnection(Request $request)
    {
        $user = $request->user();
        
        if (!$user->external_api_enabled || !$user->external_api_url || !$user->external_api_key_encrypted) {
            return redirect()
                ->route('app.external-api-settings')
                ->with('error', 'Please configure all API settings before testing.');
        }

        $apiService = new \App\Services\ExternalApiService($user);
        $result = $apiService->testConnection();

        if ($result['success']) {
            return redirect()
                ->route('app.external-api-settings')
                ->with('success', 'Connection successful! Your API is working correctly.');
        }

        return redirect()
            ->route('app.external-api-settings')
            ->with('error', 'Connection failed: ' . $result['message']);
    }
}
