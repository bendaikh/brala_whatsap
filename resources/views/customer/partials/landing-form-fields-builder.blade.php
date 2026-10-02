@php
    $workspaceLang = $workspaceLang
        ?? ($store->workspace?->getLanguage() ?? null)
        ?? (isset($product) ? ($product->store?->workspace?->getLanguage() ?? null) : null)
        ?? 'ar';
    $workspaceLang = in_array($workspaceLang, ['fr', 'en', 'ar'], true) ? $workspaceLang : 'ar';
    $initialFieldsJson = isset($initialFields) ? json_encode($initialFields) : 'null';
    $ui = match ($workspaceLang) {
        'fr' => [
            'title' => 'Champs du formulaire de la page produit',
            'subtitle' => 'Personnalisez les champs du formulaire de commande',
            'add_city' => '+ Ville',
            'add_address' => '+ Adresse',
            'add_field' => 'Ajouter un champ',
            'note' => 'Utilisez le rôle du champ pour indiquer s\'il s\'agit de la ville, de l\'adresse ou des notes. Avec "Ville", la valeur est enregistrée comme ville dans les commandes.',
            'empty' => 'Aucun champ personnalisé pour le moment',
            'field_n' => 'Champ',
            'role' => 'Rôle du champ',
            'role_custom' => 'Champ personnalisé',
            'role_name' => 'Nom',
            'role_phone' => 'Téléphone',
            'role_city' => 'Ville',
            'role_address' => 'Adresse',
            'role_note' => 'Notes',
            'city_hint' => 'Cette valeur sera enregistrée comme "Ville" dans les commandes.',
            'type' => 'Type de champ',
            'type_text' => 'Texte',
            'type_email' => 'E-mail',
            'type_tel' => 'Téléphone',
            'type_number' => 'Nombre',
            'type_textarea' => 'Zone de texte',
            'type_select' => 'Liste déroulante',
            'required' => 'Obligatoire',
            'required_field' => 'Champ obligatoire',
            'label' => 'Libellé',
            'label_ph' => 'Ex. : Ville',
            'placeholder' => 'Texte indicatif',
            'placeholder_ph' => 'Ex. : Choisissez votre ville',
            'options' => 'Options (séparées par des virgules)',
            'options_ph' => 'Ex. : Abidjan, Bouaké, Yamoussoukro',
            'role_used' => 'Ce rôle est déjà utilisé par un autre champ.',
            'remove_confirm' => 'Voulez-vous vraiment supprimer ce champ ?',
        ],
        'en' => [
            'title' => 'Landing page form fields',
            'subtitle' => 'Customize the order form fields on your product page',
            'add_city' => '+ City',
            'add_address' => '+ Address',
            'add_field' => 'Add field',
            'note' => 'Use the field role to mark city, address, or notes. With "City", the value is saved as city on orders.',
            'empty' => 'No custom form fields yet',
            'field_n' => 'Field',
            'role' => 'Field role',
            'role_custom' => 'Custom field',
            'role_name' => 'Name',
            'role_phone' => 'Phone',
            'role_city' => 'City',
            'role_address' => 'Address',
            'role_note' => 'Notes',
            'city_hint' => 'This value will be saved as "City" on orders.',
            'type' => 'Field type',
            'type_text' => 'Text',
            'type_email' => 'Email',
            'type_tel' => 'Phone',
            'type_number' => 'Number',
            'type_textarea' => 'Textarea',
            'type_select' => 'Dropdown',
            'required' => 'Required',
            'required_field' => 'Required field',
            'label' => 'Label',
            'label_ph' => 'e.g. City',
            'placeholder' => 'Placeholder',
            'placeholder_ph' => 'e.g. Choose your city',
            'options' => 'Options (comma-separated)',
            'options_ph' => 'e.g. Abidjan, Bouake, Yamoussoukro',
            'role_used' => 'This role is already used by another field.',
            'remove_confirm' => 'Are you sure you want to remove this field?',
        ],
        default => [
            'title' => 'حقول نموذج صفحة الهبوط',
            'subtitle' => 'تخصيص حقول نموذج الاتصال على صفحة الهبوط الخاصة بك',
            'add_city' => '+ المدينة',
            'add_address' => '+ العنوان',
            'add_field' => 'إضافة حقل',
            'note' => 'استخدم دور الحقل لتحديد ما إذا كان الحقل يمثل المدينة أو العنوان أو ملاحظات. عند اختيار "المدينة"، سيتم حفظ القيمة في حقل المدينة في الطلبات وليس في الملاحظات.',
            'empty' => 'لم يتم إضافة حقول نموذج مخصصة بعد',
            'field_n' => 'الحقل',
            'role' => 'دور الحقل',
            'role_custom' => 'حقل مخصص',
            'role_name' => 'الاسم',
            'role_phone' => 'الهاتف',
            'role_city' => 'المدينة',
            'role_address' => 'العنوان',
            'role_note' => 'ملاحظات',
            'city_hint' => 'سيتم حفظ هذا الحقل كـ "Ville" في الطلبات.',
            'type' => 'نوع الحقل',
            'type_text' => 'نص',
            'type_email' => 'بريد إلكتروني',
            'type_tel' => 'هاتف',
            'type_number' => 'رقم',
            'type_textarea' => 'نص متعدد الأسطر',
            'type_select' => 'قائمة منسدلة',
            'required' => 'مطلوب',
            'required_field' => 'حقل مطلوب',
            'label' => 'التسمية',
            'label_ph' => 'مثال: المدينة',
            'placeholder' => 'النص التوضيحي',
            'placeholder_ph' => 'مثال: اختر مدينتك',
            'options' => 'الخيارات (مفصولة بفاصلة)',
            'options_ph' => 'مثال: الدار البيضاء, الرباط, مراكش',
            'role_used' => 'هذا الدور مستخدم بالفعل في حقل آخر.',
            'remove_confirm' => 'هل أنت متأكد من أنك تريد إزالة هذا الحقل؟',
        ],
    };
    $labelModel = 'label_' . $workspaceLang;
    $placeholderModel = 'placeholder_' . $workspaceLang;
    $defaultFieldsJson = json_encode(\App\Support\LandingFormFields::defaultFieldsForLanguage($workspaceLang), JSON_UNESCAPED_UNICODE);
    $rolePresetsJson = json_encode([
        'city' => \App\Support\LandingFormFields::systemFieldDefinition('city', $workspaceLang),
        'address' => \App\Support\LandingFormFields::systemFieldDefinition('address', $workspaceLang),
        'name' => \App\Support\LandingFormFields::systemFieldDefinition('name', $workspaceLang),
        'phone' => \App\Support\LandingFormFields::systemFieldDefinition('phone', $workspaceLang),
        'note' => \App\Support\LandingFormFields::systemFieldDefinition('note', $workspaceLang),
    ], JSON_UNESCAPED_UNICODE);
