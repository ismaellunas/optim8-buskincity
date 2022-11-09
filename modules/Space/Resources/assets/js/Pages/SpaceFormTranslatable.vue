<template>
    <div>
        <div class="box">
            <div class="columns">
                <div class="column">
                    <biz-language-tab
                        class="is-right"
                        :locale-options="localeOptions"
                        :selected-locale="selectedLocale"
                        @on-change-locale="onChangeLocale"
                    />
                </div>
            </div>

            <biz-form-textarea
                v-model="spaceLocale.description"
                label="Description"
                placeholder="Description"
                rows="3"
                :maxlength="maxLength.description"
            />

            <biz-form-textarea
                v-model="spaceLocale.condition"
                label="Condition"
                placeholder="Condition"
                rows="3"
                :maxlength="maxLength.condition"
                :message="error('condition')"
            />

            <biz-form-textarea
                v-model="spaceLocale.surface"
                label="Surface"
                placeholder="Surface"
                rows="3"
                :maxlength="maxLength.surface"
                :message="error('surface')"
            />
        </div>
    </div>
</template>

<script>
    import BizFormTextarea from '@/Biz/Form/Textarea';
    import BizLanguageTab from '@/Biz/LanguageTab';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import { isEmpty, find, sortBy } from 'lodash';
    import { ref } from 'vue';
    import { useModelWrapper } from '@/Libs/utils';
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            BizFormTextarea,
            BizLanguageTab,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        props: {
            modelValue: { type: Object, required: true },
        },

        setup(props, { emit }) {
            const defaultLocale = usePage().props.value.defaultLanguage;
            let selectedLocale = props.locale ?? defaultLocale;

            const localeOptions = sortBy(
                usePage().props.value.languageOptions,
                [
                    function(locale) {
                        return locale.id != selectedLocale;
                    }
                ]
            );

            if (typeof find(localeOptions, { 'id': selectedLocale }) === 'undefined') {
                selectedLocale = defaultLocale;
            }

            return {
                maxLength: usePage().props.value.maxLength,
                localeOptions: localeOptions,
                selectedLocale: ref(selectedLocale),
                space: useModelWrapper(props, emit),
            };
        },

        computed: {
            spaceLocale() {
                return this.space.translations[this.selectedLocale];
            },
        },

        beforeMount() {
            const self = this;
            const translations = this.space.translations;

            self.localeOptions.forEach((locale) => {
                if (
                    !this.space.translations[ locale.id ]
                    || isEmpty(translations[ locale.id ])
                ) {
                    translations[ locale.id ] = self.initTranslation();
                }
            });
        },

        methods: {
            initTranslation() {
                return {
                    condition: null,
                    description: null,
                    excerpt: null,
                    surface: null,
                };
            },

            onChangeLocale(localeId) {
                this.selectedLocale = localeId;
            },
        },
    };
</script>
