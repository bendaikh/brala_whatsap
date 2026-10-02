<?php

namespace App\Support;

class LandingFormFields
{
    public const ROLE_CUSTOM = 'custom';

    public const ROLE_NAME = 'name';

    public const ROLE_PHONE = 'phone';

    public const ROLE_CITY = 'city';

    public const ROLE_ADDRESS = 'address';

    public const ROLE_NOTE = 'note';

    public const SYSTEM_ROLES = [
        self::ROLE_NAME => 'name',
        self::ROLE_PHONE => 'phone',
        self::ROLE_CITY => 'city',
        self::ROLE_ADDRESS => 'address',
        self::ROLE_NOTE => 'note',
    ];

    /**
     * Default labels / placeholders for system fields, keyed by language then role.
     */
    public const SYSTEM_COPY = [
        'fr' => [
            self::ROLE_NAME => ['label' => 'Nom', 'placeholder' => 'Votre nom'],
            self::ROLE_PHONE => ['label' => 'Téléphone', 'placeholder' => 'Votre téléphone'],
            self::ROLE_CITY => ['label' => 'Ville', 'placeholder' => 'Choisissez votre ville'],
            self::ROLE_ADDRESS => ['label' => 'Adresse', 'placeholder' => 'Entrez votre adresse'],
            self::ROLE_NOTE => ['label' => 'Notes', 'placeholder' => 'Notes (optionnel)'],
        ],
        'en' => [
            self::ROLE_NAME => ['label' => 'Name', 'placeholder' => 'Your name'],
            self::ROLE_PHONE => ['label' => 'Phone', 'placeholder' => 'Your phone'],
            self::ROLE_CITY => ['label' => 'City', 'placeholder' => 'Choose your city'],
            self::ROLE_ADDRESS => ['label' => 'Address', 'placeholder' => 'Enter your address'],
            self::ROLE_NOTE => ['label' => 'Notes', 'placeholder' => 'Notes (optional)'],
        ],
        'ar' => [
            self::ROLE_NAME => ['label' => 'الاسم', 'placeholder' => 'الاسم'],
            self::ROLE_PHONE => ['label' => 'الهاتف', 'placeholder' => 'الهاتف'],
            self::ROLE_CITY => ['label' => 'المدينة', 'placeholder' => 'اختر مدينتك'],
            self::ROLE_ADDRESS => ['label' => 'العنوان', 'placeholder' => 'أدخل عنوانك'],
            self::ROLE_NOTE => ['label' => 'ملاحظات', 'placeholder' => 'ملاحظات'],
        ],
    ];

    public static function inferRole(array $field): string
    {
        if (!empty($field['field_role']) && $field['field_role'] !== self::ROLE_CUSTOM) {
            return $field['field_role'];
        }

        $id = $field['id'] ?? '';

        foreach (self::SYSTEM_ROLES as $role => $roleId) {
            if ($id === $roleId) {
                return $role;
            }
        }

        if (in_array($id, ['ville'], true)) {
            return self::ROLE_CITY;
        }

        if (in_array($id, ['adresse'], true)) {
            return self::ROLE_ADDRESS;
        }

        return self::ROLE_CUSTOM;
    }

