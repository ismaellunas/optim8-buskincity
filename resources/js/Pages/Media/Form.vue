<template>
    <sdb-error-notifications :errors="formErrors"/>

    <form @submit.prevent="submit(form, media.id)">
        <sdb-form-input
            v-model="form.file_name"
            label="File Name"
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
                    :class="{'is-active': option.id == activeTab}"
                    :key="option.id"
                >
                    <a @click.prevent="setActiveTab(option.id)" >
                        <span>{{ option.id.toUpperCase() }}</span>
                        <sdb-button-delete
                            v-if="option.id !== defaultLocale"
                            class="ml-1"
                            type="button"
                            @click.once.stop="deleteTranslation($event, option.id)"
                        />
                    </a>
                </li>
                <li v-if="availableLocales.length">
                    <sdb-select v-model="selectedLocale">
                        <template v-for="locale in availableLocales">
                            <option :value="locale.id">{{ locale.name }}</option>
                        </template>
                    </sdb-select>
                    <sdb-button-icon
                        icon="fas fa-plus"
                        type="button"
                        @click="addTranslation"
                    />
                </li>
            </ul>
        </div>
        <div v-if="form.translations[activeTab]" class="content">
            <sdb-form-input
                v-if="isImage"
                v-model="form.translations[activeTab].alt"
                maxlength="255"
                :disabled="isInputDisabled"
                :label="label.alternative_text"
                :message="error('translations.'+ activeTab +'.alt')"
            />
            <sdb-form-textarea
                :label="label.description"
                v-model="form.translations[ activeTab ].description"
                placeholder="..."
                rows="3"
                :message="error('translations.'+ activeTab +'.description')"
                :disabled="isInputDisabled"
            />
        </div>

        <div class="field is-grouped is-pulled-right">
            <sdb-button
                class="is-link"
                :disabled="!canSubmit"
            >
                Submit
            </sdb-button>
            <sdb-button
                class="is-link is-light ml-2"
                type="button"
                @click="$emit('cancel')"
            >
                Cancel
            </sdb-button>
        </div>
    </form>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import SdbButton from '@/Sdb/Button';
    import SdbButtonDelete from '@/Sdb/ButtonDelete';
    import SdbButtonIcon from '@/Sdb/ButtonIcon';
    import SdbErrorNotifications from '@/Sdb/ErrorNotifications';
    import SdbFormInput from '@/Sdb/Form/Input';
    import SdbFormTextarea from '@/Sdb/Form/Textarea';
    import SdbSelect from '@/Sdb/Select';
    import { getTranslation } from '@/Libs/translation';
    import { buildFormData, regexFileName } from '@/Libs/utils';
    import { isEmpty, keys, last } from 'lodash';
    import { reactive, ref } from "vue";
    import { useForm, usePage } from '@inertiajs/inertia-vue3';

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
        mixins: [
            MixinHasPageErrors,
        ],
        components: {
            SdbButton,
            SdbButtonDelete,
            SdbButtonIcon,
            SdbErrorNotifications,
            SdbFormInput,
            SdbFormTextarea,
            SdbSelect,
        },
        emits: [
            'cancel',
            'on-success-submit',
            'on-error-submit',
        ],
        props: {
            isAjax: {type: Boolean, default: false},
            media: Object,
        },
        setup(props, { emit }) {
            let translations = {};
            const defaultLocale = usePage().props.value.currentLanguage;

            if (isEmpty(props.media.translations)) {
                translations.en = generateNewTranslation();
            } else {
                props.media.translations.forEach(translation => {
                    translations[translation.locale] = {
                        alt: translation.alt ?? null,
                        description: translation.description ?? null,
                    };
                });

                if (!translations[defaultLocale]) {
                    translations[defaultLocale] = generateNewTranslation();
                }
            }

            let form = reactive({
                _method: 'post',
                file: null,
                file_name: props.media.file_name,
                translations: translations,
            });

            const localeOptions = usePage().props.value.languageOptions;

            const firstAvailabeLocale = getFirstAvailableLocale(
                translations,
                localeOptions
            );

            const selectedLocale = ref(firstAvailabeLocale?.id ?? null);

            return {
                activeTab: ref(defaultLocale),
                defaultLocale,
                form,
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
            };
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
                delete this.form.translations[locale];

                const locales = keys(this.form.translations);

                if (! locales.includes(this.activeTab)) {
                    this.setActiveTab(last(locales));
                }

                this.resetFirstAvailableLocale();
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
            submit() {
                const currentForm = this.form;
                const self = this;
                let url = null;

                if (this.media.id) {
                    url = route('admin.media.update', this.media.id);
                    currentForm._method = 'put';
                } else {
                    if (this.isAjax) {
                        url = route('admin.api.media.store');
                    } else {
                        url = route('admin.media.store');
                    }
                    currentForm.file = this.media.file;
                }

                self.loader = self.$loading.show({});
                self.isInputDisabled = true;

                if (this.isAjax) {
                    const formData = new FormData();
                    buildFormData(formData, currentForm);

                    axios.post(
                        url,
                        formData,
                        {headers: {'Content-Type': 'multipart/form-data'}}
                    ).then(function(response) {
                        self.$emit('on-success-submit', response);
                    })
                    .catch(function(error) {
                        self.$emit('on-error-submit', error);
                    }).then(() => {
                        self.loader.hide();
                        self.isInputDisabled = false;
                    });
                } else {
                    const form = useForm(currentForm);
                    form.post(url, {
                        onSuccess: (page) => {
                            self.$emit('on-success-submit', page);
                            self.formErrors = {};
                        },
                        onError: errors => {
                            self.formErrors = errors;
                        },
                        onFinish: () => {
                            self.loader.hide();
                            self.isInputDisabled = false;
                        },
                    });
                }
            }
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
    }
</script>
