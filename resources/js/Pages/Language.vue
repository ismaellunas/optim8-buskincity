<template>
    <div>
        <biz-error-notifications :errors="$page.props.errors" />

        <div class="mb-6">
            <form
                class="columns"
                method="post"
                @submit.prevent="onSubmit"
            >
                <div class="column">
                    <fieldset
                        class="box"
                        :disabled="isProcessing"
                    >
                        <div class="field is-grouped is-grouped-right">
                            <div class="control">
                                <biz-button class="is-link">
                                    {{ i18n.update }}
                                </biz-button>
                            </div>
                        </div>
                        <div class="columns">
                            <div class="column">
                                <biz-form-dropdown-search
                                    :label="i18n.default_language"
                                    :close-on-click="true"
                                    @search="searchLanguage($event)"
                                >
                                    <template #trigger>
                                        <span :style="{'min-width': '4rem'}">
                                            {{ selectedDefaultLanguage }}
                                        </span>
                                    </template>

                                    <biz-dropdown-item
                                        v-for="option in filteredLanguages"
                                        :key="option.id"
                                        @click="selectedDefaultLanguage = option"
                                    >
                                        {{ option.value }}
                                    </biz-dropdown-item>
                                </biz-form-dropdown-search>
                            </div>
                        </div>

                        <div class="columns mt-2 is-multiline">
                            <div class="column is-full">
                                <biz-label>
                                    {{ i18n.supported_languages }}
                                </biz-label>
                            </div>

                            <div
                                v-for="option, index in sortedLanguageOptions"
                                :key="option.id"
                                class="column is-3 py-1"
                            >
                                <biz-button-option
                                    class="is-fullwidth"
                                    type="button"
                                    selected-attribute="selected"
                                    :option="option"
                                    :disabled="option.id == form.default_language"
                                    @click="toggleLanguage(sortedLanguageOptions[index])"
                                >
                                    {{ option.value }}
                                </biz-button-option>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonOption from '@/Biz/ButtonOption.vue';
    import BizDropdownItem from '@/Biz/DropdownItem.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import BizFormDropdownSearch from '@/Biz/Form/DropdownSearch.vue';
    import BizLabel from '@/Biz/Label.vue';
    import { success as successAlert } from '@/Libs/alert';
    import { debounceTime } from '@/Libs/defaults';
    import { useForm } from '@inertiajs/vue3';
    import { debounce, filter, find, isEmpty, sortBy } from 'lodash';

    export default {
        name: 'LanguageIndex',

        components: {
            BizButton,
            BizButtonOption,
            BizDropdownItem,
            BizErrorNotifications,
            BizFormDropdownSearch,
            BizLabel,
        },

        mixins: [
            MixinHasLoader,
            MixinHasPageErrors,
        ],

        layout: AppLayout,

        props: {
            supportedLanguages: {type: Array, required: true},
            baseRouteName: {type: String, required: true},
            errors: {type: Object, default: () => {}},
            languageOptions: {type: Object, required: true},
            defaultLanguage: {type: Number, required: true},
            title: {type: String, required: true},
            i18n: {type: Object, default: () => ({
                default_language : 'Default language',
                supported_languages : 'Supported languages',
                update : 'Update',
            })},
        },

        setup(props) {
            const form = {
                languages: props.supportedLanguages,
                default_language: props.defaultLanguage,
            };

            return {
                form: useForm(form),
            };
        },

        data() {
            return {
                isProcessing: false,
                filteredLanguages: this.languageOptions.slice(0, 10),
                supportedLanguageOptions: this.initSupportedLanguageOptions(),
            };
        },

        computed: {
            sortedLanguageOptions() {
                return sortBy(this.supportedLanguageOptions, ['value']);
            },

            selectedDefaultLanguage: {
                get() {
                    const self = this;
                    if (this.form.default_language) {
                        const language = find(
                            this.languageOptions,
                            ['id', parseInt(this.form.default_language)]
                        );
                        return language.value;
                    }
                    return '';
                },
                set(language) {
                    this.form.default_language = language.id;

                    this.addSupportedLanguages(language.id);

                    const selectedLanguage = find(
                        this.supportedLanguageOptions,
                        language
                    );

                    selectedLanguage.selected = true;
                }
            },
        },

        methods: {
            onSubmit() {
                const self = this;

                this.form.post(route(this.baseRouteName+'.update'), {
                    preserveScroll: false,
                    onStart: () => {
                        self.onStartLoadingOverlay();
                        self.isProcessing = true;
                    },
                    onSuccess: (page) => {
                        self.form.isDirty = false;
                        self.supportedLanguageOptions = self.initSupportedLanguageOptions();
                        successAlert(page.props.flash.message);
                    },
                    onFinish: () => {
                        self.onEndLoadingOverlay();
                        self.isProcessing = false;
                    }
                });
            },

            searchLanguage: debounce(function(term) {
                if (!isEmpty(term) && term.length > 1) {
                    this.filteredLanguages = filter(this.languageOptions, function (language) {
                        return new RegExp(term, 'i').test(language.value);
                    }).slice(0, 10);
                } else {
                    this.filteredLanguages = this.languageOptions.slice(0, 10);
                }
            }, debounceTime),

            addSupportedLanguages(languageId) {
                if (!this.form.languages.includes(languageId)) {
                    this.form.languages.push(languageId);
                }
            },

            updateSelectedLanguage(languageId) {
                const languageIds = this.form.languages;
                const index = languageIds.indexOf(languageId);

                if (index === -1) {
                    languageIds.push(languageId);
                } else {
                    languageIds.splice(index, 1);
                }
            },

            toggleLanguage(language) {
                language.selected = !language.selected;

                this.updateSelectedLanguage(language.id);
            },

            initSupportedLanguageOptions() {
                const self = this;

                return self.languageOptions.map((language) => {
                    language['selected'] = self.supportedLanguages.includes(language.id);
                    return language;
                });
            }
        },
    };
</script>
