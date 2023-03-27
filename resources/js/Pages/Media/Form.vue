<template>
    <div>
        <form>
            <biz-form-input
                v-model="form.file_name"
                :label="i18n.file_name"
                maxlength="250"
                required
                :message="error('file_name', 'default', formErrors)"
                :disabled="isInputDisabled"
                @on-keypress="keyPressFileName"
            />

            <div class="tabs is-boxed">
                <ul>
                    <li
                        v-for="option in usedLocales"
                        :key="option.id"
                        :class="{'is-active': option.id == activeTab}"
                    >
                        <a @click.prevent="setActiveTab(option.id)">
                            <span>
                                {{ option.id.toUpperCase() }}
                            </span>

                            <biz-button-delete
                                v-if="option.id !== defaultLocale"
                                class="ml-1"
                                type="button"
                                @click="deleteTranslation($event, option.id)"
                            />
                        </a>
                    </li>
                    <li v-if="availableLocales.length">
                        <biz-select v-model="selectedLocale">
                            <template
                                v-for="(locale, index) in availableLocales"
                                :key="index"
                            >
                                <option :value="locale.id">
                                    {{ locale.name }}
                                </option>
                            </template>
                        </biz-select>

                        <biz-button-icon
                            :icon="icon.add"
                            type="button"
                            @click="addTranslation"
                        />
                    </li>
                </ul>
            </div>
            <div
                v-if="form.translations[activeTab]"
                class="content"
            >
                <biz-form-input
                    v-if="isImage"
                    v-model="form.translations[activeTab].alt"
                    maxlength="255"
                    :disabled="isInputDisabled"
                    :label="i18n.alternative_text"
                    :message="error('translations.'+ activeTab +'.alt')"
                />
                <biz-form-textarea
                    v-model="form.translations[ activeTab ].description"
                    :label="i18n.description"
                    placeholder="..."
                    rows="3"
                    :message="error('translations.'+ activeTab +'.description')"
                    :disabled="isInputDisabled"
                />
            </div>
        </form>
    </div>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizButtonDelete from '@/Biz/ButtonDelete.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizFormInput from '@/Biz/Form/Input.vue';
    import BizFormTextarea from '@/Biz/Form/Textarea.vue';
    import BizSelect from '@/Biz/Select.vue';
    import icon from '@/Libs/icon-class';
    import { regexFileName, useModelWrapper } from '@/Libs/utils';
    import { confirmDelete } from '@/Libs/alert';
    import { isEmpty, keys, last } from 'lodash';
    import { ref } from "vue";
    import { usePage } from '@inertiajs/vue3';

    function generateNewTranslation() {
        return {
            alt: '',
            description: '',
        };
    };

    function getAvailableLocales(mediaTranslations, localeOptions) {
        const keys = Object.keys(mediaTranslations);
        return localeOptions.filter(locale => !keys.includes(locale.id));
    };

    function getFirstAvailableLocale(mediaTranslations, localeOptions) {
        return getAvailableLocales(mediaTranslations, localeOptions).find(Boolean);
    };

    export default {
        name: 'MediaForm',

        components: {
            BizButtonDelete,
            BizButtonIcon,
            BizFormInput,
            BizFormTextarea,
            BizSelect,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        inject: {
            i18n: {
                default: () => ({
                    file_name: 'File Name',
                    alternative_text: 'Alternative Text',
                    description: 'Description',
                })
            },
        },

        props: {
            isAjax: { type: Boolean, default: false },
            media: { type: Object, default: () => {} },
        },

        emits: [
            'on-success-submit',
            'on-error-submit',
        ],

        setup(props, {emit}) {
            const defaultLocale = usePage().props.defaultLanguage;

            const localeOptions = usePage().props.languageOptions;

            const firstAvailabeLocale = getFirstAvailableLocale(
                props.media.translations,
                localeOptions
            );

            const selectedLocale = ref(firstAvailabeLocale?.id ?? null);

            return {
                activeTab: ref(defaultLocale),
                defaultLocale,
                form: useModelWrapper(props, emit, 'media'),
                localeOptions,
                selectedLocale,
            };
        },

        data() {
            return {
                isInputDisabled: false,
                formErrors: {},
                label: {
                    alternative_text: 'Alternative Text',
                    description: 'Description',
                },
                loader: null,
                icon,
            };
        },

        computed: {
            availableLocales() {
                return getAvailableLocales(
                    this.form.translations,
                    this.localeOptions
                );
            },

            usedLocales() {
                const locales = keys(this.form.translations);
                return this.localeOptions.filter(locale => {
                    return locales.includes(locale.id);
                });
            },

            isImage() {
                return this.media.is_image;
            },

            canSubmit() {
                return !isEmpty(this.form.file_name);
            }
        },

        methods: {
            resetFirstAvailableLocale() {
                const firstAvailabeLocale = getFirstAvailableLocale(
                    this.form.translations,
                    this.localeOptions,
                );

                if (firstAvailabeLocale) {
                    this.selectedLocale = firstAvailabeLocale?.id ?? null;
                }
            },

            createNewTranslation(locale) {
                this.form.translations[locale] = generateNewTranslation();
            },

            setActiveTab(locale) {
                if (this.activeTab !== locale) {
                    this.activeTab = locale;
                }
            },

            addTranslation() {
                const locale = this.selectedLocale;
                this.createNewTranslation(locale);
                this.setActiveTab(locale);
                this.resetFirstAvailableLocale();
            },

            deleteTranslation(event, locale) {
                confirmDelete().then((result) => {
                    if (result.isConfirmed) {
                        delete this.form.translations[locale];

                        const locales = keys(this.form.translations);

                        if (! locales.includes(this.activeTab)) {
                            this.setActiveTab(last(locales));
                        }

                        this.resetFirstAvailableLocale();
                    }
                });
            },

            keyPressFileName(event) {
                // @see https://stackoverflow.com/questions/61938667/vue-js-how-to-allow-an-user-to-type-only-letters-in-an-input-field
                let char = String.fromCharCode(event.keyCode);
                const lastCharacter = event.target.value.slice(-1);

                if ( (char === ' ' || char === '_') && (lastCharacter !== '-')) {
                    event.target.value += '-';
                } else if (char === '-' && lastCharacter === '-') {
                    event.target.value += '';
                } else if ((new RegExp('^['+regexFileName+']+$')).test(char)) {
                    return true;
                }
                event.preventDefault();
            },
        },
    }
</script>
