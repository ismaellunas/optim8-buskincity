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
                                v-for="option in sortedLanguageOptions"
                                :key="option.id"
                                class="column is-3 py-1"
                            >
                                <div class="field is-horizontal">
                                    <div class="field-body">
                                        <div class="field">
                                            <div class="control">
                                                <sdb-checkbox
                                                    v-model:checked="form.languages"
                                                    :disabled="option.id == form.default_language"
                                                    :value="option.id"
                                                >
                                                    &nbsp; {{ option.value }}
                                                </sdb-checkbox>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
    import SdbCheckbox from '@/Sdb/Checkbox';
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
            SdbCheckbox,
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
                trans: {
                    labels: {
                        "Supported Languages": "Supported Languages",
                    }
                },
            };
        },

        computed: {
            sortedLanguageOptions() {
                return sortBy(this.languageOptions, ['value']);
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
        },
    };
</script>