    public static function cleanFields(array $formFields): array
    {
        $cleanedFields = [];

        foreach ($formFields as $field) {
            if (empty($field['id']) && empty($field['field_role'])) {
                continue;
            }

            $role = self::inferRole($field);
            $fieldId = $field['id'] ?? ('field_' . uniqid());

            if ($role !== self::ROLE_CUSTOM && isset(self::SYSTEM_ROLES[$role])) {
                $fieldId = self::SYSTEM_ROLES[$role];
            }

            $labelFr = trim((string) ($field['label_fr'] ?? ''));
            $labelEn = trim((string) ($field['label_en'] ?? ''));
            $labelAr = trim((string) ($field['label_ar'] ?? ''));
            $label = trim((string) ($field['label'] ?? ''));

            $placeholderFr = trim((string) ($field['placeholder_fr'] ?? ''));
            $placeholderEn = trim((string) ($field['placeholder_en'] ?? ''));
            $placeholderAr = trim((string) ($field['placeholder_ar'] ?? ''));

            if ($role !== self::ROLE_CUSTOM) {
                // System fields always get proper per-language defaults when a key is empty.
                foreach (['fr', 'en', 'ar'] as $lang) {
                    $defaults = self::SYSTEM_COPY[$lang][$role] ?? null;
                    if (!$defaults) {
                        continue;
                    }

                    if ($lang === 'fr' && $labelFr === '') {
                        $labelFr = $defaults['label'];
                    }
                    if ($lang === 'en' && $labelEn === '') {
                        $labelEn = $defaults['label'];
                    }
                    if ($lang === 'ar' && $labelAr === '') {
                        $labelAr = $defaults['label'];
                    }
                    if ($lang === 'fr' && $placeholderFr === '') {
                        $placeholderFr = $defaults['placeholder'];
                    }
                    if ($lang === 'en' && $placeholderEn === '') {
                        $placeholderEn = $defaults['placeholder'];
                    }
                    if ($lang === 'ar' && $placeholderAr === '') {
                        $placeholderAr = $defaults['placeholder'];
                    }
                }

                if ($label === '') {
                    $label = $labelFr ?: $labelEn ?: $labelAr;
                }
            } else {
                // Custom fields: if the merchant filled only one language, reuse it everywhere.
                $primaryLabel = $labelFr ?: $labelEn ?: $labelAr ?: $label;
                if ($primaryLabel !== '') {
                    $labelFr = $labelFr ?: $primaryLabel;
                    $labelEn = $labelEn ?: $primaryLabel;
                    $labelAr = $labelAr ?: $primaryLabel;
                    $label = $label ?: $primaryLabel;
                }

                $primaryPlaceholder = $placeholderFr ?: $placeholderEn ?: $placeholderAr;
                if ($primaryPlaceholder !== '') {
                    $placeholderFr = $placeholderFr ?: $primaryPlaceholder;
                    $placeholderEn = $placeholderEn ?: $primaryPlaceholder;
                    $placeholderAr = $placeholderAr ?: $primaryPlaceholder;
                }
            }

            $cleanedField = [
                'id' => $fieldId,
                'field_role' => $role,
                'type' => $field['type'] ?? 'text',
                'label' => $label,
                'label_fr' => $labelFr,
                'label_en' => $labelEn,
                'label_ar' => $labelAr,
                'placeholder_fr' => $placeholderFr,
                'placeholder_en' => $placeholderEn,
                'placeholder_ar' => $placeholderAr,
                'required' => !empty($field['required']),
                'is_system' => $role !== self::ROLE_CUSTOM,
            ];

            if ($cleanedField['type'] === 'select' && !empty($field['options'])) {
                $cleanedField['options'] = $field['options'];
            }

            $cleanedFields[] = $cleanedField;
        }

        return $cleanedFields;
    }

    /**
     * Resolve a non-empty label for the given landing-page language.
     */
    public static function resolveLabel(array $field, string $lang, string $fallback = 'Field'): string
    {
        $lang = in_array($lang, ['fr', 'en', 'ar'], true) ? $lang : 'fr';
        $role = self::inferRole($field);

        $candidates = [
            $field['label_' . $lang] ?? null,
            $field['label'] ?? null,
        ];

        if ($role !== self::ROLE_CUSTOM) {
            $candidates[] = self::SYSTEM_COPY[$lang][$role]['label'] ?? null;
        }

        // Prefer other languages only after the current-language default.
        foreach (['fr', 'en', 'ar'] as $otherLang) {
            if ($otherLang === $lang) {
                continue;
            }
            $candidates[] = $field['label_' . $otherLang] ?? null;
        }

        foreach ($candidates as $value) {
            $value = trim((string) $value);
            if ($value !== '') {
                return $value;
            }
        }

        return $fallback;
    }

