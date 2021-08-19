<template>
    <form @submit.prevent="submit(form, media.id)">
        <sdb-form-input
            label="Name"
            v-model="form.file_name"
            :message="error('name')"
            :disabled="isInputDisabled"
            required
        />

        <sdb-form-field>
            <template v-slot:label>{{ text.alternative_text }}</template>
        </sdb-form-field>

        <template v-for="option in localeOptions" :key="option.id">
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
                            :disabled="isInputDisabled"
                        />
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

        <div class="field is-grouped is-pulled-right">
            <sdb-button class="is-link">
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
    import SdbFormField from '@/Sdb/Form/Field';
    import SdbFormFieldHorizontal from '@/Sdb/Form/FieldHorizontal';
    import SdbFormInput from '@/Sdb/Form/Input';
    import SdbInput from '@/Sdb/Input';
    import SdbSelect from '@/Sdb/Select';
    import { isEmpty } from 'lodash';
    import { getTranslation } from '@/Libs/translation';
    import { useForm } from '@inertiajs/inertia-vue3'
    import { ref } from "vue";

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
            SdbFormField,
            SdbFormFieldHorizontal,
            SdbFormInput,
            SdbInput,
            SdbSelect,
        },
        props: {
            media: Object,
            localeOptions: Array,
            defaultLocale: {type: String, default: "en"},
        },
        setup(props, { emit }) {
            let translations = {};

            if (isEmpty(props.media.translations)) {
                translations.en = generateNewTranslation();
            } else {
                props.media.translations.forEach(translation => {
                    translations[translation.locale] = {
                        alt: translation.alt
                    };
                });
            }

            const form = useForm({
                file_name: props.media.file_name,
                translations: translations,
            });

            const firstAvailabeLocale = getFirstAvailableLocale(
                translations,
                props.localeOptions
            );
            const selectedLocale = ref(firstAvailabeLocale?.id ?? null);

            function submit(currentForm, id) {
                currentForm.put(route('admin.media.update', id), {
                    onSuccess: (response) => {
                        emit('on-success-submit', response);
                    }
                });
            };

            return {
                form,
                submit,
                selectedLocale,
            };
        },
        data() {
            return {
                isInputDisabled: false,
                text: {
                    alternative_text: 'Alternative Text',
                },
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
        },
        computed: {
            availableLocales() {
                return getAvailableLocales(
                    this.form.translations,
                    this.localeOptions
                );
            }
        },
    }
</script>
