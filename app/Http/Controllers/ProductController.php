<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductLead;
use App\Models\Store;
use App\Support\LandingFormFields;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function customDomainHome(Request $request)
    {
        $store = $this->resolvedStore($request);

        return $this->renderStoreHome($store, $request);
    }

    public function customDomainShow(Request $request, $slug)
    {
        $store = $this->resolvedStore($request);

        return $this->renderProductShow($store, $slug);
    }

    public function customDomainSubmitLead(Request $request, $slug)
    {
        $store = $this->resolvedStore($request);

        return $this->processLeadSubmission($request, $store, $slug);
    }

    public function index($subdomain, Request $request)
    {
        $store = $this->storeFromSubdomain($subdomain);

        return $this->renderStoreHome($store, $request);
    }

    public function show($subdomain, $slug)
    {
        $store = $this->storeFromSubdomain($subdomain);

        return $this->renderProductShow($store, $slug);
    }

    public function submitLead(Request $request, $subdomain, $slug)
    {
        $store = $this->storeFromSubdomain($subdomain);

        return $this->processLeadSubmission($request, $store, $slug);
    }

    private function resolvedStore(Request $request): Store
    {
        $store = $request->attributes->get('resolved_store');

        if (!$store) {
            abort(404);
        }

        return $store->load('activeFacebookPixels');
    }

    private function storeFromSubdomain(string $subdomain): Store
    {
        return Store::where('subdomain', $subdomain)
            ->where('is_active', true)
            ->with('activeFacebookPixels')
            ->firstOrFail();
    }

    private function renderStoreHome(Store $store, Request $request)
    {
        $settings = \App\Models\WebsiteSettings::getSettings($store->user_id, $store->id);

        $query = Product::with('category')
            ->where('is_active', true)
            ->where('store_id', $store->id);

        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->orderBy('order')->orderBy('created_at', 'desc')->paginate(12);
        $categories = Category::where('is_active', true)
            ->where('store_id', $store->id)
            ->orderBy('order')
            ->get();
        $featuredProducts = Product::where('is_active', true)
            ->where('is_featured', true)
            ->where('store_id', $store->id)
            ->limit(8)
            ->get();

        $store->loadMissing('workspace');
        $workspace = $store->workspace;

        return view('welcome', compact('products', 'categories', 'featuredProducts', 'settings', 'store', 'workspace'));
    }

    private function renderProductShow(Store $store, string $slug)
    {
        $store->loadMissing('workspace');

        $product = Product::with(['activeVariations', 'activePromotions'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->where('store_id', $store->id)
            ->firstOrFail();
            
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->where('store_id', $store->id)
            ->limit(4)
            ->get();

        $settings = \App\Models\WebsiteSettings::getSettings($store->user_id, $store->id);
        $workspace = $store->workspace;

        if ($product->landing_page_fr || $product->landing_page_en || $product->landing_page_ar) {
            return view('product-landing', compact('product', 'relatedProducts', 'store', 'settings', 'workspace'));
        }

        return view('product-detail', compact('product', 'relatedProducts', 'store', 'settings', 'workspace'));
    }

    private function processLeadSubmission(Request $request, Store $store, string $slug)
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->where('store_id', $store->id)
            ->firstOrFail();

        // Get form fields - use product's custom fields or defaults
        $formFields = $product->form_fields ?? [
            ['id' => 'name', 'type' => 'text', 'required' => true],
            ['id' => 'phone', 'type' => 'tel', 'required' => true],
            ['id' => 'note', 'type' => 'textarea', 'required' => false],
        ];

        // Build validation rules dynamically based on form fields
        $validationRules = [
            'language' => 'required|in:fr,en,ar',
        ];

        // Track which fields are standard vs custom
        $standardFields = ['name', 'phone', 'note'];
        $systemFieldIds = LandingFormFields::systemFieldIds();
        $customFieldsData = [];

        foreach ($formFields as $field) {
            $fieldId = $field['id'];
            
            // Build validation rule
            $rules = [];
            if ($field['required'] ?? false) {
                $rules[] = 'required';
            } else {
                $rules[] = 'nullable';
            }
            
            // Add type-specific validation
            switch ($field['type'] ?? 'text') {
                case 'email':
                    $rules[] = 'email';
                    $rules[] = 'max:255';
                    break;
                case 'tel':
                    $rules[] = 'string';
                    $rules[] = 'max:20';
                    break;
                case 'number':
                    $rules[] = 'numeric';
                    break;
                case 'textarea':
                    $rules[] = 'string';
                    $rules[] = 'max:1000';
                    break;
                default:
                    $rules[] = 'string';
                    $rules[] = 'max:255';
            }
            
            $validationRules[$fieldId] = implode('|', $rules);
        }

        // Ensure standard fields have fallback rules if not in form_fields
        if (!isset($validationRules['name'])) {
            $validationRules['name'] = 'nullable|string|max:255';
        }
        if (!isset($validationRules['phone'])) {
            $validationRules['phone'] = 'nullable|string|max:20';
        }
        if (!isset($validationRules['note'])) {
            $validationRules['note'] = 'nullable|string|max:1000';
        }

        // Add validation for order detail fields
        $validationRules['selected_promotion_id'] = 'nullable|integer|exists:product_promotions,id';
        $validationRules['selected_variation_id'] = 'nullable|integer|exists:product_variations,id';
        $validationRules['selected_price'] = 'nullable|numeric|min:0';

        $validated = $request->validate($validationRules);

        // Extract custom fields (fields that aren't standard)
        $orderDetailFields = ['selected_promotion_id', 'selected_variation_id', 'selected_price'];
        $excludedFromCustom = array_merge(
            $standardFields,
            $systemFieldIds,
            $orderDetailFields,
            ['language']
        );

        foreach ($validated as $key => $value) {
            if (!in_array($key, $excludedFromCustom, true) && $value !== null) {
                $customFieldsData[$key] = $value;
            }
        }

        $locationData = LandingFormFields::extractLocationData($formFields, $validated);
        $city = $locationData['city'];
        $address = $locationData['address'];

        // Only accept a promotion / variation that belongs to this product.
        $promotion = !empty($validated['selected_promotion_id'])
            ? \App\Models\ProductPromotion::where('product_id', $product->id)
                ->where('is_active', true)
                ->find($validated['selected_promotion_id'])
            : null;
        $variation = !empty($validated['selected_variation_id'])
            ? \App\Models\ProductVariation::where('product_id', $product->id)
                ->find($validated['selected_variation_id'])
            : null;
        $validated['selected_promotion_id'] = $promotion?->id;
        $validated['selected_variation_id'] = $variation?->id;

        // Unit price is resolved server-side from the chosen promotion / variation
        // (or the product price) instead of trusting the submitted selected_price.
        // Quantity is derived from the promotion: ProductLead::order_quantity.
        if ($promotion) {
            $selectedPrice = $promotion->price;
        } elseif ($variation) {
            $selectedPrice = $variation->price;
        } else {
            $selectedPrice = $product->price;
        }

        $phone = $validated['phone'] ?? null;
        $dedupeToken = md5(($phone ?: '') . '|' . ($request->ip() ?: '') . '|' . $product->id);
        $lock = Cache::lock('lead-submit:' . $dedupeToken, 15);
        $lockAcquired = $lock->get();

        if (!$lockAcquired) {
            // Identical submit already in progress — wait briefly and reuse that order.
            usleep(400000);
            $existingLead = $this->findRecentDuplicateLead($product->id, $phone, $request->ip());
            if ($existingLead) {
                return $this->redirectAfterLead($existingLead);
            }

            // Last attempt to take the lock before creating.
            $lockAcquired = $lock->get();
        }

        try {
            $existingLead = $this->findRecentDuplicateLead($product->id, $phone, $request->ip());
            if ($existingLead) {
                return $this->redirectAfterLead($existingLead);
            }

            $lead = ProductLead::create([
                'product_id' => $product->id,
                'selected_promotion_id' => $validated['selected_promotion_id'] ?? null,
                'selected_variation_id' => $validated['selected_variation_id'] ?? null,
                'selected_price' => $selectedPrice,
                'user_id' => $product->user_id,
                'name' => $validated['name'] ?? null,
                'phone' => $phone,
                'city' => $city,
                'address' => $address,
                'note' => $validated['note'] ?? null,
                'custom_fields' => !empty($customFieldsData) ? $customFieldsData : null,
                'language' => $validated['language'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'pending',
            ]);

            \App\Jobs\PushOrderToExternalApi::dispatch($lead);
            \App\Jobs\PushOrderToGoogleSheet::dispatch($lead);

            return $this->redirectAfterLead($lead);
        } finally {
            if ($lockAcquired) {
                $lock->release();
            }
        }
    }

    private function findRecentDuplicateLead(int $productId, ?string $phone, ?string $ipAddress): ?ProductLead
    {
        $query = ProductLead::query()
            ->where('product_id', $productId)
            ->where('created_at', '>=', now()->subSeconds(60));

        if ($phone) {
            $query->where('phone', $phone);
        } else {
            $query->where('ip_address', $ipAddress);
        }

        return $query->latest('id')->first();
    }

    private function redirectAfterLead(ProductLead $lead)
    {
        session([
            'completed_order_id' => $lead->id,
            'pending_conversion_tracking' => true,
        ]);

        return redirect()->route('thank-you');
    }
}