    /**
     * Resolve a placeholder for the given landing-page language.
     */
    public static function resolvePlaceholder(array $field, string $lang): string
    {
        $lang = in_array($lang, ['fr', 'en', 'ar'], true) ? $lang : 'fr';
        $role = self::inferRole($field);

        $candidates = [
            $field['placeholder_' . $lang] ?? null,
            $field['placeholder'] ?? null,
        ];

        if ($role !== self::ROLE_CUSTOM) {
            $candidates[] = self::SYSTEM_COPY[$lang][$role]['placeholder'] ?? null;
        }

        foreach (['fr', 'en', 'ar'] as $otherLang) {
            if ($otherLang === $lang) {
                continue;
            }
            $candidates[] = $field['placeholder_' . $otherLang] ?? null;
        }

        foreach ($candidates as $value) {
            $value = trim((string) $value);
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    /**
     * Default form fields for the product builder, matching workspace language.
     */
    public static function defaultFieldsForLanguage(string $lang): array
    {
        $lang = in_array($lang, ['fr', 'en', 'ar'], true) ? $lang : 'ar';
        $fields = [];

        foreach ([self::ROLE_NAME, self::ROLE_PHONE, self::ROLE_NOTE] as $role) {
            $fields[] = self::systemFieldDefinition($role, $lang);
        }

        return $fields;
    }

    public static function systemFieldDefinition(string $role, string $primaryLang = 'ar'): array
    {
        $primaryLang = in_array($primaryLang, ['fr', 'en', 'ar'], true) ? $primaryLang : 'ar';
        $id = self::SYSTEM_ROLES[$role] ?? $role;

        $field = [
            'id' => $id,
            'field_role' => $role,
            'type' => $role === self::ROLE_NOTE ? 'textarea' : ($role === self::ROLE_PHONE ? 'tel' : ($role === self::ROLE_CITY ? 'select' : 'text')),
            'label' => '',
            'label_fr' => self::SYSTEM_COPY['fr'][$role]['label'] ?? '',
            'label_en' => self::SYSTEM_COPY['en'][$role]['label'] ?? '',
            'label_ar' => self::SYSTEM_COPY['ar'][$role]['label'] ?? '',
            'placeholder_fr' => self::SYSTEM_COPY['fr'][$role]['placeholder'] ?? '',
            'placeholder_en' => self::SYSTEM_COPY['en'][$role]['placeholder'] ?? '',
            'placeholder_ar' => self::SYSTEM_COPY['ar'][$role]['placeholder'] ?? '',
            'required' => in_array($role, [self::ROLE_NAME, self::ROLE_PHONE, self::ROLE_CITY], true),
            'is_system' => true,
            'options' => [],
            'options_text' => '',
        ];

        // Keep a convenient primary label for the builder UI.
        $field['label'] = $field['label_' . $primaryLang] ?? $field['label_fr'];

        if ($role === self::ROLE_CITY) {
            if ($primaryLang === 'fr') {
                $field['options'] = ['Abidjan', 'Bouaké', 'Yamoussoukro', 'San-Pédro', 'Korhogo', 'Daloa'];
            } elseif ($primaryLang === 'en') {
                $field['options'] = ['Abidjan', 'Bouake', 'Yamoussoukro', 'San-Pedro', 'Korhogo', 'Daloa'];
            } else {
                $field['options'] = ['الدار البيضاء', 'الرباط', 'مراكش', 'فاس', 'طنجة', 'أكادير'];
            }
            $field['options_text'] = implode(', ', $field['options']);
        }

        return $field;
    }

    /**
     * @return array{city: ?string, address: ?string}
     */
    public static function extractLocationData(array $formFields, array $validated): array
    {
        $city = null;
        $address = null;

        foreach ($formFields as $field) {
            $fieldId = $field['id'] ?? null;
            if (!$fieldId) {
                continue;
            }

            $role = self::inferRole($field);
            $value = $validated[$fieldId] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            if ($role === self::ROLE_CITY) {
                $city = $value;
            }

            if ($role === self::ROLE_ADDRESS) {
                $address = $value;
            }
        }

        return [
            'city' => $city ?? $validated['city'] ?? $validated['ville'] ?? null,
            'address' => $address ?? $validated['address'] ?? $validated['adresse'] ?? null,
        ];
    }

    public static function systemFieldIds(): array
    {
        return array_values(self::SYSTEM_ROLES);
    }
}