@endphp

<div class="bg-[#0f1c2e] border border-white/10 rounded-xl p-6" x-data="formFieldsManager({{ $initialFieldsJson }}, '{{ $workspaceLang }}')">
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 mb-6">
        <div>
            <h3 class="text-xl font-bold text-white">{{ $ui['title'] }}</h3>
            <p class="text-sm text-gray-400 mt-1">{{ $ui['subtitle'] }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" @click="addPresetField('city')" :disabled="hasRole('city')"
                class="px-3 py-2 bg-cyan-600/20 hover:bg-cyan-600/30 disabled:opacity-40 disabled:cursor-not-allowed text-cyan-300 rounded-lg text-sm transition">
                {{ $ui['add_city'] }}
            </button>
            <button type="button" @click="addPresetField('address')" :disabled="hasRole('address')"
                class="px-3 py-2 bg-cyan-600/20 hover:bg-cyan-600/30 disabled:opacity-40 disabled:cursor-not-allowed text-cyan-300 rounded-lg text-sm transition">
                {{ $ui['add_address'] }}
            </button>
            <button type="button" @click="addField()"
                class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white font-semibold rounded-lg transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ $ui['add_field'] }}
            </button>
        </div>
    </div>

    <div class="space-y-4 mb-4">
        <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4 text-blue-300 text-sm">
            {{ $ui['note'] }}
        </div>
    </div>

    <input type="hidden" name="form_fields" x-model="formFieldsJson">

    <template x-if="fields.length === 0">
        <div class="text-center py-8 text-gray-400">
            <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p>{{ $ui['empty'] }}</p>
        </div>
    </template>

    <div class="space-y-4">
        <template x-for="(field, index) in fields" :key="field.id + '-' + index">
            <div class="bg-[#0a1628] border border-white/10 rounded-lg p-4" :class="field.field_role === 'city' ? 'border-cyan-500/40' : ''">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h4 class="font-semibold text-white" x-text="'{{ $ui['field_n'] }} ' + (index + 1)"></h4>
                        <span x-show="field.field_role && field.field_role !== 'custom'"
                            class="px-2 py-0.5 rounded-full text-xs font-medium bg-cyan-500/20 text-cyan-300"
                            x-text="roleLabel(field.field_role)"></span>
                    </div>
                    <button type="button" @click="removeField(index)" class="text-red-400 hover:text-red-300 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-300 mb-2">{{ $ui['role'] }}</label>
                        <select x-model="field.field_role" @change="applyRoleDefaults(field)"
                            class="w-full px-4 py-2 bg-[#0f1c2e] border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="custom">{{ $ui['role_custom'] }}</option>
                            <option value="name">{{ $ui['role_name'] }}</option>
                            <option value="phone">{{ $ui['role_phone'] }}</option>
                            <option value="city">{{ $ui['role_city'] }}</option>
                            <option value="address">{{ $ui['role_address'] }}</option>
                            <option value="note">{{ $ui['role_note'] }}</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1" x-show="field.field_role === 'city'">
                            {{ $ui['city_hint'] }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">{{ $ui['type'] }}</label>
                        <select x-model="field.type"
                            class="w-full px-4 py-2 bg-[#0f1c2e] border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="text">{{ $ui['type_text'] }}</option>
                            <option value="email">{{ $ui['type_email'] }}</option>
                            <option value="tel">{{ $ui['type_tel'] }}</option>
                            <option value="number">{{ $ui['type_number'] }}</option>
                            <option value="textarea">{{ $ui['type_textarea'] }}</option>
                            <option value="select">{{ $ui['type_select'] }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">{{ $ui['required'] }}</label>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="field.required" class="sr-only peer" />
                            <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            <span class="ml-3 text-sm text-gray-300">{{ $ui['required_field'] }}</span>
                        </label>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-300 mb-2">{{ $ui['label'] }}</label>
                        <input type="text" x-model="field.{{ $labelModel }}" placeholder="{{ $ui['label_ph'] }}"
                            class="w-full px-4 py-2 bg-[#0f1c2e] border border-white/10 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-300 mb-2">{{ $ui['placeholder'] }}</label>
                        <input type="text" x-model="field.{{ $placeholderModel }}" placeholder="{{ $ui['placeholder_ph'] }}"
                            class="w-full px-4 py-2 bg-[#0f1c2e] border border-white/10 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <template x-if="field.type === 'select'">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-300 mb-2">{{ $ui['options'] }}</label>
                            <input type="text" x-model="field.options_text"
                                @input="field.options = field.options_text.split(',').map(o => o.trim()).filter(o => o)"
                                placeholder="{{ $ui['options_ph'] }}"
                                class="w-full px-4 py-2 bg-[#0f1c2e] border border-white/10 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
window.__landingFormFieldsDefaults = window.__landingFormFieldsDefaults || {};
window.__landingFormFieldsDefaults['{{ $workspaceLang }}'] = {
    defaultFields: {!! $defaultFieldsJson !!},
    rolePresets: {!! $rolePresetsJson !!},
    roleLabels: {
        name: @json($ui['role_name']),
        phone: @json($ui['role_phone']),
        city: @json($ui['role_city']),
        address: @json($ui['role_address']),
        note: @json($ui['role_note']),
        custom: @json($ui['role_custom']),
    },
    messages: {
        roleUsed: @json($ui['role_used']),
        removeConfirm: @json($ui['remove_confirm']),
    },
};

document.addEventListener('alpine:init', () => {
    if (window.__landingFormFieldsManagerRegistered) return;
    window.__landingFormFieldsManagerRegistered = true;

    Alpine.data('formFieldsManager', (initialFields = null, lang = 'ar') => ({
        fields: [],
        lang: lang,
        config: null,

        init() {
            this.config = (window.__landingFormFieldsDefaults && window.__landingFormFieldsDefaults[this.lang])
                || (window.__landingFormFieldsDefaults && window.__landingFormFieldsDefaults['ar'])
                || { defaultFields: [], rolePresets: {}, roleLabels: {}, messages: {} };

            this.fields = Array.isArray(initialFields) && initialFields.length > 0
                ? JSON.parse(JSON.stringify(initialFields))
                : JSON.parse(JSON.stringify(this.config.defaultFields || []));

            this.fields = this.fields.map(field => this.normalizeField(field));
        },

        normalizeField(field) {
            if (!field.field_role) {
                if (field.id === 'city' || field.id === 'ville') field.field_role = 'city';
                else if (field.id === 'address' || field.id === 'adresse') field.field_role = 'address';
                else if (field.id === 'name') field.field_role = 'name';
                else if (field.id === 'phone') field.field_role = 'phone';
                else if (field.id === 'note') field.field_role = 'note';
                else field.field_role = 'custom';
            }

            const presets = this.config.rolePresets || {};
            if (field.field_role !== 'custom' && presets[field.field_role]) {
                field.id = presets[field.field_role].id;
            }

            ['label_fr', 'label_en', 'label_ar', 'placeholder_fr', 'placeholder_en', 'placeholder_ar'].forEach((key) => {
                if (typeof field[key] === 'undefined' || field[key] === null) {
                    field[key] = '';
                }
            });

            // Ensure the workspace language label is visible in the builder.
            const labelKey = 'label_' + this.lang;
            const placeholderKey = 'placeholder_' + this.lang;
            if (!field[labelKey]) {
                field[labelKey] = field.label_fr || field.label_en || field.label_ar || field.label || '';
                if (!field[labelKey] && presets[field.field_role]) {
                    field[labelKey] = presets[field.field_role][labelKey] || '';
                }
            }
            if (!field[placeholderKey]) {
                field[placeholderKey] = field.placeholder_fr || field.placeholder_en || field.placeholder_ar || '';
                if (!field[placeholderKey] && presets[field.field_role]) {
                    field[placeholderKey] = presets[field.field_role][placeholderKey] || '';
                }
            }

            if (field.type === 'select' && field.options) {
                field.options_text = field.options.join(', ');
            } else {
                field.options = field.options || [];
                field.options_text = field.options_text || '';
            }

            return field;
        },

        get formFieldsJson() {
            const presets = this.config.rolePresets || {};
            return JSON.stringify(this.fields.map(field => {
                const copy = { ...field };
                if (copy.field_role !== 'custom' && presets[copy.field_role]) {
                    copy.id = presets[copy.field_role].id;
                }
                return copy;
            }));
        },

        roleLabel(role) {
            return (this.config.roleLabels && this.config.roleLabels[role]) || role;
        },

        hasRole(role) {
            return this.fields.some(field => field.field_role === role);
        },

        applyRoleDefaults(field) {
            if (field.field_role === 'custom') {
                field.is_system = false;
                return;
            }

            if (this.fields.filter(f => f !== field && f.field_role === field.field_role).length > 0) {
                alert((this.config.messages && this.config.messages.roleUsed) || 'Role already used.');
                field.field_role = 'custom';
                return;
            }

            const preset = (this.config.rolePresets || {})[field.field_role];
            if (!preset) return;

            field.id = preset.id;
            field.type = preset.type;
            field.is_system = true;

            ['label_fr', 'label_en', 'label_ar', 'placeholder_fr', 'placeholder_en', 'placeholder_ar'].forEach((key) => {
                if (!field[key] && preset[key]) {
                    field[key] = preset[key];
                }
            });

            if (field.field_role === 'city' && (!field.options || field.options.length === 0)) {
                field.options = [...(preset.options || [])];
                field.options_text = preset.options_text || '';
            }
        },

        addPresetField(role) {
            if (this.hasRole(role)) return;
            const preset = (this.config.rolePresets || {})[role];
            if (!preset) return;
            this.fields.push(this.normalizeField(JSON.parse(JSON.stringify(preset))));
        },

        addField() {
            this.fields.push({
                id: 'field_' + Date.now(),
                field_role: 'custom',
                type: 'text',
                label: '',
                label_fr: '',
                label_en: '',
                label_ar: '',
                placeholder_fr: '',
                placeholder_en: '',
                placeholder_ar: '',
                required: false,
                is_system: false,
                options: [],
                options_text: '',
            });
        },

        removeField(index) {
            if (confirm((this.config.messages && this.config.messages.removeConfirm) || 'Remove this field?')) {
                this.fields.splice(index, 1);
            }
        },
    }));
});
</script>
