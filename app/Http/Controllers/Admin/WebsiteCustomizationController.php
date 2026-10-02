<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebsiteSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WebsiteCustomizationController extends Controller
{
    protected function getActiveStoreId()
    {
        return session('active_store_id');
    }

    public function index()
    {
        $storeId = $this->getActiveStoreId();
        $settings = WebsiteSettings::getSettings(auth()->id(), $storeId);
        return view('customer.website-customization', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string',
            'hero_title' => 'nullable|string|max:255',
            'hero_subtitle' => 'nullable|string',
            'hero_button_text' => 'nullable|string|max:100',
            'hero_button_link' => 'nullable|string|max:255',
            'hero_background_color' => 'nullable|string|max:7',
            'hero_background_image' => 'nullable|image|max:5120',
            'show_top_banner' => 'boolean',
            'banner_text' => 'nullable|string|max:255',
            'banner_icon' => 'nullable|string|max:50',
            'banner_bg_color' => 'nullable|string|max:7',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'accent_color' => 'nullable|string|max:7',
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:255',
            'contact_address' => 'nullable|string',
            'whatsapp_number' => 'nullable|string|max:50',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'footer_about' => 'nullable|string',
            'footer_copyright' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'site_logo' => 'nullable|image|max:2048',
            'site_favicon' => 'nullable|image|max:1024',
        ]);

        $storeId = $this->getActiveStoreId();
        $settings = WebsiteSettings::getSettings(auth()->id(), $storeId);

        // Remove file upload fields from validated data to handle them separately
        unset($validated['site_logo']);
        unset($validated['site_favicon']);
        unset($validated['hero_background_image']);

        // Handle file uploads
        if ($request->hasFile('site_logo')) {
            if ($settings->site_logo) {
                Storage::disk('public')->delete($settings->site_logo);
            }
            $settings->site_logo = $request->file('site_logo')->store('website', 'public');
        }

        if ($request->hasFile('site_favicon')) {
            if ($settings->site_favicon) {
                Storage::disk('public')->delete($settings->site_favicon);
            }
            $settings->site_favicon = $request->file('site_favicon')->store('website', 'public');
        }

        // Handle hero background image removal first
        if ($request->input('remove_hero_background_image')) {
            if ($settings->hero_background_image) {
                Storage::disk('public')->delete($settings->hero_background_image);
            }
            $settings->hero_background_image = null;
        }
        // Handle hero background image upload
        elseif ($request->hasFile('hero_background_image')) {
            if ($settings->hero_background_image) {
                Storage::disk('public')->delete($settings->hero_background_image);
            }
            $settings->hero_background_image = $request->file('hero_background_image')->store('website/hero', 'public');
        }

        // Handle features
        if ($request->has('features')) {
            $features = [];
            foreach ($request->features as $index => $feature) {
                if (!empty($feature['title'])) {
                    $features[] = [
                        'icon' => $feature['icon'] ?? 'star',
                        'title' => $feature['title'],
                        'color' => $feature['color'] ?? '#10b981',
                    ];
                }
            }
            $validated['features'] = $features;
        }

        // Update settings with validated data
        $settings->update($validated);
        
        // Save any file uploads that were handled separately
        $settings->save();

        return back()->with('success', 'Website settings updated successfully!');
    }

    public function preview()
    {
        $storeId = $this->getActiveStoreId();
        $settings = WebsiteSettings::getSettings(auth()->id(), $storeId);
        
        // Get the same data as the home page - filtered by store
        $query = \App\Models\Product::with('category')
            ->where('is_active', true)
            ->when($storeId, function($q) use ($storeId) {
                $q->where('store_id', $storeId);
            });
            
        $products = $query->orderBy('order')->orderBy('created_at', 'desc')->paginate(12);
        
        $categories = \App\Models\Category::where('is_active', true)
            ->when($storeId, function($q) use ($storeId) {
                $q->where('store_id', $storeId);
            })
            ->orderBy('order')
            ->get();
            
        $featuredProducts = \App\Models\Product::where('is_active', true)
            ->where('is_featured', true)
            ->when($storeId, function($q) use ($storeId) {
                $q->where('store_id', $storeId);
            })
            ->limit(8)
            ->get();
        
        // Use the real store so workspace language/currency apply in preview
        $store = $storeId
            ? \App\Models\Store::with('workspace')->find($storeId)
            : null;

        if (!$store) {
            $store = new \stdClass();
            $store->subdomain = 'preview';
            $store->facebook_pixel_enabled = false;
            $store->facebook_pixel_id = null;
            $store->tiktok_pixel_enabled = false;
            $store->tiktok_pixel_id = null;
            $store->workspace = null;
        }

        $workspace = $store->workspace ?? (session('active_workspace_id')
            ? \App\Models\Workspace::find(session('active_workspace_id'))
            : null);

        return view('welcome', compact('products', 'categories', 'featuredProducts', 'settings', 'store', 'workspace'))
            ->with('isPreview', true);
    }
}
