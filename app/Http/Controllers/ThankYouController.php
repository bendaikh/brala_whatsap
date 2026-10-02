<?php

namespace App\Http\Controllers;

use App\Models\ProductLead;
use App\Support\StoreDomain;
use Illuminate\Http\Request;

class ThankYouController extends Controller
{
    public function show(Request $request)
    {
        $leadId = session('completed_order_id');

        if (!$leadId) {
            return $this->redirectWithoutOrder($request);
        }

        $lead = ProductLead::with(['product.store', 'promotion', 'variation'])
            ->find($leadId);

        if (!$lead || !$lead->product) {
            session()->forget(['completed_order_id', 'pending_conversion_tracking']);

            return $this->redirectWithoutOrder($request);
        }

        $store = $lead->product->store;
        $store->loadMissing(['activeFacebookPixels', 'workspace']);

        $resolvedStore = $request->attributes->get('resolved_store');
        if ($resolvedStore && $resolvedStore->id !== $store->id) {
            abort(404);
        }

        $trackConversion = (bool) session()->pull('pending_conversion_tracking', false);
        $workspace = $store->workspace;

        return view('thank-you', compact('store', 'lead', 'trackConversion', 'workspace'));
    }

    private function redirectWithoutOrder(Request $request)
    {
        $resolvedStore = $request->attributes->get('resolved_store');

        if ($resolvedStore) {
            return redirect(StoreDomain::homeUrl($resolvedStore));
        }

        return redirect()->route('coming-soon');
    }
}
