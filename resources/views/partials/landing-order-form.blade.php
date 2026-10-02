{{--
    Landing page COD order form (single card).

    Contains, in order: short title, price (or quantity offers), variations,
    the merchant's form fields, the total and the submit button.

    Expects: $product, $store, $lpLang, $lpIsRtl, $lpCurrencySymbol, $errors.

    Pricing rules (unchanged from the previous form):
      - selected_price         = UNIT price of the chosen offer / variation
      - selected_promotion_id  = chosen quantity offer (quantity = promotion->min_quantity,
                                 see ProductLead::getOrderQuantityAttribute)
      - selected_variation_id  = chosen variation
    Total shown to the customer = unit price x quantity.
--}}
@php
    $lpFmt = function ($price) {
        $price = (float) $price;
        return $price == floor($price)
            ? number_format($price, 0)
            : rtrim(rtrim(number_format($price, 2), '0'), '.');
    };

    $formCopy = match ($lpLang) {
        'fr' => [
            'title' => 'Pour commander, remplissez le formulaire',
            'offers' => 'Choisissez votre offre',
            'options' => 'Choisissez une option',
            'total' => 'Total',
            'save' => 'Économisez',
            'errors' => 'Veuillez corriger les erreurs suivantes :',
            'submit' => 'Envoyer',
            'submitting' => 'Envoi en cours...',
            'choose' => 'Choisir...',
            'field' => 'Champ',
            'defaults' => [
                ['id' => 'name', 'type' => 'text', 'label' => 'Nom', 'placeholder' => 'Votre nom', 'required' => true],
                ['id' => 'phone', 'type' => 'tel', 'label' => 'Téléphone', 'placeholder' => 'Votre téléphone', 'required' => true],
                ['id' => 'note', 'type' => 'textarea', 'label' => 'Notes', 'placeholder' => 'Notes (optionnel)', 'required' => false],
            ],
        ],
        'en' => [
            'title' => 'To order, fill in the form',
            'offers' => 'Choose your offer',
            'options' => 'Choose an option',
            'total' => 'Total',
            'save' => 'Save',
            'errors' => 'Please correct the following errors:',
            'submit' => 'Submit',
            'submitting' => 'Submitting...',
            'choose' => 'Choose...',
            'field' => 'Field',
            'defaults' => [
                ['id' => 'name', 'type' => 'text', 'label' => 'Name', 'placeholder' => 'Your name', 'required' => true],
                ['id' => 'phone', 'type' => 'tel', 'label' => 'Phone', 'placeholder' => 'Your phone', 'required' => true],
                ['id' => 'note', 'type' => 'textarea', 'label' => 'Notes', 'placeholder' => 'Notes (optional)', 'required' => false],
            ],
        ],
        default => [
            'title' => 'للطلب، املأ الاستمارة',
            'offers' => 'اختر العرض ديالك',
            'options' => 'اختر الخيار',
            'total' => 'المجموع',
            'save' => 'وفّر',
            'errors' => 'يرجى تصحيح الأخطاء التالية:',
            'submit' => 'إرسال',
            'submitting' => 'جاري الإرسال...',
            'choose' => 'اختر...',
            'field' => 'حقل',
            'defaults' => [
                ['id' => 'name', 'type' => 'text', 'label' => 'الاسم', 'placeholder' => 'الاسم', 'required' => true],
                ['id' => 'phone', 'type' => 'tel', 'label' => 'الهاتف', 'placeholder' => 'الهاتف', 'required' => true],
                ['id' => 'note', 'type' => 'textarea', 'label' => 'ملاحظات', 'placeholder' => 'ملاحظات', 'required' => false],
            ],
        ],
    };

    $lpQtyLabel = function (int $qty) use ($lpLang): string {
        return match ($lpLang) {
            'fr' => $qty <= 1 ? '1 pièce' : $qty . ' pièces',
            'en' => $qty <= 1 ? '1 item' : $qty . ' items',
            default => match (true) {
                $qty <= 1 => 'قطعة واحدة',
                $qty === 2 => 'قطعتان',
                $qty <= 10 => $qty . ' قطع',
                default => $qty . ' قطعة',
            },
        };
    };

    $formAlign = $lpIsRtl ? 'text-right' : 'text-left';

    $hasVariations = $product->has_variations && $product->activeVariations->isNotEmpty();
    $hasPromotions = $product->has_promotions && $product->activePromotions->isNotEmpty();

    $defaultVariation = $hasVariations
        ? ($product->activeVariations->firstWhere('is_default', true) ?? $product->activeVariations->first())
        : null;

    // Unit price / compare-at price of "1 piece" (default variation or product).
    $baseUnit = (float) ($defaultVariation?->price ?? $product->price);
    $baseCompare = (float) ($defaultVariation
        ? ($defaultVariation->compare_at_price ?? 0)
        : ($product->compare_at_price ?? 0));
    // Reference unit price used to compute savings on promotions
    // (same rule as ProductPromotion::discount_percentage).
    $refUnit = $baseCompare > $baseUnit ? $baseCompare : $baseUnit;

    // Build quantity offers. A "1 piece" offer at the normal price is added
    // automatically unless the merchant already defined a quantity-1 promotion.
    $offers = [];
    if ($hasPromotions) {
        $promotionsForDefault = $product->activePromotions->filter(
            fn ($p) => !$p->product_variation_id || $p->product_variation_id == $defaultVariation?->id
        );
        $hasQtyOne = $promotionsForDefault->contains(fn ($p) => (int) $p->min_quantity <= 1);

        $offers[] = [
            'synthetic' => true,
            'hidden' => $hasQtyOne,
            'promotion_id' => '',
            'variation_id' => '',
            'qty' => 1,
            'label' => null,
            'unit' => $baseUnit,
            'compare' => $baseCompare > $baseUnit ? $baseCompare : 0,
            'promo_compare' => 0,
        ];

        foreach ($product->activePromotions as $promotion) {
            $qty = max(1, (int) $promotion->min_quantity);
            $promoCompare = (float) ($promotion->compare_at_price ?? 0);
            $unit = (float) $promotion->price;
            $cmp = $promoCompare > 0 ? $promoCompare : $refUnit;
            $offers[] = [
                'synthetic' => false,
                'hidden' => $promotion->product_variation_id && $promotion->product_variation_id != $defaultVariation?->id,
                'promotion_id' => $promotion->id,
                'variation_id' => $promotion->product_variation_id ?: '',
                'qty' => $qty,
                'label' => $promotion->label,
                'unit' => $unit,
                'compare' => $cmp > $unit ? $cmp : 0,
                'promo_compare' => $promoCompare,
            ];
        }
    }
    $selectedOffer = collect($offers)->first(fn ($o) => !$o['hidden']);

    $initialUnit = $selectedOffer['unit'] ?? $baseUnit;
    $initialQty = $selectedOffer['qty'] ?? 1;

    $formFields = $product->form_fields ?? $formCopy['defaults'];
