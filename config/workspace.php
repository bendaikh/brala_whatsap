<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Workspace landing page languages
    |--------------------------------------------------------------------------
    | Used when creating/editing a workspace. Product landing pages and AI
    | generation follow the selected language.
    */
    'languages' => [
        'ar' => [
            'label' => 'Arabic (العربية)',
            'native' => 'العربية',
            'dir' => 'rtl',
            'html_lang' => 'ar',
        ],
        'fr' => [
            'label' => 'French (Français)',
            'native' => 'Français',
            'dir' => 'ltr',
            'html_lang' => 'fr',
        ],
        'en' => [
            'label' => 'English',
            'native' => 'English',
            'dir' => 'ltr',
            'html_lang' => 'en',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Workspace currencies
    |--------------------------------------------------------------------------
    | code  = ISO code for pixels / analytics
    | symbol = display label next to prices on landing pages
    | label  = admin UI label
    */
    'currencies' => [
        'MAD' => [
            'label' => 'Moroccan Dirham (MAD)',
            'symbol' => 'DHS',
            'code' => 'MAD',
        ],
        'XOF' => [
            'label' => 'West African CFA Franc (XOF) — Côte d\'Ivoire',
            'symbol' => 'FCFA',
            'code' => 'XOF',
        ],
        'EUR' => [
            'label' => 'Euro (EUR)',
            'symbol' => '€',
            'code' => 'EUR',
        ],
        'USD' => [
            'label' => 'US Dollar (USD)',
            'symbol' => '$',
            'code' => 'USD',
        ],
        'TND' => [
            'label' => 'Tunisian Dinar (TND)',
            'symbol' => 'د.ت',
            'code' => 'TND',
        ],
        'DZD' => [
            'label' => 'Algerian Dinar (DZD)',
            'symbol' => 'د.ج',
            'code' => 'DZD',
        ],
        'XAF' => [
            'label' => 'Central African CFA Franc (XAF)',
            'symbol' => 'FCFA',
            'code' => 'XAF',
        ],
        'GNF' => [
            'label' => 'Guinean Franc (GNF)',
            'symbol' => 'GNF',
            'code' => 'GNF',
        ],
        'NGN' => [
            'label' => 'Nigerian Naira (NGN)',
            'symbol' => '₦',
            'code' => 'NGN',
        ],
        'GBP' => [
            'label' => 'British Pound (GBP)',
            'symbol' => '£',
            'code' => 'GBP',
        ],
    ],

    'defaults' => [
        'language' => 'ar',
        'currency' => 'MAD',
    ],

];
