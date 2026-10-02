@php($appsScript = file_get_contents(resource_path('google-sheets/apps-script.gs')))
<details class="mt-3" {{ ($open ?? false) ? 'open' : '' }}>
    <summary class="cursor-pointer text-sm font-medium text-blue-900">Show Apps Script template (v3 — matches your sheet's column headers, plain-number prices)</summary>
    <p class="mt-2 text-xs text-blue-800">
        Values are written under the matching header in row 1 (e.g. <em>Nom</em>, <em>Téléphone</em>, <em>Ville</em>, <em>Adresse</em>, <em>Produit</em>, <em>Quantité</em>, <em>Prix</em>, <em>Total</em>…), whatever the column order.
        If you already deployed an older script, paste this one over it, then <strong>Deploy → Manage deployments → Edit → Version: New version → Deploy</strong> (the URL stays the same).
    </p>
    <div class="relative">
        <button type="button"
            onclick="navigator.clipboard.writeText(this.nextElementSibling.innerText).then(() => { this.innerText = 'Copied'; setTimeout(() => this.innerText = 'Copy', 1500); })"
            class="absolute top-2 right-2 text-xs px-2 py-1 rounded bg-blue-600 text-white hover:bg-blue-700">Copy</button>
        <pre class="mt-2 text-xs bg-white border border-blue-100 rounded-lg p-3 overflow-x-auto text-gray-800">{{ $appsScript }}</pre>
    </div>
</details>
