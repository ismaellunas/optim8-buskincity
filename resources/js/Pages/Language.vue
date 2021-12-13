<template>
    <app-layout>
        <template #header>
            {{ title }}
        </template>

        <sdb-error-notifications :errors="$page.props.errors" />

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
                                <sdb-button class="is-link">
                                    Update
                                </sdb-button>
                            </div>
                        </div>
                        <div class="columns">
                            <div class="column">
                                <sdb-form-dropdown-search
                                    label="Default Language"
                                    :close-on-click="true"
                                    @search="searchLanguage($event)"
                                >
                                    <template #trigger>
                                        <span :style="{'min-width': '4rem'}">
                                            {{ selectedDefaultLanguage }}
                                        </span>
                                    </template>

                                    <sdb-dropdown-item
                                        v-for="option in filteredLanguages"
                                        :key="option.id"
                                        @click="selectedDefaultLanguage = option.id"
                                    >
                                        {{ option.value }}
                                    </sdb-dropdown-item>
                                </sdb-form-dropdown-search>
                            </div>
                        </div>

                        <div class="columns mt-2 is-multiline">
                            <div class="column is-full">
                                <sdb-label>
                                    {{ trans.labels["Supported Languages"] }}
                                </sdb-label>
                            </div>

                            <div
                                v-for="option, index in sortedLanguageOptions"
                                :key="option.id"
                                class="column is-3 py-1"
                            >
                                <sdb-button-option
                                    class="is-fullwidth"
                                    type="button"
                                    selected-attribute="selected"
                                    :option="option"
                                    @click="toggleLanguage(sortedLanguageOptions[index])"
                                >
                                    {{ option.value }}
                                </sdb-button-option>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </form>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import SdbButton from '@/Sdb/Button';
    import SdbButtonOption from '@/Sdb/ButtonOption';
    import SdbDropdownItem from '@/Sdb/DropdownItem';
    import SdbErrorNotifications from '@/Sdb/ErrorNotifications';
    import SdbFormDropdownSearch from '@/Sdb/Form/DropdownSearch';
    import SdbLabel from '@/Sdb/Label';
    import { success as successAlert, oops as oopsAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/inertia-vue3';
    import { debounce, filter, find, isEmpty, sortBy } from 'lodash';

    export default {
        name: 'Language',

        components: {
            AppLayout,
            SdbButton,
            SdbButtonOption,
            SdbDropdownItem,
            SdbErrorNotifications,
            SdbFormDropdownSearch,
            SdbLabel,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        props: {
            supportedLanguages: {type: Array, required: true},
            baseRouteName: {type: String, required: true},
            errors: {type: Object, default: () => {}},
            languageOptions: {type: Object, required: true},
            defaultLanguage: {type: Number, required: true},
            title: {type: String, required: true},
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
                loader: null,
                filteredLanguages: this.languageOptions.slice(0, 10),
                supportedLanguageOptions: this.initSupportedLanguageOptions(),
                trans: {
                    labels: {
                        "Supported Languages": "Supported Languages",
                    }
                },
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
                set(languageId) {
                    this.form.default_language = languageId;

                    this.addSupportedLanguages(languageId);
                }
            },
        },

        update() {
            this.supportedLanguageOptions = this.initSupportedLanguageOptions();
        },

        methods: {
            onSubmit() {
                const self = this;

                this.form.post(route(this.baseRouteName+'.update'), {
                    preserveScroll: false,
                    onStart: () => {
                        self.loader = self.$loading.show();
                        self.isProcessing = true;
                    },
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                        self.form.isDirty = false;
                    },
                    onFinish: () => {
                        self.loader.hide();
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
            }, 750),

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

                return self.languageOptions
                    .map((language) => {
                        language['selected'] = self.supportedLanguages.includes(language.id);
                        return language;
                    });
            }
        },
    };
</script>
