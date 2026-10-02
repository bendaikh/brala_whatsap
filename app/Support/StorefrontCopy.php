<?php

namespace App\Support;

use App\Models\Workspace;

class StorefrontCopy
{
    public static function language(?Workspace $workspace = null): string
    {
        return $workspace?->getLanguage() ?? config('workspace.defaults.language', 'ar');
    }

    /**
     * Default WebsiteSettings content for a workspace language.
     */
    public static function settingsDefaults(?Workspace $workspace = null): array
    {
        $lang = self::language($workspace);
        $appName = config('app.name');

        return match ($lang) {
            'fr' => [
                'hero_title' => 'Bienvenue dans notre boutique',
                'hero_subtitle' => 'Découvrez des produits uniques soigneusement sélectionnés pour vous',
                'hero_button_text' => 'Acheter maintenant',
                'banner_text' => 'Livraison rapide · Paiement à la livraison · Service client disponible',
                'footer_about' => 'Votre boutique de confiance pour des produits de qualité.',
                'footer_copyright' => '© ' . date('Y') . ' ' . $appName . '. Tous droits réservés.',
                'features' => [
                    ['icon' => 'local_shipping', 'title' => 'Livraison rapide', 'color' => '#10b981'],
                    ['icon' => 'support_agent', 'title' => 'Service client', 'color' => '#3b82f6'],
                    ['icon' => 'public', 'title' => 'Livraison nationale', 'color' => '#a855f7'],
                    ['icon' => 'payment', 'title' => 'Paiement à la livraison', 'color' => '#f97316'],
                ],
            ],
            'en' => [
                'hero_title' => 'Welcome to our store',
                'hero_subtitle' => 'Discover unique products carefully selected for you',
                'hero_button_text' => 'Shop now',
                'banner_text' => 'Fast delivery · Cash on delivery · Customer support available',
                'footer_about' => 'Your trusted store for high-quality products.',
                'footer_copyright' => '© ' . date('Y') . ' ' . $appName . '. All rights reserved.',
                'features' => [
                    ['icon' => 'local_shipping', 'title' => 'Fast delivery', 'color' => '#10b981'],
                    ['icon' => 'support_agent', 'title' => 'Customer support', 'color' => '#3b82f6'],
                    ['icon' => 'public', 'title' => 'Nationwide delivery', 'color' => '#a855f7'],
                    ['icon' => 'payment', 'title' => 'Cash on delivery', 'color' => '#f97316'],
                ],
            ],
            default => [
                'hero_title' => 'مرحباً بكم في متجرنا',
                'hero_subtitle' => 'اكتشف منتجات فريدة تم اختيارها بعناية من أجلك',
                'hero_button_text' => 'تسوق الآن',
                'banner_text' => 'المتجر الإلكتروني رقم 1! مباشرة من عندنا إلى عندكم',
                'footer_about' => 'متجركم الموثوق للمنتجات عالية الجودة.',
                'footer_copyright' => '© ' . date('Y') . ' ' . $appName . '. جميع الحقوق محفوظة.',
                'features' => [
                    ['icon' => 'local_shipping', 'title' => 'توصيل مجاني', 'color' => '#10b981'],
                    ['icon' => 'support_agent', 'title' => 'خدمة العملاء', 'color' => '#3b82f6'],
                    ['icon' => 'public', 'title' => 'التوصيل لجميع المدن', 'color' => '#a855f7'],
                    ['icon' => 'payment', 'title' => 'الدفع عند الاستلام', 'color' => '#f97316'],
                ],
            ],
        };
    }

