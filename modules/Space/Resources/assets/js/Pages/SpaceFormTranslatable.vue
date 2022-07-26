<template>
    <div>
        <div class="tabs is-toggle">
            <ul>
                <li
                    v-for="(option, index) in localeOptions"
                    :key="option.id"
                    :class="{ 'is-active': option.id == selectedLocale }"
                    @click="selectedLocale = option.id"
                >
                    <a>
                        <span>{{ option.name }}</span>
                        <span
                            v-if="index == 0"
                            class="tag is-link is-light ml-3"
                        >
                            Default
                        </span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="box">
            <biz-form-textarea
                v-model="spaceLocale.description"
                label="Description"
                placeholder="Description"
                rows="3"
                maxlength="1000"
            />

            <biz-form-textarea
                v-model="spaceLocale.condition"
                label="Condition"
                placeholder="Condition"
                rows="3"
                maxlength="500"
                :message="error('condition')"
            />

            <biz-form-textarea
                v-model="spaceLocale.surface"
                label="Surface"
                placeholder="Surface"
                rows="3"
                maxlength="500"
                :message="error('surface')"
            />
        </div>
    </div>
</template>

<script>
    import BizFormTextarea from '@/Biz/Form/Textarea';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import { isEmpty, find, sortBy } from 'lodash';
    import { ref } from 'vue';
    import { useModelWrapper } from '@/Libs/utils';
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            BizFormTextarea,
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
        },
    };
</script>
