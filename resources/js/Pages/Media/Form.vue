<template>
    <sdb-error-notifications :errors="formErrors"/>

    <form @submit.prevent="submit(form, media.id)">
        <sdb-form-input
            label="File Name"
            v-model="form.file_name"
            :message="error('file_name', 'default', formErrors)"
            :disabled="isInputDisabled"
            maxlength="250"
            @on-keypress="keyPressFileName"
            required
        />

        <template v-if="isImage">
            <sdb-form-field>
                <template v-slot:label>{{ label.alternative_text }}</template>
            </sdb-form-field>

            <template v-for="option in localeOptions" :key="option.id" >
                <sdb-form-field-horizontal
                    v-if="form.translations[ option.id ]"
                >
                    <template v-slot:label>
                        {{ option.id.toUpperCase() }}
                    </template>
                    <div class="columns">
                        <div class="column is-three-quarters">
                            <sdb-input
                                v-model="form.translations[ option.id ].alt"
                                maxlength="255"
                                :disabled="isInputDisabled"
                            />
                            <sdb-input-error :message="error('translations.'+option.id+'.alt')"/>
                        </div>
                        <div class="column">
                            <sdb-button-icon
                                v-if="option.id !== defaultLocale"
                                icon="fas fa-minus"
                                type="button"
                                @click="deleteTranslation(option.id)"
                            />
                        </div>
                    </div>
                </sdb-form-field-horizontal>
            </template>

            <sdb-form-field-horizontal>
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
            </sdb-form-field-horizontal>
        </template>

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
    import SdbButtonIcon from '@/Sdb/ButtonIcon';
    import SdbErrorNotifications from '@/Sdb/ErrorNotifications';
    import SdbFormField from '@/Sdb/Form/Field';
    import SdbFormFieldHorizontal from '@/Sdb/Form/FieldHorizontal';
    import SdbFormInput from '@/Sdb/Form/Input';
    import SdbInput from '@/Sdb/Input';
    import SdbInputError from '@/Sdb/InputError';
    import SdbSelect from '@/Sdb/Select';
    import { isBlank,buildFormData, regexFileName } from '@/Libs/utils';
    import { getTranslation } from '@/Libs/translation';
    import { isEmpty } from 'lodash';
    import { reactive, ref } from "vue";
    import { useForm, usePage } from '@inertiajs/inertia-vue3';

    function generateNewTranslation() {
        return {
            alt: '',
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
        mixins: [
            MixinHasPageErrors,
        ],
        components: {
            SdbButton,
            SdbButtonIcon,
            SdbErrorNotifications,
            SdbFormField,
            SdbFormFieldHorizontal,
            SdbFormInput,
            SdbInput,
            SdbInputError,
            SdbSelect,
        },
        emits: [
            'cancel',
            'on-success-submit',
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
                        alt: translation.alt
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
                defaultLocale,
                form,
                localeOptions,
                selectedLocale,
            };
        },
        data() {
            return {
                isInputDisabled: false,
                label: {
                    alternative_text: 'Alternative Text',
                },
                formErrors: {},
            };
        },
        methods: {
            addTranslation() {
                this.form.translations[this.selectedLocale] = generateNewTranslation();

                const firstAvailabeLocale = getFirstAvailableLocale(
                    this.form.translations,
                    this.localeOptions,
                );

                this.selectedLocale = firstAvailabeLocale?.id ?? null;
            },
            deleteTranslation(locale) {
                delete this.form.translations[locale];
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
                        url = route('api.admin.media.store');
                    } else {
                        url = route('admin.media.store');
                    }
                    currentForm.file = this.media.file;
                }

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
                        console.log(error);
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
            isImage() {
                return this.media.is_image;
            },
            canSubmit() {
                return !isEmpty(this.form.file_name);
            }
        },
    }
</script>