    /**
     * Hardcoded storefront chrome (nav, sections, FAQ, etc.).
     */
    public static function ui(?Workspace $workspace = null): array
    {
        $lang = self::language($workspace);

        return match ($lang) {
            'fr' => [
                'title_suffix' => 'Boutique en ligne',
                'preview_banner' => 'Mode aperçu — voici à quoi ressemble votre site',
                'preview_back' => 'Retour à l\'éditeur',
                'nav_home' => 'Accueil',
                'nav_categories' => 'Catégories',
                'nav_featured' => 'Sélection',
                'nav_contact' => 'Contact',
                'whatsapp' => 'WhatsApp',
                'whatsapp_title' => 'Contactez-nous sur WhatsApp',
                'categories_title' => 'Nos catégories',
                'categories_subtitle' => 'Choisissez une catégorie',
                'featured_title' => 'Produits en vedette',
                'featured_subtitle' => 'Nos meilleures sélections',
                'featured_intro' => 'Découvrez nos produits les plus populaires.',
                'buy_now' => 'Acheter maintenant',
                'all_products' => 'Tous les produits',
                'browse_catalog' => 'Parcourez tout notre catalogue',
                'category_products' => 'Produits de la catégorie :name',
                'clear_filter' => 'Effacer le filtre',
                'out_of_stock' => 'Rupture de stock',
                'uncategorized' => 'Sans catégorie',
                'no_products' => 'Aucun produit disponible pour le moment.',
                'faq_title' => 'Questions & réponses',
                'faq_subtitle' => 'Foire aux questions',
                'faq' => [
                    [
                        'q' => 'Comment passer une commande ?',
                        'a' => 'Parcourez nos produits, cliquez sur celui qui vous plaît, puis remplissez le formulaire de commande ou contactez-nous via WhatsApp.',
                    ],
                    [
                        'q' => 'Proposez-vous le paiement à la livraison ?',
                        'a' => 'Oui, vous pouvez payer à la livraison pour plus de confort et de confiance. Vous réglez directement au livreur à la réception du colis.',
                    ],
                    [
                        'q' => 'Combien de temps prend la livraison ?',
                        'a' => 'La livraison prend généralement de 2 à 5 jours ouvrables selon votre localisation.',
                    ],
                ],
                'more_questions' => 'Vous avez d\'autres questions ?',
                'more_questions_text' => 'Pour plus d\'informations, n\'hésitez pas à nous contacter.',
                'hours' => 'Disponibles de 9h à 20h. Nous sommes prêts à répondre à vos questions.',
                'about_us' => 'À propos',
                'about_store' => 'À propos de la boutique',
                'privacy' => 'Politique de confidentialité',
                'quick_links' => 'Liens rapides',
                'contact_us' => 'Contactez-nous',
            ],
            'en' => [
                'title_suffix' => 'Online store',
                'preview_banner' => 'Preview mode — this is how your site looks',
                'preview_back' => 'Back to editor',
                'nav_home' => 'Home',
                'nav_categories' => 'Categories',
                'nav_featured' => 'Featured',
                'nav_contact' => 'Contact',
                'whatsapp' => 'WhatsApp',
                'whatsapp_title' => 'Contact us on WhatsApp',
                'categories_title' => 'Our categories',
                'categories_subtitle' => 'Shop by category',
                'featured_title' => 'Featured products',
                'featured_subtitle' => 'Get our best picks',
                'featured_intro' => 'Here are some of our best-selling products.',
                'buy_now' => 'Buy now',
                'all_products' => 'All products',
                'browse_catalog' => 'Browse our full catalog',
                'category_products' => 'Products in :name',
                'clear_filter' => 'Clear filter',
                'out_of_stock' => 'Out of stock',
                'uncategorized' => 'Uncategorized',
                'no_products' => 'No products available right now.',
                'faq_title' => 'Q&A',
                'faq_subtitle' => 'Frequently asked questions',
                'faq' => [
                    [
                        'q' => 'How do I place an order?',
                        'a' => 'Browse our products, click the one you like, then fill out the order form or contact us on WhatsApp.',
                    ],
                    [
                        'q' => 'Do you offer cash on delivery?',
                        'a' => 'Yes, you can pay on delivery for your convenience and peace of mind. Pay the courier when you receive your parcel.',
                    ],
                    [
                        'q' => 'How long does shipping take?',
                        'a' => 'Shipping usually takes 2 to 5 business days depending on your location.',
                    ],
                ],
                'more_questions' => 'Have more questions?',
                'more_questions_text' => 'If you need more information, you can always contact us.',
                'hours' => 'Available from 9 AM to 8 PM. We are ready to answer your questions.',
                'about_us' => 'About us',
                'about_store' => 'About the store',
                'privacy' => 'Privacy policy',
                'quick_links' => 'Quick links',
                'contact_us' => 'Contact us',
            ],
            default => [
                'title_suffix' => 'متجر إلكتروني',
                'preview_banner' => 'وضع المعاينة - هذا هو شكل موقعك',
                'preview_back' => 'العودة للمحرر',
                'nav_home' => 'الرئيسية',
                'nav_categories' => 'الفئات',
                'nav_featured' => 'المميزة',
                'nav_contact' => 'اتصل بنا',
                'whatsapp' => 'واتساب',
                'whatsapp_title' => 'تواصل معنا عبر واتساب',
                'categories_title' => 'فئاتنا',
                'categories_subtitle' => 'اختر حسب الفئة',
                'featured_title' => 'المنتجات المميزة',
                'featured_subtitle' => 'احصل على أفضل المنتجات',
                'featured_intro' => 'إليكم مجموعة من أفضل المنتجات مبيعاً لدينا.',
                'buy_now' => 'اشتري الآن',
                'all_products' => 'جميع المنتجات',
                'browse_catalog' => 'تصفح كتالوجنا الكامل',
                'category_products' => 'منتجات فئة :name',
                'clear_filter' => 'إزالة الفلتر',
                'out_of_stock' => 'نفذت الكمية',
                'uncategorized' => 'غير مصنف',
                'no_products' => 'لا توجد منتجات متاحة حالياً.',
                'faq_title' => 'أسئلة وأجوبة',
                'faq_subtitle' => 'الأسئلة الشائعة',
                'faq' => [
                    [
                        'q' => 'كيف أقوم بتقديم طلب؟',
                        'a' => 'تصفح منتجاتنا، انقر على المنتج الذي يعجبك، وتواصل معنا عبر واتساب لإتمام طلبك.',
                    ],
                    [
                        'q' => 'سياسة الدفع عند الاستلام',
                        'a' => 'نوفر لكم خيار الدفع عند الاستلام لراحتكم وثقتكم. تدفعون مباشرة عند استلام الطرد من عامل التوصيل.',
                    ],
                    [
                        'q' => 'كم تستغرق عملية الشحن؟',
                        'a' => 'تستغرق عملية الشحن عادةً من 2 إلى 5 أيام عمل حسب موقعك.',
                    ],
                ],
                'more_questions' => 'هل لديك أسئلة أخرى؟',
                'more_questions_text' => 'إذا كنت تريد المزيد من المعلومات، يمكنك دائماً التواصل معنا.',
                'hours' => 'متاحون من الساعة 9 صباحاً حتى 8 مساءً. نحن جاهزون للرد على استفساراتكم.',
                'about_us' => 'من نحن',
                'about_store' => 'عن المتجر',
                'privacy' => 'سياسة الخصوصية',
                'quick_links' => 'روابط سريعة',
                'contact_us' => 'اتصل بنا',
            ],
        };
    }

    /**
     * Detect default Arabic seed so we can refresh for non-Arabic workspaces.
     */
    public static function looksLikeArabicSeed(?string $heroTitle): bool
    {
        if ($heroTitle === null || $heroTitle === '') {
            return true;
        }

        return str_contains($heroTitle, 'مرحباً بكم في متجرنا')
            || str_contains($heroTitle, 'مرحبا بكم في متجرنا');
    }
}