@endphp

<style>
    .lp-order-section { padding:1rem 0 3rem; }
    @media (min-width: 1024px) { .lp-order-section { padding:1.5rem 0 4rem; } }
    .lp-form-title { font-size:1.125rem; }
    @media (min-width: 640px) { .lp-form-title { font-size:1.5rem; } }
    .lp-choice { display:flex; align-items:center; gap:0.75rem; padding:0.85rem 1rem; border:2px solid #e5e7eb; border-radius:0.9rem; background:#f9fafb; cursor:pointer; transition:border-color .15s, background-color .15s, box-shadow .15s; }
    .lp-choice:hover { border-color:#93c5fd; }
    .lp-choice.is-selected { border-color:#2563eb; background:#eff6ff; box-shadow:0 0 0 3px rgba(37,99,235,.12); }
    .lp-choice[hidden] { display:none; }
    .lp-choice input[type=radio] { width:1.25rem; height:1.25rem; accent-color:#2563eb; flex-shrink:0; }
    .lp-offer__qty { font-weight:900; color:#111827; font-size:1.05rem; line-height:1.2; }
    .lp-offer__label { display:inline-block; margin-top:0.2rem; font-size:0.75rem; font-weight:700; color:#92400e; background:#fef3c7; border-radius:999px; padding:0.1rem 0.55rem; white-space:nowrap; }
    .lp-offer__prices { margin-inline-start:auto; text-align:end; flex-shrink:0; }
    .lp-offer__total { font-weight:900; color:#1e3a8a; font-size:1.2rem; white-space:nowrap; line-height:1.2; }
    .lp-offer__total small, .lp-total small { font-size:0.72em; font-weight:700; }
    .lp-offer__compare { font-size:0.8rem; color:#9ca3af; text-decoration:line-through; white-space:nowrap; }
    .lp-offer__save { display:inline-block; margin-top:0.15rem; font-size:0.72rem; font-weight:800; color:#065f46; background:#d1fae5; border-radius:999px; padding:0.1rem 0.5rem; white-space:nowrap; }
    .lp-total { display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:0.9rem 1.1rem; border-radius:0.9rem; background:#111827; color:#fff; }
    .lp-total__label { font-weight:700; font-size:1rem; }
    .lp-total__label span { font-weight:500; opacity:.75; font-size:0.85rem; }
    .lp-total__value { font-weight:900; font-size:1.5rem; white-space:nowrap; }
</style>

<!-- Order Form Section (directly below the description) -->
<section class="landing-order-band lp-order-section" id="order-section">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-xl mx-auto">
            <div id="order-form" class="bg-white rounded-2xl p-5 sm:p-8 shadow-2xl shadow-blue-950/30 ring-1 ring-black/5 scroll-mt-6" @if($lpIsRtl) dir="rtl" @endif>
                <h2 class="lp-form-title font-black mb-4 text-gray-900 text-center leading-snug" style="text-wrap: balance;{{ $lpIsRtl ? " font-family: 'Tajawal', 'Cairo', sans-serif;" : '' }}">
                    {{ $formCopy['title'] }}
                </h2>

                @if ($errors->any())
                <div class="mb-4 rounded-xl border-2 border-red-500 bg-red-50 px-4 py-3 text-red-800">
                    <p class="font-semibold mb-2 text-center">{{ $formCopy['errors'] }}</p>
                    <ul class="list-disc list-inside text-sm {{ $formAlign }}">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form id="landing-order-form" method="POST"
                      action="{{ \App\Support\StoreDomain::submitLeadUrl($store, $product->slug) }}"
                      class="space-y-5"
                      data-base-price="{{ $product->price }}"
                      data-base-compare="{{ $product->compare_at_price ?? 0 }}"
                      data-currency="{{ $lpCurrencySymbol }}"
                      data-save-label="{{ $formCopy['save'] }}">
                    @csrf
                    <input type="hidden" name="language" value="{{ $lpLang }}">

                    {{-- Order details submitted with the lead (kept in sync by the script below) --}}
                    @if($hasPromotions)
                        <input type="hidden" name="selected_promotion_id" id="selected_promotion_id" value="{{ $selectedOffer['promotion_id'] ?? '' }}">
                    @endif
                    @if($hasVariations)
                        <input type="hidden" name="selected_variation_id" id="selected_variation_id" value="{{ $defaultVariation->id }}">
                    @endif
                    <input type="hidden" name="selected_price" id="selected_price" value="{{ $initialUnit }}">

                    {{-- Price (only when there are no quantity offers; offers carry their own prices) --}}
                    @unless($hasPromotions)
                    <div class="flex flex-wrap items-center justify-center gap-3" id="lp-price-block">
                        <div class="text-4xl font-black text-blue-900 leading-none">
                            <span id="lp-price-main">{{ $lpFmt($baseUnit) }}</span>
                            <span class="text-xl font-bold">{{ $lpCurrencySymbol }}</span>
                        </div>
                        <div id="lp-price-compare" class="text-lg line-through text-gray-400" @if($baseCompare <= $baseUnit) hidden @endif>
                            {{ $lpFmt($baseCompare) }} {{ $lpCurrencySymbol }}
                        </div>
                        <div id="lp-price-discount" class="bg-gradient-to-l from-yellow-300 to-amber-400 text-blue-950 px-3 py-1 rounded-lg font-black text-base" @if($baseCompare <= $baseUnit) hidden @endif>
                            -{{ $baseCompare > $baseUnit ? round((($baseCompare - $baseUnit) / $baseCompare) * 100) : 0 }}%
                        </div>
                    </div>
                    @endunless

                    {{-- Variations --}}
                    @if($hasVariations)
                    <div>
                        <p class="block text-gray-900 font-bold mb-2 {{ $formAlign }}">{{ $formCopy['options'] }}</p>
                        <div class="space-y-2" id="lp-variations">
                            @foreach($product->activeVariations as $index => $variation)
                            @php
                                $attrParts = [];
                                if (!empty($variation->attributes) && is_array($variation->attributes)) {
                                    foreach ($variation->attributes as $key => $value) {
                                        $attrParts[] = ucfirst($key) . ': ' . $value;
                                    }
                                }
                                $displayName = $attrParts ? implode(' / ', $attrParts) : 'Option ' . ($index + 1);
                                $isDefault = $variation->id === $defaultVariation->id;
                            @endphp
                            <label class="lp-choice lp-variation {{ $isDefault ? 'is-selected' : '' }}"
                                   data-variation-id="{{ $variation->id }}"
                                   data-price="{{ $variation->price }}"
                                   data-compare-price="{{ $variation->compare_at_price ?? 0 }}">
                                <input type="radio" name="lp_variation" value="{{ $variation->id }}" {{ $isDefault ? 'checked' : '' }}>
                                <span class="font-semibold text-gray-900 flex-1 min-w-0">{{ $displayName }}</span>
                                <span class="lp-offer__prices">
                                    <span class="lp-offer__total" style="font-size:1.05rem;">{{ $lpFmt($variation->price) }} <small>{{ $lpCurrencySymbol }}</small></span>
                                    @if($variation->compare_at_price && $variation->compare_at_price > $variation->price)
                                        <span class="lp-offer__compare" style="display:block;">{{ $lpFmt($variation->compare_at_price) }} {{ $lpCurrencySymbol }}</span>
                                    @endif
                                </span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Quantity offers (product promotions) --}}
                    @if($hasPromotions)
                    <div>
                        <p class="block text-gray-900 font-bold mb-2 {{ $formAlign }}">{{ $formCopy['offers'] }}</p>
                        <div class="space-y-2" id="lp-offers">
                            @foreach($offers as $offer)
                            @php $isSel = $selectedOffer && $offer === $selectedOffer; @endphp
                            <label class="lp-choice lp-offer {{ $isSel ? 'is-selected' : '' }}"
                                   @if($offer['hidden']) hidden @endif
                                   @if($offer['synthetic']) data-synthetic="1" @endif
                                   data-promotion-id="{{ $offer['promotion_id'] }}"
                                   data-variation-id="{{ $offer['variation_id'] }}"
                                   data-qty="{{ $offer['qty'] }}"
                                   data-price="{{ $offer['unit'] }}"
                                   data-compare="{{ $offer['promo_compare'] }}">
                                <input type="radio" name="lp_offer" value="{{ $offer['promotion_id'] ?: 'single' }}" {{ $isSel ? 'checked' : '' }}>
                                <span class="flex-1 min-w-0">
                                    <span class="lp-offer__qty" style="display:block;">{{ $lpQtyLabel($offer['qty']) }}</span>
                                    @if($offer['label'])
                                        <span class="lp-offer__label">{{ $offer['label'] }}</span>
                                    @endif
                                </span>
                                <span class="lp-offer__prices">
                                    <span class="lp-offer__total" style="display:block;"><span class="js-total">{{ $lpFmt($offer['unit'] * $offer['qty']) }}</span> <small>{{ $lpCurrencySymbol }}</small></span>
                                    <span class="lp-offer__compare js-compare" style="display:block;" @if(!$offer['compare']) hidden @endif>{{ $offer['compare'] ? $lpFmt($offer['compare'] * $offer['qty']) . ' ' . $lpCurrencySymbol : '' }}</span>
                                    <span class="lp-offer__save js-save" @if(!$offer['compare']) hidden @endif>{{ $offer['compare'] ? $formCopy['save'] . ' ' . $lpFmt(($offer['compare'] - $offer['unit']) * $offer['qty']) . ' ' . $lpCurrencySymbol : '' }}</span>
                                </span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Customer fields (merchant-configurable) --}}
                    @foreach($formFields as $field)
                        @php
                            $fieldLabel = \App\Support\LandingFormFields::resolveLabel($field, $lpLang, $formCopy['field']);
                            $fieldPlaceholder = \App\Support\LandingFormFields::resolvePlaceholder($field, $lpLang);
                        @endphp
                        <div>
                            <label class="block text-gray-900 font-bold mb-2 {{ $formAlign }}">
                                {{ $fieldLabel }}
                                @if($field['required'] ?? false)
                                    <span class="text-red-500">*</span>
                                @endif
                            </label>

                            @if(($field['type'] ?? 'text') === 'textarea')
                                <textarea
                                    name="{{ $field['id'] }}"
                                    rows="3"
                                    {{ ($field['required'] ?? false) ? 'required' : '' }}
                                    placeholder="{{ $fieldPlaceholder }}"
                                    class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 focus:outline-none transition-all resize-none {{ $formAlign }}">{{ old($field['id']) }}</textarea>
                            @elseif(($field['type'] ?? 'text') === 'select')
                                <select
                                    name="{{ $field['id'] }}"
                                    {{ ($field['required'] ?? false) ? 'required' : '' }}
                                    class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 focus:outline-none transition-all {{ $formAlign }}">
                                    <option value="">{{ $fieldPlaceholder ?: $formCopy['choose'] }}</option>
                                    @if(!empty($field['options']))
                                        @foreach($field['options'] as $option)
                                            <option value="{{ $option }}" @selected(old($field['id']) === $option)>{{ $option }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            @else
                                <input
                                    type="{{ $field['type'] ?? 'text' }}"
                                    name="{{ $field['id'] }}"
                                    value="{{ old($field['id']) }}"
                                    {{ ($field['required'] ?? false) ? 'required' : '' }}
                                    placeholder="{{ $fieldPlaceholder }}"
                                    class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 focus:outline-none transition-all {{ $formAlign }}">
                            @endif

                            @error($field['id'])
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach

                    {{-- Total (shown when quantity offers exist) --}}
                    @if($hasPromotions)
                    <div class="lp-total" id="lp-total">
                        <span class="lp-total__label">{{ $formCopy['total'] }} <span id="lp-total-qty">({{ $lpQtyLabel($initialQty) }})</span></span>
                        <span class="lp-total__value"><span id="lp-total-value">{{ $lpFmt($initialUnit * $initialQty) }}</span> <small>{{ $lpCurrencySymbol }}</small></span>
                    </div>
                    @endif

                    <button type="submit" id="landing-order-submit"
                            class="w-full py-5 bg-gradient-to-r from-blue-600 to-blue-800 hover:from-blue-700 hover:to-blue-900 text-white font-black text-xl rounded-xl transition-all duration-300 shadow-xl hover:shadow-2xl flex items-center justify-center gap-3 disabled:opacity-70 disabled:cursor-not-allowed disabled:pointer-events-none">
                        <svg class="w-6 h-6 submit-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span class="submit-label">{{ $formCopy['submit'] }}</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

{{-- Keeps hidden order inputs, offer prices and the total in sync; prevents double submit. --}}
<script>
    (function () {
        const form = document.getElementById('landing-order-form');
        if (!form) return;

        const qtyLabels = @json(collect(range(1, 50))->mapWithKeys(fn ($q) => [$q => $lpQtyLabel($q)]));
        const currency = form.dataset.currency || '';
        const saveLabel = form.dataset.saveLabel || '';

        // Same output as PHP number_format(): "14,900" / "12.5"
        const fmt = (n) => {
            const fixed = (Math.round(n * 100) / 100).toFixed(2);
            let [int, dec] = fixed.split('.');
            int = int.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            dec = dec.replace(/0+$/, '');
            return dec ? int + '.' + dec : int;
        };

        const offers = Array.from(form.querySelectorAll('.lp-offer'));
        const variations = Array.from(form.querySelectorAll('.lp-variation'));
        const inPromotion = document.getElementById('selected_promotion_id');
        const inVariation = document.getElementById('selected_variation_id');
        const inPrice = document.getElementById('selected_price');

        function currentBase() {
            const checked = form.querySelector('input[name="lp_variation"]:checked');
            const v = checked ? checked.closest('.lp-variation') : null;
            if (v) {
                return { id: v.dataset.variationId, price: parseFloat(v.dataset.price) || 0, compare: parseFloat(v.dataset.comparePrice) || 0 };
            }
            return { id: '', price: parseFloat(form.dataset.basePrice) || 0, compare: parseFloat(form.dataset.baseCompare) || 0 };
        }

        function refresh() {
            const base = currentBase();
            const refUnit = base.compare > base.price ? base.compare : base.price;

            variations.forEach(v => v.classList.toggle('is-selected', v.querySelector('input').checked));

            // Offers: show the ones that apply to the selected variation and recompute their totals.
            let hasQtyOne = false;
            offers.forEach(o => {
                if (o.dataset.synthetic) return;
                const visible = !o.dataset.variationId || o.dataset.variationId === base.id;
                o.hidden = !visible;
                if (visible && parseInt(o.dataset.qty, 10) <= 1) hasQtyOne = true;
            });
            offers.forEach(o => {
                const qty = parseInt(o.dataset.qty, 10) || 1;
                let unit, cmp;
                if (o.dataset.synthetic) {
                    o.hidden = hasQtyOne;
                    unit = base.price;
                    cmp = base.compare;
                } else {
                    unit = parseFloat(o.dataset.price) || 0;
                    cmp = parseFloat(o.dataset.compare) || refUnit;
                }
                o.dataset.unit = unit;
                o.querySelector('.js-total').textContent = fmt(unit * qty);
                const cmpEl = o.querySelector('.js-compare');
                const saveEl = o.querySelector('.js-save');
                if (cmp > unit) {
                    cmpEl.textContent = fmt(cmp * qty) + ' ' + currency;
                    saveEl.textContent = saveLabel + ' ' + fmt((cmp - unit) * qty) + ' ' + currency;
                    cmpEl.hidden = saveEl.hidden = false;
                } else {
                    cmpEl.hidden = saveEl.hidden = true;
                }
            });

            let selected = offers.find(o => !o.hidden && o.querySelector('input').checked);
            if (offers.length && !selected) {
                selected = offers.find(o => !o.hidden);
                if (selected) selected.querySelector('input').checked = true;
            }
            offers.forEach(o => o.classList.toggle('is-selected', o === selected));

            const unit = selected ? parseFloat(selected.dataset.unit) : base.price;
            const qty = selected ? (parseInt(selected.dataset.qty, 10) || 1) : 1;

            if (inPromotion) inPromotion.value = selected ? (selected.dataset.promotionId || '') : '';
            if (inVariation) inVariation.value = base.id;
            if (inPrice) inPrice.value = unit;

            // Simple price block (products without quantity offers)
            const priceMain = document.getElementById('lp-price-main');
            if (priceMain) {
                priceMain.textContent = fmt(base.price);
                const cmpEl = document.getElementById('lp-price-compare');
                const discEl = document.getElementById('lp-price-discount');
                const hasCmp = base.compare > base.price;
                if (cmpEl) { cmpEl.hidden = !hasCmp; if (hasCmp) cmpEl.textContent = fmt(base.compare) + ' ' + currency; }
                if (discEl) { discEl.hidden = !hasCmp; if (hasCmp) discEl.textContent = '-' + Math.round((base.compare - base.price) / base.compare * 100) + '%'; }
            }

            const totalEl = document.getElementById('lp-total-value');
            if (totalEl) totalEl.textContent = fmt(unit * qty);
            const totalQtyEl = document.getElementById('lp-total-qty');
            if (totalQtyEl) totalQtyEl.textContent = '(' + (qtyLabels[qty] || qty) + ')';
        }

        form.addEventListener('change', function (event) {
            if (event.target && (event.target.name === 'lp_offer' || event.target.name === 'lp_variation')) {
                refresh();
            }
        });
        refresh();

        // Hide the sticky "Order now" bar while the form itself is on screen,
        // so it never covers the submit button.
        document.addEventListener('DOMContentLoaded', function () {
            const orderCard = document.getElementById('order-form');
            const stickyBar = document.getElementById('lp-sticky-order');
            if (orderCard && stickyBar && 'IntersectionObserver' in window) {
                new IntersectionObserver(function (entries) {
                    const visible = entries.some(e => e.isIntersecting);
                    stickyBar.style.display = visible ? 'none' : '';
                }, { threshold: 0.15 }).observe(orderCard);
            }
        });

        // Duplicate-submit prevention
        let isSubmitting = false;
        form.addEventListener('submit', function (event) {
            if (isSubmitting) {
                event.preventDefault();
                return;
            }
            isSubmitting = true;
            const btn = document.getElementById('landing-order-submit');
            if (btn) {
                btn.disabled = true;
                btn.setAttribute('aria-busy', 'true');
                const label = btn.querySelector('.submit-label');
                if (label) label.textContent = @json($formCopy['submitting']);
            }
        });
    })();
</script>
